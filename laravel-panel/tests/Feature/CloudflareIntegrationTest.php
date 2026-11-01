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
            'https://api.cloudflare.com/client/v4/user/tokens/verify' => Http::response(['success' => true, 'result' => ['status' => 'active']]),
            'https://api.cloudflare.com/client/v4/accounts?per_page=50' => Http::response(['success' => true, 'result' => [['id' => 'acct-1', 'name' => 'Example']]]),
            'https://api.cloudflare.com/client/v4/zones?account.id=acct-1&per_page=100' => Http::response(['success' => true, 'result' => [['id' => 'zone-1', 'name' => 'example.test']]]),
            'https://api.cloudflare.com/client/v4/zones/zone-1/dns_records' => Http::response(['success' => true, 'result' => ['id' => 'record-1', 'type' => 'A', 'name' => 'mc.example.test', 'content' => '203.0.113.10']]),
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

    public function test_inactive_tokens_are_rejected_without_creating_an_account(): void
    {
        Http::fake(['https://api.cloudflare.com/client/v4/user/tokens/verify' => Http::response(['success' => true, 'result' => ['status' => 'inactive']])]);
        $owner = $this->user('cf-owner', 'owner');
        $this->actingAs($owner)->postJson('/api/cloudflare/connection', ['token' => 'inactive'])->assertStatus(400)->assertJsonPath('error', 'Cloudflare token is not active');
        $this->assertDatabaseCount('cloudflare_accounts', 0);
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'correct horse battery staple']);
    }
}
