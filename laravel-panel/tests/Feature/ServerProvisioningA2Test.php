<?php

namespace Tests\Feature;

use App\Livewire\ServerCreate;
use App\Models\Allocation;
use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ServerProvisioningA2Test extends TestCase
{
    use RefreshDatabase;

    public function test_non_staff_user_is_rejected_when_the_server_would_exceed_quota(): void
    {
        $owner = $this->user('quota-owner', 'user');
        $node = $this->node('quota-node');
        $allocation = $this->allocation($node, 'quota-port-2', 25566);
        Server::query()->create($this->serverData($owner, $node, 'existing', 1024, 100, 10240));
        $this->setting('quota_max_servers', 5);
        $this->setting('quota_total_ram_mb', 2048);
        $this->setting('quota_total_cpu_percent', 400);
        $this->setting('quota_total_disk_mb', 51200);
        Http::preventStrayRequests();

        $this->actingAs($owner)->postJson('/api/servers', $this->payload($node, $allocation, $owner, 2048, 100, 10240))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Your total RAM quota would be exceeded.');
    }

    public function test_non_staff_cannot_choose_another_owner_through_api_or_livewire(): void
    {
        $actor = $this->user('actor', 'user');
        $other = $this->user('other', 'user');
        $node = $this->node('owner-node');
        $allocation = $this->allocation($node, 'owner-port', 25567);
        $payload = $this->payload($node, $allocation, $other, 512, 100, 10240);

        $this->actingAs($actor)->postJson('/api/servers', $payload)->assertForbidden();

        Livewire::actingAs($actor)->test(ServerCreate::class)
            ->set('nodeId', $node->id)
            ->set('allocationId', $allocation->id)
            ->set('ownerId', $other->id)
            ->set('name', 'forbidden server')
            ->set('memoryLimitMb', 512)
            ->set('cpuLimitPercent', 100)
            ->set('diskLimitMb', 10240)
            ->call('create')
            ->assertStatus(403);
    }

    public function test_staff_can_create_for_another_owner(): void
    {
        $staff = $this->user('staff', 'admin');
        $owner = $this->user('delegated-owner', 'user');
        $node = $this->node('staff-node');
        $allocation = $this->allocation($node, 'staff-port', 25568);
        $this->fakeDaemon();

        $this->actingAs($staff)->postJson('/api/servers', $this->payload($node, $allocation, $owner, 512, 100, 10240))
            ->assertCreated()
            ->assertJsonPath('server.owner_id', $owner->id);
    }

    public function test_livewire_creation_provisions_and_redirects_to_server_page(): void
    {
        $owner = $this->user('livewire-owner', 'user');
        $node = $this->node('livewire-node');
        $allocation = $this->allocation($node, 'livewire-port', 25569);
        $this->fakeDaemon();

        Livewire::actingAs($owner)->test(ServerCreate::class)
            ->set('nodeId', $node->id)
            ->set('allocationId', $allocation->id)
            ->set('ownerId', $owner->id)
            ->set('name', 'Livewire SMP')
            ->set('type', 'PAPER')
            ->set('version', '1.21.4')
            ->set('javaVersion', '21')
            ->set('memoryLimitMb', 512)
            ->set('cpuLimitPercent', 100)
            ->set('diskLimitMb', 10240)
            ->call('create')
            ->assertRedirect();

        $this->assertDatabaseHas('servers', ['name' => 'Livewire SMP', 'owner_id' => $owner->id, 'status' => 'running']);
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'correct horse battery staple']);
    }

    private function node(string $id): Node
    {
        return Node::query()->create([
            'id' => $id, 'name' => 'Test node', 'fqdn' => 'node.example.test', 'daemon_port' => 6768,
            'credential_encrypted' => Crypt::encryptString('node-test-token'), 'credential_hash' => hash('sha256', 'node-test-token'),
            'status' => 'online', 'docker_available' => true, 'last_heartbeat_at' => now(), 'memory_limit_mb' => 4096,
        ]);
    }

    private function allocation(Node $node, string $id, int $port): Allocation
    {
        return Allocation::query()->create(['id' => $id, 'node_id' => $node->id, 'ip' => '203.0.113.10', 'port' => $port]);
    }

    private function serverData(User $owner, Node $node, string $id, int $memory, int $cpu, int $disk): array
    {
        return ['id' => $id, 'identifier' => $id.'-identifier', 'owner_id' => $owner->id, 'node_id' => $node->id, 'name' => $id, 'image' => 'itzg/minecraft-server:latest', 'type' => 'PAPER', 'version' => 'latest', 'status' => 'running', 'memory_limit_mb' => $memory, 'ram_gb' => $memory / 1024, 'cpu_limit_percent' => $cpu, 'disk_limit_mb' => $disk, 'port' => 25565];
    }

    private function payload(Node $node, Allocation $allocation, User $owner, int $memory, int $cpu, int $disk): array
    {
        return ['node_id' => $node->id, 'allocation_id' => $allocation->id, 'owner_id' => $owner->id, 'name' => 'New server', 'type' => 'PAPER', 'version' => 'latest', 'java_version' => '21', 'memory_limit_mb' => $memory, 'cpu_limit_percent' => $cpu, 'disk_limit_mb' => $disk];
    }

    private function setting(string $key, int $value): void
    {
        \DB::table('settings')->updateOrInsert(['key' => $key], ['value' => json_encode($value), 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function fakeDaemon(): void
    {
        Http::fake(function ($request) {
            if (str_ends_with($request->url(), '/v1/servers')) return Http::response(['containerId' => str_repeat('a', 64)], 201);
            if (str_ends_with($request->url(), '/status')) return Http::response(['State' => ['Status' => 'running']], 200);
            return Http::response(['error' => 'Unexpected mock route'], 404);
        });
    }
}
