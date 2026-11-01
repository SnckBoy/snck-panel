<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use App\Services\PlayitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayitIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_read_playit_status_and_delegated_user_is_denied(): void
    {
        $owner = $this->user('playit-owner', 'user');
        $delegate = $this->user('playit-delegate', 'user');
        $server = $this->server($owner);
        $server->subusers()->attach($delegate->id, ['permissions' => json_encode(['files'])]);
        $this->mock(PlayitService::class, fn ($mock) => $mock->shouldReceive('status')->once()->andReturn(['status' => 'stopped', 'claimLink' => null, 'logs' => '']));

        $this->actingAs($owner)->getJson('/api/servers/'.$server->id.'/playit')->assertOk()->assertJsonPath('status', 'stopped');
        $this->actingAs($delegate)->getJson('/api/servers/'.$server->id.'/playit')->assertForbidden();
    }

    public function test_owner_start_is_audited_and_regular_user_cannot_start_another_server(): void
    {
        $owner = $this->user('playit-owner-2', 'user');
        $other = $this->user('playit-other', 'user');
        $server = $this->server($owner);
        $this->mock(PlayitService::class, fn ($mock) => $mock->shouldReceive('start')->once()->andReturn(['success' => true]));

        $this->actingAs($owner)->postJson('/api/servers/'.$server->id.'/playit/start')->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('audit_events', ['action' => 'playit.started', 'resource_id' => $server->id, 'user_id' => $owner->id]);
        $this->actingAs($other)->postJson('/api/servers/'.$server->id.'/playit/start')->assertForbidden();
    }

    private function server(User $owner): Server
    {
        $node = Node::query()->create(['id' => 'node-'.$owner->id, 'name' => 'Playit node']);

        return Server::query()->create(['id' => 'server-'.$owner->id, 'identifier' => 'server-'.$owner->id, 'owner_id' => $owner->id, 'node_id' => $node->id, 'name' => 'Minecraft: '.$owner->id]);
    }

    private function user(string $id, string $role): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => $role, 'password' => 'correct horse battery staple']);
    }
}
