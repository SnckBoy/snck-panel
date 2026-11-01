<?php

namespace Tests\Feature;

use App\Models\DiscordRewardState;
use App\Models\User;
use App\Services\DiscordRewardsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    private function configureDiscord(): void
    {
        app(DiscordRewardsService::class)->updateConfig(['enabled' => false, 'guildId' => '123456789012345678', 'oauthClientId' => '123456789012345678', 'oauthRedirectUri' => 'https://panel.test/auth/discord/callback', 'botToken' => str_repeat('b', 50), 'oauthClientSecret' => str_repeat('s', 20)]);
    }

    private function user(string $id, string $role = 'user'): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'password']);
    }
}
