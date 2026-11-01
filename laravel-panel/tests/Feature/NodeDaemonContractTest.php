<?php

namespace Tests\Feature;

use Tests\TestCase;

class NodeDaemonContractTest extends TestCase
{
    public function test_frozen_manifest_exactly_matches_every_live_daemon_route(): void
    {
        $manifest = json_decode(file_get_contents(base_path('contracts/node-daemon-v1.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('snck-node-daemon-v1', $manifest['contract']);
        $this->assertTrue($manifest['authentication']['allDaemonRoutesRequireBearer']);
        $this->assertSame('https', $manifest['transport']['panelToDaemon']);
        $this->assertSame('required', $manifest['transport']['certificateVerification']);
        $this->assertFalse($manifest['transport']['allowInsecureVerification']);
        $this->assertFalse($manifest['transport']['allowHttp']);
        $this->assertCount(29, $manifest['routes']);

        $daemon = dirname(base_path()).'/node-daemon/src/index.ts';
        if (!is_file($daemon)) {
            $this->markTestSkipped('The stand-alone laravel-panel archive does not include the sibling node-daemon source.');
        }
        $source = file_get_contents($daemon);
        $this->assertIsString($source);

        preg_match_all('/app\.(get|post|put|patch|delete)\(\s*["\']([^"\']+)["\']\s*,\s*auth\b/i', $source, $matches, PREG_SET_ORDER);
        $actual = [];
        foreach ($matches as $match) $actual[] = [strtoupper($match[1]), $match[2]];

        // These four handlers are emitted from the daemon's explicit action loop.
        $this->assertStringContainsString('for (const [route,method] of [["start","start"],["stop","stop"],["restart","restart"],["kill","kill"]]', $source);
        foreach (['start', 'stop', 'restart', 'kill'] as $action) $actual[] = ['POST', '/v1/servers/:id/'.$action];
        $this->assertStringContainsString('/api/node-agent/heartbeat', $source);

        $expected = array_map(fn (array $route) => [strtoupper($route['method']), $route['path']], $manifest['routes']);
        sort($actual);
        sort($expected);
        $this->assertSame($expected, $actual, 'Daemon method/path/auth set changed; version the contract before implementation changes.');
    }
}
