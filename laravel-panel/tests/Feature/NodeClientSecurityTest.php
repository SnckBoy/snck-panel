<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Services\NodeClient;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class NodeClientSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_uses_the_frozen_get_route_over_https_with_the_node_bearer(): void
    {
        $node = Node::query()->create([
            'id' => 'node-https',
            'name' => 'HTTPS node',
            'fqdn' => 'node.example.test',
            'daemon_port' => 6768,
            'credential_encrypted' => Crypt::encryptString('per-node-secret'),
        ]);
        Http::fake(['https://node.example.test:6768/v1/health' => Http::response(['ok' => true, 'docker' => true], 200)]);

        $result = app(NodeClient::class)->health($node);

        $this->assertTrue($result['docker']);
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request->url() === 'https://node.example.test:6768/v1/health'
            && ($request->header('Authorization')[0] ?? null) === 'Bearer per-node-secret');
    }

    public function test_http_and_missing_node_credentials_fail_closed(): void
    {
        $node = Node::query()->create([
            'id' => 'node-insecure',
            'name' => 'HTTP node',
            'fqdn' => 'node.example.test',
            'daemon_port' => 6768,
            'credential_encrypted' => Crypt::encryptString('per-node-secret'),
        ]);
        DB::table('nodes')->where('id', $node->id)->update(['protocol' => 'http']);
        $node->refresh();
        $this->expectException(RuntimeException::class);
        app(NodeClient::class)->request($node);
    }

    public function test_missing_bearer_secret_is_rejected_before_network_access(): void
    {
        $node = Node::query()->create([
            'id' => 'node-no-secret',
            'name' => 'No secret',
            'fqdn' => 'node.example.test',
            'daemon_port' => 6768,
        ]);
        Http::preventStrayRequests();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no stored bearer credential');
        app(NodeClient::class)->request($node);
    }
}
