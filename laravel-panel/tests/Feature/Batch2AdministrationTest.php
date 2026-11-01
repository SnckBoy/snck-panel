<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Batch2AdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_revoke_a_scoped_api_key_without_plaintext_persistence(): void
    {
        $user = $this->user('batch2-user', 'user');
        $response = $this->actingAs($user)->postJson('/api/api-keys', ['label' => 'Deploy key', 'scopes' => ['servers.read', 'files.read']]);
        $response->assertCreated()->assertJsonStructure(['token', 'key' => ['id', 'label', 'scopes']]);
        $token = (string) $response->json('token');
        $this->assertNotSame('', $token);
        $this->assertDatabaseMissing('api_keys', ['key_hash' => $token]);
        $key = ApiKey::query()->firstOrFail();
        $this->assertSame(hash('sha256', $token), $key->key_hash);
        $this->actingAs($user)->deleteJson('/api/api-keys/'.$key->id)->assertOk();
        $this->assertNotNull($key->fresh()->revoked_at);
    }

    public function test_regular_users_cannot_issue_admin_scopes_or_change_settings(): void
    {
        $user = $this->user('batch2-regular', 'user');
        $this->actingAs($user)->postJson('/api/api-keys', ['label' => 'Bad key', 'scopes' => ['admin.settings']])->assertForbidden();
        $this->actingAs($user)->putJson('/api/admin/settings', ['enableRegistration' => true])->assertForbidden();
    }

    public function test_owner_can_update_allowlisted_settings_and_audit_keys(): void
    {
        $owner = $this->user('batch2-owner', 'owner');
        $this->actingAs($owner)->putJson('/api/admin/settings', ['enableRegistration' => true, 'quota_max_servers' => 8, 'panelName' => 'Snck Production'])
            ->assertOk()->assertJsonPath('settings.enableRegistration', true)->assertJsonPath('settings.quota_max_servers', 8);
        $this->assertDatabaseHas('settings', ['key' => 'enableRegistration']);
        $this->assertDatabaseHas('audit_events', ['action' => 'settings.update', 'user_id' => $owner->id]);
    }

    public function test_admin_can_list_keys_but_cannot_read_plaintext_tokens(): void
    {
        $admin = $this->user('batch2-admin', 'admin');
        $owner = $this->user('batch2-key-owner', 'user');
        $this->actingAs($owner)->postJson('/api/api-keys', ['label' => 'Owner key', 'scopes' => ['servers.read']])->assertCreated();
        $response = $this->actingAs($admin)->getJson('/api/api-keys')->assertOk();
        $response->assertJsonMissingPath('keys.0.token')->assertJsonMissingPath('keys.0.key_hash');
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'correct horse battery staple']);
    }
}
