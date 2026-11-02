<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminControlCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_admin_can_open_admin_control_center(): void
    {
        foreach (['owner', 'admin'] as $role) {
            $user = $this->user('control-'.$role, $role);

            $this->actingAs($user)->get('/admin')->assertOk()->assertSee('Administration')->assertSee('Panel settings')->assertSee('Nodes');
        }
    }

    public function test_regular_user_cannot_open_admin_control_center(): void
    {
        $user = $this->user('control-user', 'user');

        $this->actingAs($user)->get('/admin')->assertForbidden();
        Livewire::actingAs($user)->test(\App\Livewire\AdminDashboard::class)->assertForbidden();
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create([
            'id' => $id,
            'name' => $id,
            'username' => $id,
            'email' => $id.'@example.test',
            'role' => $role,
            'password' => 'password',
        ]);
    }
}
