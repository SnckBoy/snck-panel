<?php

namespace Pterodactyl\Tests\Unit\Http\Controllers\Admin\Nodes;

use Pterodactyl\Models\Node;
use Pterodactyl\Tests\TestCase;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;
use Pterodactyl\Http\Controllers\Admin\Nodes\NodeHealthController;

class NodeHealthControllerTest extends TestCase
{
    public function testAuthenticatedWingsResponseIsReportedAsOnline(): void
    {
        $node = new Node();
        $node->id = 7;
        $repository = \Mockery::mock(DaemonConfigurationRepository::class);
        $repository->expects('setNode')->with(\Mockery::on(fn (Node $value): bool => $value->id === 7))->andReturnSelf();
        $repository->expects('getSystemInformation')->andReturn(['version' => '1.13.5']);

        $response = (new NodeHealthController($repository))($node);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'online' => true,
            'version' => '1.13.5',
            'heartbeat' => true,
        ], $response->getData(true));
    }
}
