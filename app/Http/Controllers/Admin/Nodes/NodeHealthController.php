<?php

namespace Pterodactyl\Http\Controllers\Admin\Nodes;

use Pterodactyl\Models\Node;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;

class NodeHealthController extends Controller
{
    public function __construct(private DaemonConfigurationRepository $repository)
    {
    }

    public function __invoke(Node $node): JsonResponse
    {
        try {
            $system = $this->repository->setNode($node)->getSystemInformation();
        } catch (DaemonConnectionException $exception) {
            return response()->json([
                'online' => false,
                'message' => 'Wings did not return an authenticated health response.',
            ], 503);
        }

        return response()->json([
            'online' => true,
            'version' => $system['version'] ?? null,
            'heartbeat' => true,
        ]);
    }
}
