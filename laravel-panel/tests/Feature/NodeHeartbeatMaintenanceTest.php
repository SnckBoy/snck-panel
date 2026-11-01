<?php

namespace Tests\Feature;

use App\Models\Node;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class NodeHeartbeatMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_heartbeat_authentication_is_limited_by_client_ip(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.42']);
        $payload = ['nodeId' => 'nonexistent-node', 'stats' => ['docker' => true]];

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->postJson('/api/node-agent/heartbeat', $payload)
                ->assertUnauthorized();
        }

        $this->postJson('/api/node-agent/heartbeat', $payload)->assertStatus(429);
    }

    public function test_scheduled_maintenance_marks_nodes_offline_after_45_seconds(): void
    {
        Date::setTestNow('2026-10-29 08:00:00');
        $stale = Node::query()->create([
            'id' => 'stale-node',
            'name' => 'Stale node',
            'host' => 'node.example.test',
            'daemon_port' => 6768,
            'api_port' => 6768,
            'credential_encrypted' => Crypt::encryptString('node-secret'),
            'credential_hash' => hash('sha256', 'node-secret'),
            'status' => 'online',
            'docker_available' => true,
            'last_heartbeat_at' => now()->subSeconds(46),
        ]);
        $recent = Node::query()->create([
            'id' => 'recent-node',
            'name' => 'Recent node',
            'host' => 'recent.example.test',
            'daemon_port' => 6768,
            'api_port' => 6768,
            'credential_encrypted' => Crypt::encryptString('another-secret'),
            'credential_hash' => hash('sha256', 'another-secret'),
            'status' => 'online',
            'docker_available' => true,
            'last_heartbeat_at' => now()->subSeconds(44),
        ]);

        $this->artisan('snck:nodes-mark-offline')->assertSuccessful();

        $this->assertSame('offline', $stale->fresh()->status);
        $this->assertFalse($stale->fresh()->docker_available);
        $this->assertSame('online', $recent->fresh()->status);
    }
}
