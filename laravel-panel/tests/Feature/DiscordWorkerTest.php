<?php

namespace Tests\Feature;

use App\Models\DiscordRewardState;
use App\Models\User;
use App\Services\DiscordRewardsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscordWorkerTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_reconciles_members_and_removes_expired_claims(): void
    {
        $user = User::query()->create(['id' => 'worker-user', 'name' => 'Worker user', 'username' => 'worker-user', 'email' => 'worker@example.test', 'role' => 'user', 'password' => 'password']);
        app(DiscordRewardsService::class)->updateConfig(['enabled' => false, 'guildId' => '123456789012345678', 'oauthClientId' => '123456789012345678', 'oauthRedirectUri' => 'https://panel.test/auth/discord/callback', 'botToken' => str_repeat('b', 50), 'oauthClientSecret' => str_repeat('s', 20), 'dataRetentionDays' => 30]);
        $state = app(DiscordRewardsService::class)->state();
        $state['linkedAccounts'][$user->id] = ['discordId' => '123456789012345679', 'username' => 'worker', 'tokenEncrypted' => 'x'];
        $state['claims'][] = ['id' => 'old-claim', 'userId' => $user->id, 'planId' => 'old', 'status' => 'rejected', 'createdAt' => now()->subDays(40)->toISOString()];
        DiscordRewardState::query()->updateOrCreate(['id' => 'default'], ['payload' => $state]);
        Http::fake(['https://discord.com/api/guilds/*' => Http::response([['user' => ['id' => '123456789012345679']]])]);
        $this->artisan('discord:maintenance', ['--reconcile' => true, '--cleanup' => true])->assertSuccessful();
        $saved = app(DiscordRewardsService::class)->state();
        $this->assertTrue($saved['linkedAccounts'][$user->id]['inGuild']);
        $this->assertCount(0, $saved['claims']);
    }
}
