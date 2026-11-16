<?php

namespace Tests\Feature;

use App\Livewire\AdminAuditLogs;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuditLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_review_and_filter_audit_events(): void
    {
        $admin = $this->user('audit-admin', 'admin');
        DB::table('audit_events')->insert([
            [
                'user_id' => $admin->id,
                'action' => 'server.created',
                'resource_type' => 'server',
                'resource_id' => 'server-123',
                'result' => 'success',
                'ip_address' => '192.0.2.10',
                'metadata' => json_encode(['name' => 'Survival']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $admin->id,
                'action' => 'auth.login_failed',
                'resource_type' => 'session',
                'resource_id' => null,
                'result' => 'failure',
                'ip_address' => '192.0.2.11',
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminAuditLogs::class)
            ->assertSee('server.created')
            ->assertSee('auth.login_failed')
            ->set('filterResult', 'failure')
            ->assertSee('auth.login_failed')
            ->assertDontSee('server.created')
            ->set('filterResult', 'all')
            ->set('search', 'server-123')
            ->assertSee('server.created')
            ->assertDontSee('auth.login_failed');
    }

    public function test_regular_users_cannot_open_the_audit_console(): void
    {
        $user = $this->user('audit-member', 'user');

        $this->actingAs($user)
            ->get('/admin/audit-logs')
            ->assertForbidden();
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
