<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Services\NodeClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class NodeClientContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_typed_operations_match_frozen_v1_methods_and_preserve_text_responses(): void
    {
        $node = $this->node();
        Http::fake(function ($request) {
            $url = $request->url();
            if (str_ends_with($url, '/files/list')) {
                return Http::response([['name' => 'plugins', 'isDirectory' => true, 'size' => 0]]);
            }
            if (str_ends_with($url, '/logs')) {
                return Http::response("[Server thread/INFO]: ready\n", 200, ['Content-Type' => 'text/plain']);
            }
            if (str_ends_with($url, '/backups')) {
                return Http::response([['filename' => 'backup-20261028.tar.gz', 'size' => 12, 'createdAt' => '2026-10-28T00:00:00Z']]);
            }
            if (str_ends_with($url, '/status')) {
                return Http::response(['State' => ['Status' => 'running']]);
            }
            if (str_ends_with($url, '/daemon/restart')) {
                return Http::response(['success' => true, 'restarting' => true], 202);
            }
            if (str_ends_with($url, '/start')) {
                return Http::response(['success' => true]);
            }
            if (str_ends_with($url, '/servers/server-1')) {
                return Http::response(['success' => true]);
            }
            if (str_ends_with($url, '/backups/backup-20261028.tar.gz')) {
                return Http::response(['success' => true]);
            }

            return Http::response(['error' => 'unexpected route'], 404);
        });

        $client = app(NodeClient::class);
        $this->assertCount(1, $client->serverRequest($node, 'server-1', 'POST', 'files/list', ['path' => '.']));
        $this->assertSame("[Server thread/INFO]: ready\n", $client->serverLogs($node, 'server-1'));
        $this->assertCount(1, $client->listBackups($node, 'server-1'));
        $this->assertSame('running', $client->serverStatus($node, 'server-1')['State']['Status']);
        $client->serverAction($node, 'server-1', 'start');
        $client->deleteServer($node, 'server-1');
        $client->deleteBackup($node, 'server-1', 'backup-20261028.tar.gz');
        $client->restartNode($node);

        Http::assertSent(fn ($request) => $request->method() === 'POST' && str_ends_with($request->url(), '/v1/servers/server-1/files/list') && ($request->data()['path'] ?? null) === '.');
        Http::assertSent(fn ($request) => $request->method() === 'GET' && str_ends_with($request->url(), '/v1/servers/server-1/logs'));
        Http::assertSent(fn ($request) => $request->method() === 'GET' && str_ends_with($request->url(), '/v1/servers/server-1/backups'));
        Http::assertSent(fn ($request) => $request->method() === 'GET' && str_ends_with($request->url(), '/v1/servers/server-1/status'));
        Http::assertSent(fn ($request) => $request->method() === 'DELETE' && str_ends_with($request->url(), '/v1/servers/server-1'));
        Http::assertSent(fn ($request) => $request->method() === 'DELETE' && str_ends_with($request->url(), '/v1/servers/server-1/backups/backup-20261028.tar.gz'));
        Http::assertSent(fn ($request) => $request->method() === 'POST' && str_ends_with($request->url(), '/v1/daemon/restart'));
    }

    public function test_method_mismatch_and_backup_path_injection_are_rejected_without_network_calls(): void
    {
        $node = $this->node();
        Http::preventStrayRequests();
        try {
            app(NodeClient::class)->serverRequest($node, 'server-1', 'GET', 'files/list', ['path' => '.']);
            $this->fail('A method/path mismatch should fail closed.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('frozen v1 contract', $exception->getMessage());
        }
        $this->expectException(RuntimeException::class);
        app(NodeClient::class)->downloadBackup($node, 'server-1', '../secrets.tar.gz');
    }

    public function test_backup_download_is_streamed_to_a_private_file_and_not_json_decoded(): void
    {
        $node = $this->node();
        Http::fake(['https://node.example.test:6768/v1/servers/server-1/backups/backup-20261028.tar.gz' => Http::response("\x1F\x8Bbinary-backup", 200, ['Content-Type' => 'application/gzip'])]);
        $path = app(NodeClient::class)->downloadBackup($node, 'server-1', 'backup-20261028.tar.gz');
        try {
            $this->assertSame("\x1F\x8Bbinary-backup", file_get_contents($path));
            $this->assertSame(0600, fileperms($path) & 0777);
        } finally {
            @unlink($path);
        }
        Http::assertSent(fn ($request) => $request->method() === 'GET' && str_ends_with($request->url(), '/v1/servers/server-1/backups/backup-20261028.tar.gz'));
    }

    private function node(): Node
    {
        return Node::query()->create([
            'id' => 'node-client',
            'name' => 'Client test node',
            'fqdn' => 'node.example.test',
            'daemon_port' => 6768,
            'credential_encrypted' => Crypt::encryptString('node-client-secret'),
        ]);
    }
}
