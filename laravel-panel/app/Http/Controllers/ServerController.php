<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\Server;
use App\Services\NodeClient;
use App\Services\ServerProvisioner;
use App\Services\ServerProvisioningException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class ServerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Server::class);
        $user = $request->user();
        $servers = Server::query()->visibleTo($user)->with(['node', 'allocations', 'owner'])->latest()->get()
            ->filter(fn (Server $server): bool => $user->can('view', $server))->values();

        return response()->json($servers);
    }

    public function store(Request $request, NodeClient $client, ServerProvisioner $provisioner): JsonResponse
    {
        $this->authorize('create', Server::class);
        $data = $request->validate([
            'node_id' => ['required', 'string', 'exists:nodes,id'],
            'allocation_id' => ['required', 'string', 'exists:allocations,id'],
            'owner_id' => ['sometimes', 'string', 'exists:users,id'],
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'type' => ['sometimes', 'string', Rule::in(['PAPER', 'VANILLA', 'FABRIC', 'FORGE', 'NEOFORGE', 'SPIGOT', 'PURPUR', 'VELOCITY', 'BUNGEECORD', 'WATERFALL'])],
            'version' => ['nullable', 'string', 'max:64'],
            'java_version' => ['nullable', 'string', Rule::in(['8', '11', '17', '21', '25'])],
            'image' => ['nullable', 'string', 'max:255', 'regex:~^itzg/(minecraft-server|bungeecord):[A-Za-z0-9._-]{1,80}$~'],
            'memory_limit_mb' => ['nullable', 'required_without:memory_limit', 'integer', 'min:128', 'max:1048576'],
            'memory_limit' => ['nullable', 'required_without:memory_limit_mb', 'integer', 'min:128', 'max:1048576'],
            'cpu_limit_percent' => ['nullable', 'required_without:cpu_limit', 'integer', 'min:1', 'max:10000'],
            'cpu_limit' => ['nullable', 'required_without:cpu_limit_percent', 'integer', 'min:1', 'max:10000'],
            'environment_json' => ['nullable', 'array', 'max:50'],
            'environment_json.*' => ['string', 'max:2048'],
            'startup_command' => ['nullable', 'string', 'max:2000'],
            'disk_limit_mb' => ['nullable', 'integer', 'min:512', 'max:1099511627776'],
            'disk_limit' => ['nullable', 'integer', 'min:512', 'max:1099511627776'],
        ]);

        try {
            $result = $provisioner->provision($request->user(), $data, $client);

            return response()->json($result, 201);
        } catch (ServerProvisioningException $exception) {
            return response()->json(['error' => $exception->getMessage(), 'server_id' => $exception->serverId], 502);
        }
    }

    public function action(Request $request, Server $server, string $action, NodeClient $client): JsonResponse
    {
        abort_if($server->suspended && ! $request->user()->isStaff(), 403, 'This server is suspended.');
        $this->authorize($action, $server);
        try {
            $nodeResponse = $client->serverAction($server->node, $server->id, $action);
            try {
                $inspection = $client->serverStatus($server->node, $server->id);
                $server->update(['status' => $this->panelStatus($inspection)]);
                $nodeResponse['inspection'] = $inspection;
            } catch (Throwable $statusError) {
                $server->update(['status' => 'unknown']);
                Log::warning('Daemon accepted lifecycle action but status inspection failed.', ['server_id' => $server->id, 'action' => $action, 'exception' => $statusError::class]);
                $nodeResponse['statusUnknown'] = true;
            }

            return response()->json(['success' => true, 'server' => $server->fresh(), 'node' => $nodeResponse]);
        } catch (Throwable $exception) {
            Log::warning('Node daemon lifecycle operation failed.', ['server_id' => $server->id, 'action' => $action, 'exception' => $exception::class]);

            return response()->json(['error' => 'The node daemon could not complete the server action.'], 502);
        }
    }

    public function status(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        abort_if($server->suspended && ! $request->user()->isStaff(), 403, 'This server is suspended.');
        $this->authorize('status', $server);
        try {
            $inspection = $client->serverStatus($server->node, $server->id);
            $server->update(['status' => $this->panelStatus($inspection)]);

            return response()->json(['server' => $server->fresh(), 'inspection' => $inspection]);
        } catch (Throwable $exception) {
            Log::warning('Node server status check failed.', ['server_id' => $server->id, 'exception' => $exception::class]);

            return response()->json(['error' => 'The node daemon could not return server status.'], 502);
        }
    }

    public function destroy(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorize('delete', $server);
        try {
            $client->deleteServer($server->node, $server->id);
            DB::transaction(function () use ($server): void {
                Allocation::query()->where('assigned_server_id', $server->id)->update(['assigned_server_id' => null]);
                $server->update(['status' => 'deleted', 'container_id' => null]);
            });

            return response()->json(['success' => true, 'server' => $server->fresh()]);
        } catch (Throwable $exception) {
            Log::warning('Node server deletion failed.', ['server_id' => $server->id, 'exception' => $exception::class]);

            return response()->json(['error' => 'The node daemon could not delete the server container.'], 502);
        }
    }

    private function panelStatus(array $inspection): string
    {
        $state = $inspection['State'] ?? $inspection['state'] ?? null;
        if (! is_array($state)) {
            return 'unknown';
        }
        $dockerState = strtolower((string) ($state['Status'] ?? $state['status'] ?? ''));
        if ($dockerState === 'running') {
            return 'running';
        }
        if (in_array($dockerState, ['created', 'restarting'], true)) {
            return 'installing';
        }
        if (in_array($dockerState, ['exited', 'paused', 'dead', 'removing'], true)) {
            return $dockerState;
        }

        return 'unknown';
    }
}
