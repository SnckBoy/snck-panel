<?php

namespace Tests\Feature;

use App\Livewire\ServerDetail;
use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class Batch1ServerExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_properties_can_be_loaded_and_saved_through_the_livewire_server_view(): void
    {
        $owner = $this->user('batch1-owner');
        $this->server($owner);
        Http::fake([
            'https://node.example.test:6768/v1/servers/server-1/files/read' => Http::response(['content' => "motd=Snck\nmax-players=20\n"]),
            'https://node.example.test:6768/v1/servers/server-1/files/write' => Http::response(['success' => true]),
        ]);

        Livewire::actingAs($owner)->test(ServerDetail::class, ['server' => 'server-1'])
            ->call('setTab', 'properties')
            ->assertSet('propertiesContent', "motd=Snck\nmax-players=20\n")
            ->set('propertiesContent', "motd=Updated\nmax-players=30\n")
            ->call('saveProperties')
            ->assertSet('notice', 'server.properties saved.');

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && str_ends_with($request->url(), '/v1/servers/server-1/files/write')
            && ($request->data()['path'] ?? null) === 'server.properties'
            && ($request->data()['content'] ?? null) === "motd=Updated\nmax-players=30\n");
    }

    public function test_players_view_uses_the_minecraft_list_command_and_parses_names(): void
    {
        $owner = $this->user('batch1-player-owner');
        $this->server($owner);
        Http::fake([
            'https://node.example.test:6768/v1/servers/server-1/command' => Http::response([
                'success' => true,
                'output' => '[Server thread/INFO]: There are 2 of a max of 20 players online: Alex, Steve',
            ]),
        ]);

        Livewire::actingAs($owner)->test(ServerDetail::class, ['server' => 'server-1'])
            ->call('setTab', 'players')
            ->assertSet('players', ['Alex', 'Steve'])
            ->assertSet('maxPlayers', 20);
    }

    public function test_console_and_logs_are_loaded_from_the_daemon_contract(): void
    {
        $owner = $this->user('batch1-console-owner');
        $this->server($owner);
        Http::fake([
            'https://node.example.test:6768/v1/servers/server-1/logs' => Http::response("[12:00:00 INFO]: Done\n[12:00:01 INFO]: Ready\n", 200, ['Content-Type' => 'text/plain']),
        ]);

        Livewire::actingAs($owner)->test(ServerDetail::class, ['server' => 'server-1'])
            ->call('setTab', 'console')
            ->assertSet('logs', "[12:00:00 INFO]: Done\n[12:00:01 INFO]: Ready\n")
            ->set('logFilter', 'ready')
            ->call('refreshLogs')
            ->assertSet('noticeType', 'success');
    }

    public function test_non_owner_cannot_open_properties_or_console(): void
    {
        $owner = $this->user('batch1-protected-owner');
        $outsider = $this->user('batch1-outsider');
        $this->server($owner);
        Livewire::actingAs($outsider)->test(ServerDetail::class, ['server' => 'server-1'])
            ->assertStatus(403);
    }

    private function user(string $id): User
    {
        return User::query()->create([
            'id' => $id, 'name' => $id, 'username' => $id,
            'email' => $id.'@example.test', 'role' => 'user',
            'password' => 'correct horse battery staple',
        ]);
    }

    private function server(User $owner): Server
    {
        $node = Node::query()->create([
            'id' => 'node-1', 'name' => 'Test node', 'fqdn' => 'node.example.test',
            'daemon_port' => 6768, 'credential_encrypted' => Crypt::encryptString('node-token'),
            'credential_hash' => hash('sha256', 'node-token'), 'status' => 'online',
            'docker_available' => true, 'last_heartbeat_at' => now(), 'memory_limit_mb' => 4096,
        ]);

        return Server::query()->create([
            'id' => 'server-1', 'identifier' => 'srv-test-1', 'owner_id' => $owner->id,
            'node_id' => $node->id, 'name' => 'Test server', 'port' => 25565,
            'status' => 'running', 'memory_limit_mb' => 512, 'cpu_limit_percent' => 100,
            'disk_limit_mb' => 10240,
        ]);
    }
}
