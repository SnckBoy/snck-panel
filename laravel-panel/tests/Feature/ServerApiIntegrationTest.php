<?php

namespace Tests\Feature;

use App\Models\Allocation;
use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ServerApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_server_creation_reserves_allocation_atomically_and_uses_daemon_contract_payload(): void
    {
        $owner = $this->user('server-owner', 'user');
        $node = $this->node('node-1');
        $allocation = Allocation::query()->create(['id' => 'alloc-1', 'node_id' => $node->id, 'ip' => '203.0.113.10', 'port' => 25571]);
        Http::fake(function ($request) {
            if ($request->url() === 'https://node.example.test:6768/v1/servers') return Http::response(['containerId' => str_repeat('a', 64)], 201);
            if (preg_match('#/v1/servers/[a-z0-9]+/status$#', $request->url())) return Http::response(['State' => ['Status' => 'running']], 200);
            return Http::response(['error' => 'Unexpected mock route'], 404);
        });

        $response = $this->actingAs($owner)->postJson('/api/servers', [
            'node_id' => $node->id,
            'allocation_id' => $allocation->id,
            'name' => 'My Minecraft Server',
            'type' => 'PAPER',
            'version' => '1.21.4',
            'java_version' => '21',
            'memory_limit_mb' => 512,
            'cpu_limit_percent' => 100,
            'environment_json' => ['MOTD' => 'Welcome to the server'],
        ]);

        $response->assertCreated()->assertJsonPath('server.status', 'running')->assertJsonPath('diskQuotaEnforced', false);
        $serverId = (string) $response->json('server.id');
        $this->assertDatabaseHas('allocations', ['id' => $allocation->id, 'assigned_server_id' => $serverId]);
        $this->assertDatabaseHas('servers', ['id' => $serverId, 'owner_id' => $owner->id, 'port' => 25571, 'status' => 'running']);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://node.example.test:6768/v1/servers'
            && ($request->data()['port'] ?? null) === 25571
            && (float) ($request->data()['ram'] ?? 0) === 0.5
            && ($request->data()['environment']['MOTD'] ?? null) === 'Welcome to the server'
            && strlen((string) ($request->data()['rconPassword'] ?? '')) >= 24);
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && preg_match('#/v1/servers/'.$serverId.'/status$#', $request->url()) === 1);
    }

    public function test_lifecycle_and_file_list_use_actual_daemon_methods_and_current_status(): void
    {
        $owner = $this->user('server-owner', 'user');
        $server = $this->server($owner);
        Http::fake([
            'https://node.example.test:6768/v1/servers/server-1/start' => Http::response(['success' => true]),
            'https://node.example.test:6768/v1/servers/server-1/status' => Http::response(['State' => ['Status' => 'running']]),
            'https://node.example.test:6768/v1/servers/server-1/files/list' => Http::response([['name' => 'plugins', 'isDirectory' => true, 'size' => 0]]),
        ]);

        $this->actingAs($owner)->postJson('/api/servers/server-1/start')->assertOk()->assertJsonPath('server.status', 'running');
        $this->getJson('/api/servers/server-1/files?path=/')->assertOk()->assertJsonCount(1);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://node.example.test:6768/v1/servers/server-1/files/list'
            && ($request->data()['path'] ?? null) === '.');
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://node.example.test:6768/v1/servers/server-1/start');
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request->url() === 'https://node.example.test:6768/v1/servers/server-1/status');
    }

    public function test_subusers_are_server_scoped_and_cannot_use_console_or_wildcard_grants(): void
    {
        $owner = $this->user('server-owner', 'user');
        $delegate = $this->user('server-delegate', 'user');
        $outsider = $this->user('server-outsider', 'user');
        $server = $this->server($owner);
        Http::fake(['*' => Http::response([['name' => 'server.properties', 'isDirectory' => false, 'size' => 12]])]);

        $this->actingAs($owner)->putJson('/api/servers/server-1/subusers', [
            'user_id' => $delegate->id,
            'permissions' => ['files', 'start'],
        ])->assertCreated()->assertJsonPath('permissions.0', 'files');
        $this->assertDatabaseHas('server_subusers', ['server_id' => $server->id, 'user_id' => $delegate->id]);

        $this->actingAs($delegate)->getJson('/api/servers/server-1/files?path=.')->assertOk();
        $this->actingAs($delegate)->postJson('/api/servers/server-1/command', ['command' => 'stop'])->assertForbidden();
        $this->actingAs($delegate)->putJson('/api/servers/server-1/subusers', ['user_id' => $outsider->id, 'permissions' => ['files']])->assertForbidden();
        $this->actingAs($outsider)->getJson('/api/servers/server-1/files?path=.')->assertForbidden();
        $this->actingAs($owner)->putJson('/api/servers/server-1/subusers', ['user_id' => $outsider->id, 'permissions' => ['*']])->assertUnprocessable();
        $this->actingAs($owner)->deleteJson('/api/servers/server-1/subusers/'.$delegate->id)->assertOk();
        $this->assertDatabaseMissing('server_subusers', ['server_id' => $server->id, 'user_id' => $delegate->id]);
    }

    public function test_traversal_paths_are_rejected_before_daemon_request(): void
    {
        $owner = $this->user('server-owner', 'user');
        $this->server($owner);
        Http::preventStrayRequests();
        $this->actingAs($owner)->getJson('/api/servers/server-1/files/read?path=..%2Fsecrets.json')->assertUnprocessable();
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'correct horse battery staple']);
    }

    private function node(string $id): Node
    {
        return Node::query()->create([
            'id' => $id,
            'name' => 'Test node',
            'fqdn' => 'node.example.test',
            'daemon_port' => 6768,
            'credential_encrypted' => Crypt::encryptString('node-test-token'),
            'credential_hash' => hash('sha256', 'node-test-token'),
            'status' => 'online',
            'docker_available' => true,
            'last_heartbeat_at' => now(),
            'memory_limit_mb' => 4096,
        ]);
    }

    private function server(User $owner): Server
    {
        $node = $this->node('node-1');
        return Server::query()->create([
            'id' => 'server-1',
            'identifier' => 'srv-test-1',
            'owner_id' => $owner->id,
            'node_id' => $node->id,
            'name' => 'Test server',
            'port' => 25565,
            'status' => 'offline',
        ]);
    }
}
