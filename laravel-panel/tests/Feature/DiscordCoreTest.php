<?php

namespace Tests\Feature;

use App\Models\Allocation;
use App\Models\DiscordRewardState;
use App\Models\Node;
use App\Models\User;
use App\Services\DiscordRewardsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscordCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_callback_links_account_and_never_stores_plain_token(): void
    {
        $user = $this->user('discord-core');
        $this->configureDiscord();
        Http::fakeSequence()->push(['access_token' => 'discord-access-token'])->push(['id' => '123456789012345678', 'username' => 'player', 'global_name' => 'Player']);
        $redirect = $this->actingAs($user)->get('/auth/discord/redirect')->assertRedirect();
        $state = session('discord_oauth_state');
        $this->actingAs($user)->withSession(['discord_oauth_state' => $state])->get('/auth/discord/callback?code=test-code&state='.$state)->assertRedirect(route('discord.rewards'));
        $payload = DiscordRewardState::query()->find('default')->payload;
        $this->assertSame('123456789012345678', $payload['linkedAccounts'][$user->id]['discordId']);
        $this->assertNotSame('discord-access-token', $payload['linkedAccounts'][$user->id]['tokenEncrypted']);
        $this->actingAs($user)->get('/auth/discord/callback?code=test-code&state=wrong')->assertStatus(419);
    }

    public function test_claim_is_duplicate_safe_and_staff_can_review_it(): void
    {
        $user = $this->user('discord-claimer');
        $staff = $this->user('discord-staff', 'admin');
        $this->configureDiscord();
        $state = app(DiscordRewardsService::class)->state();
        $state['linkedAccounts'][$user->id] = ['discordId' => '123456789012345679', 'username' => 'player', 'tokenEncrypted' => 'encrypted', 'inviteCount' => 10];
        $state['plans'][] = ['id' => 'starter', 'name' => 'Starter', 'description' => 'Starter reward', 'requiredInvites' => 1, 'enabled' => true, 'maxClaimsPerUser' => 1];
        DiscordRewardState::query()->updateOrCreate(['id' => 'default'], ['payload' => $state]);
        $this->actingAs($user)->postJson('/api/discord-rewards/claim/starter')->assertCreated();
        $this->actingAs($user)->postJson('/api/discord-rewards/claim/starter')->assertStatus(422);
        $id = app(DiscordRewardsService::class)->state()['claims'][0]['id'];
        $this->actingAs($staff)->patchJson('/api/discord-rewards/admin/claims/'.$id, ['status' => 'approved'])->assertOk();
    }

    public function test_approved_claim_provisions_a_server_and_records_result(): void
    {
        $user = $this->user('discord-provision-user');
        $staff = $this->user('discord-provision-staff', 'admin');
        $this->configureDiscord();
        $node = Node::query()->create(['id' => 'discord-node', 'name' => 'Discord node', 'fqdn' => 'node.example.test', 'daemon_port' => 6768, 'credential_encrypted' => Crypt::encryptString('node-token'), 'credential_hash' => hash('sha256', 'node-token'), 'status' => 'online', 'docker_available' => true, 'last_heartbeat_at' => now(), 'memory_limit_mb' => 8192]);
        $allocation = Allocation::query()->create(['id' => 'discord-allocation', 'node_id' => $node->id, 'ip' => '203.0.113.10', 'port' => 25565]);
        $state = app(DiscordRewardsService::class)->state();
        $state['linkedAccounts'][$user->id] = ['discordId' => '123456789012345679', 'username' => 'player', 'tokenEncrypted' => 'encrypted', 'inviteCount' => 10];
        $state['plans'][] = ['id' => 'provision', 'name' => 'Provisioned', 'description' => 'Reward', 'requiredInvites' => 1, 'allowCreate' => true, 'ram' => 1, 'cpu' => 100, 'disk' => 10240, 'enabled' => true, 'maxClaimsPerUser' => 1];
        DiscordRewardState::query()->updateOrCreate(['id' => 'default'], ['payload' => $state]);
        Http::fake(function ($request) {
            return str_ends_with($request->url(), '/v1/servers') ? Http::response(['containerId' => str_repeat('a', 64)], 201) : Http::response(['State' => ['Status' => 'running']]);
        });
        $this->actingAs($user)->postJson('/api/discord-rewards/claim/provision')->assertCreated();
        $id = app(DiscordRewardsService::class)->state()['claims'][0]['id'];
        $response = $this->actingAs($staff)->patchJson('/api/discord-rewards/admin/claims/'.$id, ['status' => 'approved'])->assertOk();
        $response->assertJsonPath('claim.provisioning.status', 'success')->assertJsonPath('claim.status', 'approved');
        $this->assertNotNull($allocation->refresh()->assigned_server_id);
    }

    private function configureDiscord(): void
    {
        app(DiscordRewardsService::class)->updateConfig(['enabled' => false, 'guildId' => '123456789012345678', 'oauthClientId' => '123456789012345678', 'oauthRedirectUri' => 'https://panel.test/auth/discord/callback', 'botToken' => str_repeat('b', 50), 'oauthClientSecret' => str_repeat('s', 20)]);
    }

    private function user(string $id, string $role = 'user'): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'password']);
    }
}
