<?php

namespace Tests\Feature;

use App\Livewire\AdminUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_open_an_owner_for_editing(): void
    {
        $admin = $this->user('admin-1', 'admin');
        $owner = $this->user('owner-1', 'owner');

        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->call('edit', $owner->id)
            ->assertForbidden();
    }

    public function test_last_owner_cannot_be_demoted_or_disabled(): void
    {
        $owner = $this->user('owner-1', 'owner');

        Livewire::actingAs($owner)
            ->test(AdminUsers::class)
            ->call('edit', $owner->id)
            ->set('role', 'admin')
            ->call('save')
            ->assertHasErrors();

        $owner->refresh();
        $this->assertSame('owner', $owner->role);
        $this->assertFalse($owner->disabled);

        Livewire::actingAs($owner)
            ->test(AdminUsers::class)
            ->call('edit', $owner->id)
            ->set('disabled', true)
            ->call('save')
            ->assertHasErrors();

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'owner', 'disabled' => false]);
    }

    public function test_role_disable_and_password_changes_each_write_an_audit_event(): void
    {
        $owner = $this->user('owner-1', 'owner');
        $member = $this->user('member-1', 'user');

        Livewire::actingAs($owner)
            ->test(AdminUsers::class)
            ->call('edit', $member->id)
            ->set('role', 'admin')
            ->set('disabled', true)
            ->set('password', 'new-secure-password-123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $owner->id,
            'action' => 'admin.user_role_changed',
            'resource_type' => 'user',
            'resource_id' => $member->id,
            'result' => 'success',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $owner->id,
            'action' => 'admin.user_disabled',
            'resource_type' => 'user',
            'resource_id' => $member->id,
            'result' => 'success',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $owner->id,
            'action' => 'admin.user_password_reset',
            'resource_type' => 'user',
            'resource_id' => $member->id,
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
            'password' => 'correct horse battery staple',
        ]);
    }
}
