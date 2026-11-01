<?php

namespace Tests\Feature;

use App\Models\CloudflareAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloudflareIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_cannot_access_cloudflare(): void
    {
        $user = $this->user('cf-user', 'user');
        $this->actingAs($user)->getJson('/api/cloudflare/connection')->assertForbidden();
        $this->actingAs($user)->postJson('/api/cloudflare/connection', ['token' => 'secret'])->assertForbidden();
    }

    public function test_staff_can_connect_and_manage_dns_without_plaintext_token_storage(): void
    {
        Http::fake([
            '*user/tokens/verify' => Http::response(['success' => true, 'result' => ['status' => 'active']]),
            '*accounts?per_page=50' => Http::response(['success' => true, 'result' => [['id' => 'acct-1', 'name' => 'Example']]]),
            '*zones?account.id=acct-1&per_page=100&page=1' => Http::response(['success' => true, 'result' => [['id' => 'zone-1', 'name' => 'example.test']], 'result_info' => ['total_pages' => 1]]),
            '*zones/zone-1/dns_records*' => Http::response(['success' => true, 'result' => ['id' => 'record-1', 'type' => 'A', 'name' => 'mc.example.test', 'content' => '203.0.113.10']]),
        ]);
        $admin = $this->user('cf-admin', 'admin');

        $connect = $this->actingAs($admin)->postJson('/api/cloudflare/connection', ['token' => 'cf-secret-token'])->assertCreated();
        $this->assertSame('acct-1', $connect->json('accountId'));
        $account = CloudflareAccount::query()->firstOrFail();
        $this->assertNotSame('cf-secret-token', $account->api_token_encrypted);
        $this->assertSame('cf-secret-token', Crypt::decryptString($account->api_token_encrypted));

        $this->actingAs($admin)->getJson('/api/cloudflare/zones')->assertOk()->assertJsonPath('0.id', 'zone-1');
        $this->actingAs($admin)->postJson('/api/cloudflare/zones/zone-1/dns', [
            'type' => 'A', 'name' => 'mc.example.test', 'content' => '203.0.113.10', 'ttl' => 300, 'proxied' => false,
        ])->assertCreated()->assertJsonPath('id', 'record-1');
        $this->assertDatabaseHas('audit_events', ['action' => 'cloudflare.connected', 'user_id' => $admin->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'cloudflare.dns.created', 'user_id' => $admin->id]);
    }

    public function test_srv_create_and_update_use_data_object_without_content_or_proxied(): void
    {
        $admin = $this->user('cf-srv-admin', 'admin');
        $this->account('acct-srv');
        Http::fake([
            '*zones/zone-srv/dns_records*' => Http::sequence()
                ->push(['success' => true, 'result' => ['id' => 'srv-1', 'type' => 'SRV']], 200)
                ->push(['success' => true, 'result' => ['id' => 'srv-1', 'type' => 'SRV']], 200),
        ]);
        $payload = [
            'type' => 'SRV', 'name' => '_minecraft._tcp.example.test', 'ttl' => 300, 'proxied' => true,
            'data' => ['service' => '_minecraft', 'proto' => '_tcp', 'name' => 'example.test', 'priority' => 10, 'weight' => 5, 'port' => 25565, 'target' => 'mc.example.test'],
        ];

        $this->actingAs($admin)->postJson('/api/cloudflare/zones/zone-srv/dns', $payload)->assertCreated();
        $this->actingAs($admin)->putJson('/api/cloudflare/zones/zone-srv/dns/srv-1', $payload)->assertOk();
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->method() === 'POST' && ! array_key_exists('content', $request->data()) && ! array_key_exists('proxied', $request->data()) && $request->data()['data']['port'] === 25565);
        Http::assertSent(fn ($request) => $request->method() === 'PUT' && ! array_key_exists('content', $request->data()) && ! array_key_exists('proxied', $request->data()) && $request->data()['data']['target'] === 'mc.example.test');
    }

    public function test_reconnecting_same_account_updates_the_existing_encrypted_token(): void
    {
        $admin = $this->user('cf-reconnect-admin', 'admin');
        Http::fake([
            '*user/tokens/verify' => Http::response(['success' => true, 'result' => ['status' => 'active']]),
            '*accounts?per_page=50' => Http::response(['success' => true, 'result' => [['id' => 'acct-reconnect', 'name' => 'Example']]]),
        ]);

        $first = $this->actingAs($admin)->postJson('/api/cloudflare/connection', ['token' => 'first-token'])->assertCreated();
        $second = $this->actingAs($admin)->postJson('/api/cloudflare/connection', ['token' => 'second-token'])->assertCreated();
        $this->assertSame($first->json('id'), $second->json('id'));
        $this->assertDatabaseCount('cloudflare_accounts', 1);
        $this->assertSame('second-token', Crypt::decryptString(CloudflareAccount::query()->firstOrFail()->api_token_encrypted));
    }

    public function test_zone_and_dns_listing_follows_cloudflare_pagination(): void
    {
        $admin = $this->user('cf-pages-admin', 'admin');
        $this->account('acct-pages');
        Http::fake(function ($request) {
            $url = $request->url();
            $page = str_contains($url, 'page=2') ? 2 : 1;
            if (str_contains($url, 'dns_records')) {
                return Http::response(['success' => true, 'result' => [['id' => 'record-'.$page]], 'result_info' => ['total_pages' => 2]]);
            }

            return Http::response(['success' => true, 'result' => [['id' => 'zone-'.$page]], 'result_info' => ['total_pages' => 2]]);
        });

        $this->actingAs($admin)->getJson('/api/cloudflare/zones')->assertOk()->assertJsonCount(2)->assertJsonPath('1.id', 'zone-2');
        $this->actingAs($admin)->getJson('/api/cloudflare/zones/zone-1/dns')->assertOk()->assertJsonCount(2)->assertJsonPath('1.id', 'record-2');
    }

    public function test_inactive_tokens_are_rejected_without_creating_an_account(): void
    {
        Http::fake(['*user/tokens/verify' => Http::response(['success' => true, 'result' => ['status' => 'inactive']])]);
        $owner = $this->user('cf-owner', 'owner');
        $this->actingAs($owner)->postJson('/api/cloudflare/connection', ['token' => 'inactive'])->assertStatus(400)->assertJsonPath('error', 'Cloudflare token is not active');
        $this->assertDatabaseCount('cloudflare_accounts', 0);
    }

    private function account(string $id): CloudflareAccount
    {
        return CloudflareAccount::query()->create(['id' => $id, 'account_id' => $id, 'api_token_encrypted' => Crypt::encryptString('test-token')]);
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'correct horse battery staple']);
    }
}
