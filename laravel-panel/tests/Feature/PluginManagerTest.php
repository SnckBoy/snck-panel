<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use App\Services\PluginManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PluginManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_server_owner_or_delegated_plugin_permission_can_list_plugins(): void
    {
        $owner = $this->user('plugin-owner');
        $delegate = $this->user('plugin-delegate');
        $outsider = $this->user('plugin-outsider');
        $server = $this->server($owner);
        $server->subusers()->attach($delegate->id, ['permissions' => json_encode(['plugins'])]);
        $this->mock(PluginManagerService::class, fn ($mock) => $mock->shouldReceive('installed')->twice()->andReturn(['items' => [['filename' => 'example.jar', 'size' => 12]]]));
        $this->actingAs($owner)->getJson('/api/servers/'.$server->id.'/plugins/installed')->assertOk()->assertJsonPath('items.0.filename', 'example.jar');
        $this->actingAs($delegate)->getJson('/api/servers/'.$server->id.'/plugins/installed')->assertOk();
        $this->actingAs($outsider)->getJson('/api/servers/'.$server->id.'/plugins/installed')->assertForbidden();
    }

    public function test_mod_install_requires_mod_permission_and_modpack_upload_is_size_validated(): void
    {
        $owner = $this->user('mod-owner');
        $server = $this->server($owner);
        $this->mock(PluginManagerService::class, fn ($mock) => $mock->shouldReceive('install')->once()->andReturn(['success' => true, 'filename' => 'mod.jar']));
        $this->actingAs($owner)->postJson('/api/servers/'.$server->id.'/mods/install', ['projectId' => 'create']);
        $this->actingAs($owner)->postJson('/api/servers/'.$server->id.'/modpacks/import', ['confirmReplace' => false])->assertUnprocessable();
    }

    private function server(User $owner): Server
    {
        $node = Node::query()->create(['id' => 'node-'.$owner->id, 'name' => 'Node']);

        return Server::query()->create(['id' => 'server-'.$owner->id, 'identifier' => 'server-'.$owner->id, 'owner_id' => $owner->id, 'node_id' => $node->id, 'name' => 'Test server', 'version' => '1.21.1', 'type' => 'PAPER']);
    }

    private function user(string $id): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => 'user', 'password' => 'password']);
    }
}
