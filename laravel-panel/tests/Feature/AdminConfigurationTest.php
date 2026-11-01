<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_update_user_configuration(): void
    {
        $admin = $this->user('config-admin', 'admin'); $target = $this->user('config-target', 'user');
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->getJson('/api/admin/users')->assertOk()->assertJsonPath('users.1.username', 'config-target');
        $this->actingAs($admin)->patchJson('/api/admin/users/'.$target->id, ['role' => 'admin', 'disabled' => true, 'password' => 'a much stronger password'])->assertOk()->assertJsonPath('user.role', 'admin');
        $target->refresh(); $this->assertTrue($target->disabled); $this->assertTrue(Hash::check('a much stronger password', $target->password)); $this->assertDatabaseHas('audit_events', ['action' => 'admin.user_updated', 'resource_id' => $target->id]);
    }

    public function test_admin_cannot_grant_owner_or_modify_self_and_regular_users_are_denied(): void
    {
        $admin = $this->user('config-admin-2', 'admin'); $target = $this->user('config-target-2', 'user'); $regular = $this->user('config-regular', 'user');
        $this->actingAs($admin)->patchJson('/api/admin/users/'.$target->id, ['role' => 'owner'])->assertStatus(422);
        $this->actingAs($admin)->patchJson('/api/admin/users/'.$admin->id, ['disabled' => true])->assertStatus(422);
        $this->actingAs($regular)->getJson('/api/admin/users')->assertForbidden();
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'password']);
    }
}
