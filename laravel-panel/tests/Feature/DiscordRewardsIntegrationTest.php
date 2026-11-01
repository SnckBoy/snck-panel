<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscordRewardsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_cannot_manage_discord_rewards(): void
    {
        $user = $this->user('discord-user', 'user');
        $this->actingAs($user)->getJson('/api/discord-rewards/admin')->assertForbidden();
        $this->actingAs($user)->postJson('/api/discord-rewards/admin/plans', $this->plan())->assertForbidden();
    }

    public function test_staff_can_save_redacted_config_and_create_update_delete_plan(): void
    {
        $owner = $this->user('discord-owner', 'owner');
        $config = $this->actingAs($owner)->putJson('/api/discord-rewards/admin/config', [
            'enabled' => false, 'guildId' => '12345678901234567', 'announcementChannelId' => '', 'oauthClientId' => '22345678901234567', 'oauthRedirectUri' => 'https://panel.example.test/api/discord-rewards/oauth/callback', 'minimumAccountAgeDays' => 30, 'dataRetentionDays' => 365,
        ])->assertOk();
        $config->assertJsonMissingPath('config.botTokenEncrypted')->assertJsonPath('config.enabled', false);

        $created = $this->actingAs($owner)->postJson('/api/discord-rewards/admin/plans', $this->plan())->assertCreated();
        $this->assertSame('starter', $created->json('plan.id'));
        $this->actingAs($owner)->putJson('/api/discord-rewards/admin/plans/starter', array_merge($this->plan(), ['name' => 'Updated starter']))->assertOk()->assertJsonPath('plan.name', 'Updated starter');
        $this->actingAs($owner)->postJson('/api/discord-rewards/admin/plans', $this->plan())->assertStatus(409);
        $this->actingAs($owner)->deleteJson('/api/discord-rewards/admin/plans/starter')->assertOk();
        $this->assertDatabaseHas('audit_events', ['action' => 'discord.config_updated', 'user_id' => $owner->id]);
    }

    public function test_invalid_enabled_config_and_plan_are_rejected(): void
    {
        $admin = $this->user('discord-admin', 'admin');
        $this->actingAs($admin)->putJson('/api/discord-rewards/admin/config', ['enabled' => true, 'guildId' => 'bad'])->assertStatus(400);
        $this->actingAs($admin)->postJson('/api/discord-rewards/admin/plans', ['id' => 'bad id'])->assertStatus(400);
    }

    private function plan(): array
    {
        return ['id' => 'starter', 'name' => 'Starter reward', 'description' => 'A starter reward plan.', 'requiredInvites' => 3, 'allowCreate' => true, 'allowUpgrade' => false, 'ram' => 2, 'cpu' => 100, 'disk' => 10, 'durationHours' => 720, 'allowedNodeIds' => [], 'maxClaimsPerUser' => 1, 'maxTotalClaims' => null, 'enabled' => true, 'sortOrder' => 0];
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'correct horse battery staple']);
    }
}
