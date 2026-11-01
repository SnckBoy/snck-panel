<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SftpIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_and_reset_sftp_credentials_without_get_password_leak(): void
    {
        $owner = $this->user('sftp-owner');
        $server = $this->server($owner);
        $created = $this->actingAs($owner)->postJson('/api/servers/'.$server->id.'/sftp/create')->assertOk();
        $password = $created->json('password');
        $created->assertJsonPath('username', 'sftp_'.$server->identifier)->assertJsonPath('port', 2022);
        $this->assertIsString($password);
        $this->assertGreaterThanOrEqual(24, strlen($password));
        $this->actingAs($owner)->getJson('/api/servers/'.$server->id.'/sftp')->assertOk()->assertJsonPath('password', null);
        $reset = $this->actingAs($owner)->postJson('/api/servers/'.$server->id.'/sftp/reset-password')->assertOk();
        $this->assertNotSame($password, $reset->json('password'));
        $this->assertTrue(Hash::check($reset->json('password'), (string) $server->refresh()->sftpUser?->password_hash));
        $this->actingAs($owner)->postJson('/api/servers/'.$server->id.'/sftp/create')->assertStatus(409);
    }

    public function test_delegated_subuser_cannot_manage_sftp(): void
    {
        $owner = $this->user('sftp-owner-2');
        $delegate = $this->user('sftp-delegate');
        $server = $this->server($owner);
        $server->subusers()->attach($delegate->id, ['permissions' => json_encode(['files', 'plugins'])]);
        $this->actingAs($delegate)->postJson('/api/servers/'.$server->id.'/sftp/create')->assertForbidden();
    }

    private function server(User $owner): Server
    {
        $node = Node::query()->create(['id' => 'node-'.$owner->id, 'name' => 'SFTP node', 'fqdn' => 'node.example.test', 'sftp_port' => 2022]);

        return Server::query()->create(['id' => 'server-'.$owner->id, 'identifier' => 'server-'.$owner->id, 'owner_id' => $owner->id, 'node_id' => $node->id, 'name' => 'SFTP server']);
    }

    private function user(string $id): User
    {
        return User::query()->create(['id' => $id, 'name' => $id, 'username' => $id, 'email' => $id.'@example.test', 'role' => 'user', 'password' => 'password']);
    }
}
