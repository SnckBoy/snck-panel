<?php

namespace Pterodactyl\Tests\Unit\Services\Nodes;

use Pterodactyl\Tests\TestCase;
use Pterodactyl\Services\Nodes\NodeCreationService;
use Pterodactyl\Services\Nodes\LocalNodeProvisioningService;

class LocalNodeProvisioningServiceTest extends TestCase
{
    public function testResourcesContainSafePositiveCapacityValues(): void
    {
        $service = new LocalNodeProvisioningService(\Mockery::mock(NodeCreationService::class));
        $resources = $service->resources();

        $this->assertGreaterThanOrEqual(512, $resources['memory']);
        $this->assertGreaterThanOrEqual(1024, $resources['disk']);
        $this->assertGreaterThanOrEqual(1, $resources['cpu']);
    }

    public function testPrivateLanAndTailnetAddressesAreRecognized(): void
    {
        $service = new LocalNodeProvisioningService(\Mockery::mock(NodeCreationService::class));

        $this->assertTrue($service->isPrivateHost('127.0.0.1'));
        $this->assertTrue($service->isPrivateHost('100.64.12.3'));
        $this->assertTrue($service->isPrivateHost('192.168.1.15'));
        $this->assertFalse($service->isPrivateHost('8.8.8.8'));
    }

    public function testInvalidHostIsRejectedBeforeAnyProvisioning(): void
    {
        $service = new LocalNodeProvisioningService(\Mockery::mock(NodeCreationService::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('hostname is invalid');
        $service->normalizeHost('not a valid host');
    }

    public function testPublicHttpEndpointIsRejected(): void
    {
        config(['app.url' => 'https://panel.example.com']);
        $service = new LocalNodeProvisioningService(\Mockery::mock(NodeCreationService::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTP is allowed only');
        $service->provision('8.8.8.8', 'http');
    }
}
