<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloudflareUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_open_cloudflare_dns_manager(): void
    {
        $owner = User::query()->create([
            'id' => 'cf-ui-owner', 'name' => 'Cloudflare owner', 'username' => 'cf-ui-owner',
            'email' => 'cf-ui@example.test', 'role' => 'owner', 'password' => 'password',
        ]);
        $this->actingAs($owner)->get('/cloudflare')->assertOk()->assertSee('Cloudflare DNS')->assertSee('Minecraft SRV helper');
    }

    public function test_cloudflare_controller_accepts_srv_data_payload_from_ui(): void
    {
        $sequence = Http::sequence()
            ->push(['success' => true, 'result' => ['status' => 'active']])
            ->push(['success' => true, 'result' => [['id' => 'account-1', 'name' => 'Account']]])
            ->push(['success' => true, 'result' => ['id' => 'zone-1']])
            ->push(['success' => true, 'result' => ['id' => 'record-1']]);
        Http::fake(['https://api.cloudflare.com/*' => $sequence]);
        $owner = User::query()->create([
            'id' => 'cf-ui-owner-2', 'name' => 'Cloudflare owner', 'username' => 'cf-ui-owner-2',
            'email' => 'cf-ui-2@example.test', 'role' => 'owner', 'password' => 'password',
        ]);
        $this->actingAs($owner)->postJson('/api/cloudflare/connection', ['token' => 'token-value'])->assertCreated();
        $this->actingAs($owner)->postJson('/api/cloudflare/zones/zone-1/dns', [
            'type' => 'SRV', 'name' => '_minecraft._tcp.example.com', 'ttl' => 60,
            'data' => ['service' => '_minecraft', 'proto' => '_tcp', 'name' => 'example.com', 'priority' => 0, 'weight' => 5, 'port' => 25565, 'target' => 'proxy.example.com'],
        ])->assertCreated();
    }
}
