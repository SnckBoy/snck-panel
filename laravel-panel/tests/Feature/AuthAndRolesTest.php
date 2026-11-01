<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_first_owner_can_complete_setup(): void
    {
        $this->get('/setup')->assertOk();
        $this->post('/setup', [
            'username' => 'First.Owner',
            'email' => 'owner@example.test',
            'password' => 'a-long-test-password',
            'password_confirmation' => 'a-long-test-password',
        ])->assertRedirect('/dashboard');

        $owner = User::query()->firstOrFail();
        $this->assertSame('owner', $owner->role);
        $this->assertSame('first.owner', $owner->username);
        $this->assertTrue(Hash::check('a-long-test-password', $owner->password));
        $this->assertNotNull(DB::table('setup_state')->where('id', 1)->value('completed_at'));
        $secondAttempt = $this->post('/setup', [
            'username' => 'second-owner',
            'email' => 'second@example.test',
            'password' => 'a-long-test-password',
            'password_confirmation' => 'a-long-test-password',
        ]);
        $this->assertContains($secondAttempt->getStatusCode(), [302, 403, 422]);
        $this->assertSame(1, User::query()->where('role', 'owner')->count());
    }

    public function test_username_login_and_dashboard_use_database_roles(): void
    {
        $user = $this->makeUser('member', 'user', false);
        $this->post('/login', [
            'identity' => 'MEMBER',
            'password' => 'test-password-123',
        ])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk()->assertSee('Your accessible servers');
        $this->get('/api/nodes')->assertForbidden();
    }

    public function test_disabled_account_cannot_sign_in_or_use_authenticated_pages(): void
    {
        $user = $this->makeUser('disabled-user', 'user', true);
        $this->post('/login', [
            'identity' => $user->username,
            'password' => 'test-password-123',
        ])->assertSessionHasErrors('identity');
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    public function test_registration_is_closed_until_owner_enables_it(): void
    {
        $this->makeUser('owner-user', 'owner', false);
        $this->get('/register')->assertRedirect('/login');
        $this->post('/register', [
            'username' => 'new-user',
            'email' => 'new@example.test',
            'password' => 'test-password-123',
            'password_confirmation' => 'test-password-123',
        ])->assertForbidden();
        $this->assertDatabaseMissing('users', ['username' => 'new-user']);
    }

    public function test_enabled_registration_creates_a_regular_user_not_staff(): void
    {
        $this->makeUser('owner-user', 'owner', false);
        DB::table('settings')->insert([
            'key' => 'enableRegistration',
            'value' => json_encode(true),
            'is_encrypted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->post('/register', [
            'username' => 'new-user',
            'email' => 'new@example.test',
            'password' => 'test-password-123',
            'password_confirmation' => 'test-password-123',
        ])->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['username' => 'new-user', 'role' => 'user']);
    }

    private function makeUser(string $username, string $role, bool $disabled): User
    {
        return User::query()->create([
            'id' => $username,
            'name' => $username,
            'username' => $username,
            'email' => $username.'@example.test',
            'role' => $role,
            'disabled' => $disabled,
            'password' => 'test-password-123',
        ]);
    }
}
