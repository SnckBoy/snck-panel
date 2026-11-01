<?php

namespace Tests\Feature;

use App\Livewire\AdminUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_open_an_owner_for_editing_in_livewire(): void
    {
        $admin = $this->user('admin-1', 'admin');
        $owner = $this->user('owner-1', 'owner');

        $this->actingAs($admin);

        Livewire::test(AdminUsers::class)
            ->call('edit', $owner->id)
            ->assertForbidden();

        Livewire::test(AdminUsers::class)
            ->set('editing', $owner->id)
            ->set('role', 'user')
            ->set('disabled', true)
            ->set('password', 'attempted-owner-password')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'role' => 'owner',
            'disabled' => false,
        ]);
    }

    public function test_last_owner_cannot_be_demoted_or_disabled_through_api(): void
    {
        $owner = $this->user('owner-1', 'owner');

        $this->actingAs($owner);

        $this->patchJson('/api/admin/users/'.$owner->id, ['role' => 'admin'])
            ->assertUnprocessable();

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'owner', 'disabled' => false]);

        $this->patchJson('/api/admin/users/'.$owner->id, ['disabled' => true])
            ->assertUnprocessable();

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'owner', 'disabled' => false]);

        $this->deleteJson('/api/admin/users/'.$owner->id)
            ->assertUnprocessable();

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'owner', 'disabled' => false]);
    }

    public function test_role_disable_and_password_changes_each_write_an_audit_event(): void
    {
        $admin = $this->user('admin-1', 'admin');
        $target = $this->user('member-1', 'user');

        $this->actingAs($admin);

        Livewire::test(AdminUsers::class)
            ->call('edit', $target->id)
            ->set('role', 'admin')
            ->set('disabled', true)
            ->set('password', 'a-new-long-password')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'admin', 'disabled' => true]);
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $admin->id,
            'action' => 'admin.user_role_changed',
            'resource_type' => 'user',
            'resource_id' => $target->id,
            'result' => 'success',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $admin->id,
            'action' => 'admin.user_disabled',
            'resource_type' => 'user',
            'resource_id' => $target->id,
            'result' => 'success',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $admin->id,
            'action' => 'admin.user_password_changed',
            'resource_type' => 'user',
            'resource_id' => $target->id,
            'result' => 'success',
        ]);
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create([
            'id' => $id,
            'name' => $id,
            'username' => $id,
            'email' => $id.'@example.test',
            'role' => $role,
            'disabled' => false,
            'password' => 'test-password-123',
        ]);
    }
}
