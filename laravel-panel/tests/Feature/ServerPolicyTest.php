<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ServerPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_staff_have_full_server_access(): void
    {
        $owner = $this->user('owner-1', 'owner');
        $admin = $this->user('admin-1', 'admin');
        $server = $this->server($owner);

        $this->assertTrue(Gate::forUser($owner)->allows('start', $server));
        $this->assertTrue(Gate::forUser($owner)->allows('sftp', $server));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $server));
        $this->assertTrue(Gate::forUser($admin)->allows('sftp', $server));
    }

    public function test_subuser_receives_only_explicit_delegated_grants_and_not_sftp(): void
    {
        $owner = $this->user('owner-1', 'owner');
        $delegate = $this->user('user-1', 'user');
        $server = $this->server($owner);
        DB::table('server_subusers')->insert([
            'server_id' => $server->id,
            'user_id' => $delegate->id,
            'permissions' => json_encode(['files', 'start', 'unknown_permission']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(Gate::forUser($delegate)->allows('view', $server));
        $this->assertTrue(Gate::forUser($delegate)->allows('files', $server));
        $this->assertTrue(Gate::forUser($delegate)->allows('start', $server));
        $this->assertFalse(Gate::forUser($delegate)->allows('stop', $server));
        $this->assertFalse(Gate::forUser($delegate)->allows('sftp', $server));
        $this->assertFalse(Gate::forUser($delegate)->allows('manageSubusers', $server));
    }

    public function test_wildcard_grant_does_not_become_full_access_for_subuser(): void
    {
        $owner = $this->user('owner-1', 'owner');
        $delegate = $this->user('user-1', 'user');
        $server = $this->server($owner);
        DB::table('server_subusers')->insert([
            'server_id' => $server->id,
            'user_id' => $delegate->id,
            'permissions' => json_encode(['*']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(Gate::forUser($delegate)->allows('view', $server));
        $this->assertFalse(Gate::forUser($delegate)->allows('start', $server));
        $this->assertFalse(Gate::forUser($delegate)->allows('sftp', $server));
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

    private function server(User $owner): Server
    {
        $node = Node::query()->create(['id' => 'node-1', 'name' => 'Test node', 'status' => 'offline']);

        return Server::query()->create([
            'id' => 'server-1',
            'identifier' => 'srv-test-1',
            'owner_id' => $owner->id,
            'node_id' => $node->id,
            'name' => 'Test server',
        ]);
    }
}
