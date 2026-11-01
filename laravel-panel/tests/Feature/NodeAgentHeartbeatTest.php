<?php

namespace Tests\Feature;

use App\Models\Node;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NodeAgentHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_heartbeat_persists_only_observed_daemon_and_docker_status(): void
    {
        $node = $this->node('heartbeat-token');
        $this->postJson('/api/node-agent/heartbeat', [
            'nodeId' => $node->id,
            'stats' => ['docker' => false, 'daemonVersion' => '1.1.0', 'timestamp' => now()->toISOString()],
        ], ['Authorization' => 'Bearer heartbeat-token'])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('nodes', ['id' => $node->id, 'status' => 'online', 'docker_available' => false, 'daemon_version' => '1.1.0']);
        $this->assertNotNull(DB::table('nodes')->where('id', $node->id)->value('last_heartbeat_at'));
    }

    public function test_invalid_token_and_disabled_node_cannot_update_status(): void
    {
        $node = $this->node('valid-secret');
        $this->postJson('/api/node-agent/heartbeat', ['nodeId' => $node->id, 'stats' => ['docker' => true]], ['Authorization' => 'Bearer invalid-secret'])->assertUnauthorized();
        $this->assertDatabaseHas('nodes', ['id' => $node->id, 'status' => 'offline', 'docker_available' => false]);

        DB::table('nodes')->where('id', $node->id)->update(['disabled' => true]);
        $this->postJson('/api/node-agent/heartbeat', ['nodeId' => $node->id, 'stats' => ['docker' => true]], ['Authorization' => 'Bearer valid-secret'])->assertForbidden();
        $this->assertDatabaseHas('nodes', ['id' => $node->id, 'status' => 'offline', 'docker_available' => false]);
    }

    public function test_legacy_imported_encrypted_node_secret_is_verified_then_hash_backfilled(): void
    {
        $node = $this->node('legacy-secret');
        DB::table('nodes')->where('id', $node->id)->update(['credential_hash' => null]);
        $this->postJson('/api/node-agent/heartbeat', ['nodeId' => $node->id, 'stats' => ['docker' => true]], ['Authorization' => 'Bearer legacy-secret'])->assertOk();
        $this->assertDatabaseHas('nodes', ['id' => $node->id, 'credential_hash' => hash('sha256', 'legacy-secret')]);
    }

    private function node(string $token): Node
    {
        return Node::query()->create([
            'id' => 'node-heartbeat',
            'name' => 'Heartbeat node',
            'credential_encrypted' => Crypt::encryptString($token),
            'credential_hash' => hash('sha256', $token),
            'status' => 'offline',
            'docker_available' => false,
        ]);
    }
}
