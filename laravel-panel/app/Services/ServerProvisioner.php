<?php

namespace App\Services;

use App\Models\Allocation;
use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ServerProvisioner
{
    public const DEFAULT_QUOTAS = [
        'max_servers' => 3,
        'total_ram_mb' => 8192,
        'total_cpu_percent' => 400,
        'total_disk_mb' => 51200,
    ];

    public function provision(User $actor, array $data, NodeClient $client): array
    {
        $ownerId = $actor->isStaff() ? (string) ($data['owner_id'] ?? $actor->id) : (string) $actor->id;
        if (! $actor->isStaff() && isset($data['owner_id']) && (string) $data['owner_id'] !== (string) $actor->id) {
            abort(403, 'You cannot create a server for another owner.');
        }

        $memoryMb = (int) ($data['memory_limit_mb'] ?? $data['memory_limit']);
        $cpuPercent = (int) ($data['cpu_limit_percent'] ?? $data['cpu_limit']);
        $diskMb = (int) ($data['disk_limit_mb'] ?? $data['disk_limit'] ?? 10240);
        $serverId = Str::lower(Str::random(24));
        $identifier = Str::lower(Str::random(12));

        $server = DB::transaction(function () use ($actor, $data, $ownerId, $memoryMb, $cpuPercent, $diskMb, $serverId, $identifier): Server {
            // Serializing on the owner row makes quota checks and allocation claims atomic.
            $owner = User::query()->whereKey($ownerId)->lockForUpdate()->firstOrFail();
            if (! $actor->isStaff()) {
                $this->enforceUserQuota($owner, $memoryMb, $cpuPercent, $diskMb);
            }

            $node = Node::query()->whereKey($data['node_id'])->lockForUpdate()->firstOrFail();
            abort_if($node->disabled || $node->maintenance, 409, 'The selected node is disabled or under maintenance.');
            abort_unless(
                $node->status === 'online'
                    && $node->docker_available
                    && $node->last_heartbeat_at !== null
                    && $node->last_heartbeat_at->greaterThan(now()->subSeconds(45)),
                409,
                'The selected node must have a recent authenticated heartbeat and Docker readiness before provisioning.',
            );
            if ($node->memory_limit_mb !== null) {
                $reservedMb = (int) Server::query()->where('node_id', $node->id)->where('status', '!=', 'deleted')->sum('memory_limit_mb');
                abort_if($reservedMb + $memoryMb > (int) $node->memory_limit_mb, 422, 'The requested memory exceeds this node\'s configured capacity.');
            }
            if ($node->cpu_limit_percent !== null) {
                $reservedCpu = (int) Server::query()->where('node_id', $node->id)->where('status', '!=', 'deleted')->sum('cpu_limit_percent');
                abort_if($reservedCpu + $cpuPercent > (int) $node->cpu_limit_percent, 422, 'The requested CPU exceeds this node\'s configured capacity.');
            }

            $allocation = Allocation::query()->whereKey($data['allocation_id'])->lockForUpdate()->firstOrFail();
            abort_if($allocation->assigned_server_id !== null, 409, 'The selected allocation is already assigned.');
            abort_unless((string) $allocation->node_id === (string) $node->id, 422, 'The selected allocation belongs to another node.');

            $server = Server::query()->create([
                'id' => $serverId,
                'identifier' => $identifier,
                'owner_id' => $ownerId,
                'node_id' => $node->id,
                'name' => trim($data['name']),
                'image' => $data['image'] ?? 'itzg/minecraft-server:latest',
                'type' => strtoupper((string) ($data['type'] ?? 'PAPER')),
                'version' => $data['version'] ?? 'latest',
                'java_version' => $data['java_version'] ?? null,
                'status' => 'installing',
                'memory_limit_mb' => $memoryMb,
                'ram_gb' => round($memoryMb / 1024, 3),
                'cpu_limit_percent' => $cpuPercent,
                'disk_limit_mb' => $diskMb,
                'port' => $allocation->port,
                'startup_command' => $data['startup_command'] ?? null,
                'environment_json' => $data['environment_json'] ?? [],
            ]);
            $allocation->update(['assigned_server_id' => $server->id]);

            return $server;
        }, 3);

        $server->load(['node', 'allocations', 'owner']);
        try {
            $nodeResult = $client->createServer($server->node, [
                'id' => $server->id,
                'name' => $server->name,
                'type' => $server->type,
                'version' => $server->version ?: 'latest',
                'javaVersion' => $server->java_version,
                'image' => $server->image,
                'port' => $server->port,
                'ram' => $server->ram_gb,
                'cpu' => $server->cpu_limit_percent,
                'rconPassword' => bin2hex(random_bytes(32)),
                'environment' => $server->environment_json ?? [],
            ]);
            $server->update(['container_id' => $nodeResult['containerId'] ?? $nodeResult['container_id'] ?? null]);
            try {
                $inspection = $client->serverStatus($server->node, $server->id);
                $server->update(['status' => $this->panelStatus($inspection)]);
                $nodeResult['inspection'] = $inspection;
            } catch (Throwable $statusError) {
                $server->update(['status' => 'unknown']);
                Log::warning('Daemon accepted server creation but status inspection failed.', ['server_id' => $server->id, 'exception' => $statusError::class]);
                $nodeResult['statusUnknown'] = true;
            }

            return ['server' => $server->fresh(['node', 'allocations', 'owner']), 'node' => $nodeResult, 'diskQuotaEnforced' => false];
        } catch (Throwable $exception) {
            $server->update(['status' => 'error']);
            Log::warning('Node daemon could not provision a server.', ['server_id' => $server->id, 'exception' => $exception::class]);
            throw new ServerProvisioningException($server->id, 'The node daemon did not complete server provisioning.', $exception);
        }
    }

    public function quotas(): array
    {
        return collect(self::DEFAULT_QUOTAS)->mapWithKeys(function (int $default, string $key): array {
            $value = DB::table('settings')->where('key', 'quota_'.$key)->value('value');
            if (is_string($value)) {
                $value = json_decode($value, true) ?? $value;
            }
            $value = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: $default;

            return [$key => (int) $value];
        })->all();
    }

    private function enforceUserQuota(User $owner, int $memoryMb, int $cpuPercent, int $diskMb): void
    {
        $quotas = $this->quotas();
        $query = Server::query()->where('owner_id', $owner->id)->where('status', '!=', 'deleted');
        abort_if((clone $query)->count() >= $quotas['max_servers'], 422, 'Your server limit has been reached.');
        abort_if((int) (clone $query)->sum('memory_limit_mb') + $memoryMb > $quotas['total_ram_mb'], 422, 'Your total RAM quota would be exceeded.');
        abort_if((int) (clone $query)->sum('cpu_limit_percent') + $cpuPercent > $quotas['total_cpu_percent'], 422, 'Your total CPU quota would be exceeded.');
        abort_if((int) (clone $query)->sum('disk_limit_mb') + $diskMb > $quotas['total_disk_mb'], 422, 'Your total disk quota would be exceeded.');
    }

    private function panelStatus(array $inspection): string
    {
        $state = $inspection['State'] ?? $inspection['state'] ?? $inspection;
        $status = strtolower((string) (is_array($state) ? ($state['Status'] ?? $state['status'] ?? 'unknown') : $state));

        return match ($status) {
            'running', 'online' => 'running',
            'stopped', 'offline', 'exited' => 'stopped',
            'starting', 'created', 'restarting' => 'installing',
            default => 'unknown',
        };
    }
}
