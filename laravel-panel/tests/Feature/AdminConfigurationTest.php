<?php

namespace Tests\Feature;

use App\Livewire\AdminWorkspace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_update_user_configuration(): void
    {
        $admin = $this->user('config-admin', 'admin');
        $target = $this->user('config-target', 'user');
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->getJson('/api/admin/users')->assertOk()->assertJsonPath('users.1.username', 'config-target');
        $this->actingAs($admin)->patchJson('/api/admin/users/'.$target->id, ['role' => 'admin', 'disabled' => true, 'password' => 'a much stronger password'])->assertOk()->assertJsonPath('user.role', 'admin');
        $target->refresh();
        $this->assertTrue($target->disabled);
        $this->assertTrue(Hash::check('a much stronger password', $target->password));
        $this->assertDatabaseHas('audit_events', ['action' => 'admin.user_role_changed', 'resource_id' => $target->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'admin.user_disabled', 'resource_id' => $target->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'admin.user_password_changed', 'resource_id' => $target->id]);
    }

    public function test_admin_cannot_grant_owner_or_modify_self_and_regular_users_are_denied(): void
    {
        $admin = $this->user('config-admin-2', 'admin');
        $target = $this->user('config-target-2', 'user');
        $regular = $this->user('config-regular', 'user');
        $this->actingAs($admin)->patchJson('/api/admin/users/'.$target->id, ['role' => 'owner'])->assertForbidden();
        $this->actingAs($admin)->patchJson('/api/admin/users/'.$admin->id, ['disabled' => true])->assertStatus(422);
        $this->actingAs($regular)->getJson('/api/admin/users')->assertForbidden();
    }

    public function test_admin_workspace_rechecks_staff_access_after_role_is_removed(): void
    {
        $admin = $this->user('workspace-admin', 'admin');
        $this->actingAs($admin);
        $component = Livewire::test(AdminWorkspace::class);

        $admin->forceFill(['role' => 'user'])->save();

        $component->call('saveSettings')->assertForbidden();
    }

    public function test_admin_can_update_default_hosting_quotas_from_workspace(): void
    {
        $admin = $this->user('quota-settings-admin', 'admin');

        Livewire::actingAs($admin)->test(AdminWorkspace::class)
            ->set('settings.quota_max_servers', 6)
            ->set('settings.quota_total_ram_mb', 12288)
            ->set('settings.quota_total_cpu_percent', 600)
            ->set('settings.quota_total_disk_mb', 102400)
            ->call('saveSettings')
            ->assertHasNoErrors()
            ->assertSee('Settings saved.');

        $this->assertDatabaseHas('settings', ['key' => 'quota_max_servers', 'value' => '6']);
        $this->assertDatabaseHas('settings', ['key' => 'quota_total_ram_mb', 'value' => '12288']);
        $this->assertDatabaseHas('settings', ['key' => 'quota_total_cpu_percent', 'value' => '600']);
        $this->assertDatabaseHas('settings', ['key' => 'quota_total_disk_mb', 'value' => '102400']);
        $this->assertDatabaseHas('audit_events', ['action' => 'settings.update', 'resource_type' => 'admin-workspace']);
    }

    public function test_admin_workspace_rejects_invalid_hosting_quotas(): void
    {
        $admin = $this->user('invalid-quota-settings-admin', 'admin');

        Livewire::actingAs($admin)->test(AdminWorkspace::class)
            ->set('settings.quota_max_servers', 0)
            ->call('saveSettings')
            ->assertHasErrors(['settings.quota_max_servers']);
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'password']);
    }
}
