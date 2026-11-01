<?php

namespace Tests\Feature;

use App\Livewire\AdminUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsersAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_open_or_change_an_owner_account_in_livewire(): void
    {
        $admin = $this->user('admin-one', 'admin');
        $owner = $this->user('owner-one', 'owner');

        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->call('edit', $owner->id)
            ->assertForbidden();

        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->set('editing', $owner->id)
            ->set('role', 'user')
            ->set('disabled', true)
            ->set('password', 'a-new-secure-password')
            ->call('save')
            ->assertForbidden();

        $this->assertSame('owner', $owner->fresh()->role);
        $this->assertFalse($owner->fresh()->disabled);
        $this->assertSame(0, DB::table('audit_events')->where('resource_id', $owner->id)->count());
    }

    public function test_last_owner_cannot_be_demoted_disabled_or_deleted(): void
    {
        $owner = $this->user('last-owner', 'owner');

        Livewire::actingAs($owner)->test(AdminUsers::class)
            ->call('edit', $owner->id)
            ->set('role', 'admin')
            ->call('save')
            ->assertStatus(422);

        Livewire::actingAs($owner)->test(AdminUsers::class)
            ->call('edit', $owner->id)
            ->set('disabled', true)
            ->call('save')
            ->assertStatus(422);

        Livewire::actingAs($owner)->test(AdminUsers::class)
            ->call('delete', $owner->id)
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'owner', 'disabled' => false]);
    }

    public function test_role_disable_and_password_changes_each_write_an_audit_event(): void
    {
        $owner = $this->user('audit-owner', 'owner');
        $member = $this->user('audit-member', 'user');

        Livewire::actingAs($owner)->test(AdminUsers::class)
            ->call('edit', $member->id)
            ->set('role', 'admin')
            ->set('disabled', true)
            ->set('password', 'a-new-secure-password')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $owner->id,
            'resource_type' => 'user',
            'resource_id' => $member->id,
            'action' => 'admin.user_role_changed',
            'result' => 'success',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $owner->id,
            'resource_type' => 'user',
            'resource_id' => $member->id,
            'action' => 'admin.user_disabled',
            'result' => 'success',
        ]);
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $owner->id,
            'resource_type' => 'user',
            'resource_id' => $member->id,
            'action' => 'admin.user_password_reset',
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
            'password' => 'test-password-123',
        ]);
    }
}
