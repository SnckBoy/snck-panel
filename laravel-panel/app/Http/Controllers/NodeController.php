<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Services\NodeClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class NodeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->adminOnly($request);
        return response()->json(Node::query()->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'id' => ['nullable', 'string', 'max:64', 'alpha_dash', 'unique:nodes,id'],
            'name' => ['required', 'string', 'max:120'],
            'host' => ['nullable', 'string', 'max:255'],
            'fqdn' => ['nullable', 'string', 'max:255'],
            'public_port' => ['required', 'integer', 'between:1,65535'],
            'daemon_port' => ['required', 'integer', 'between:1,65535'],
            'sftp_port' => ['required', 'integer', 'between:1,65535'],
            'protocol' => ['sometimes', 'in:https'],
            'behind_proxy' => ['sometimes', 'boolean'],
            'custom_ca_pem' => ['nullable', 'string', 'max:65536'],
            'access_client_id' => ['nullable', 'string', 'max:255'],
            'access_client_secret' => ['nullable', 'string', 'max:512'],
            'credential' => ['nullable', 'string', 'min:32', 'max:512'],
            'memory_limit_mb' => ['nullable', 'integer', 'min:1', 'max:1099511627776'],
            'disk_limit_mb' => ['nullable', 'integer', 'min:1', 'max:1099511627776'],
            'cpu_limit_percent' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        $credential = (string) ($data['credential'] ?? bin2hex(random_bytes(32)));
        $id = $data['id'] ?? Str::lower(Str::random(12));
        $node = Node::query()->create([
            'id' => $id,
            'name' => $data['name'],
            'host' => $data['host'] ?? null,
            'fqdn' => $data['fqdn'] ?? null,
            'public_port' => $data['public_port'],
            'daemon_port' => $data['daemon_port'],
            'api_port' => $data['daemon_port'],
            'sftp_port' => $data['sftp_port'],
            'protocol' => 'https',
            'behind_proxy' => (bool) ($data['behind_proxy'] ?? false),
            'custom_ca_pem' => $data['custom_ca_pem'] ?? null,
            'access_client_id' => $data['access_client_id'] ?? null,
            'access_client_secret_encrypted' => isset($data['access_client_secret']) ? Crypt::encryptString($data['access_client_secret']) : null,
            'credential_encrypted' => Crypt::encryptString($credential),
            'credential_hash' => hash('sha256', $credential),
            'memory_limit_mb' => $data['memory_limit_mb'] ?? null,
            'disk_limit_mb' => $data['disk_limit_mb'] ?? null,
            'cpu_limit_percent' => $data['cpu_limit_percent'] ?? null,
            'status' => 'offline',
            'docker_available' => false,
        ]);
        return response()->json(['node' => $node, 'daemonCredentialOnce' => $credential], 201);
    }

    public function update(Request $request, Node $node): JsonResponse
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'host' => ['nullable', 'string', 'max:255'],
            'fqdn' => ['nullable', 'string', 'max:255'],
            'public_port' => ['sometimes', 'integer', 'between:1,65535'],
            'daemon_port' => ['sometimes', 'integer', 'between:1,65535'],
            'sftp_port' => ['sometimes', 'integer', 'between:1,65535'],
            'protocol' => ['sometimes', 'in:https'],
            'behind_proxy' => ['sometimes', 'boolean'],
            'custom_ca_pem' => ['nullable', 'string', 'max:65536'],
            'maintenance' => ['sometimes', 'boolean'],
            'disabled' => ['sometimes', 'boolean'],
            'access_client_id' => ['nullable', 'string', 'max:255'],
            'access_client_secret' => ['nullable', 'string', 'max:512'],
            'credential' => ['sometimes', 'string', 'min:32', 'max:512'],
            'memory_limit_mb' => ['nullable', 'integer', 'min:1', 'max:1099511627776'],
            'disk_limit_mb' => ['nullable', 'integer', 'min:1', 'max:1099511627776'],
            'cpu_limit_percent' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        $credential = $data['credential'] ?? null;
        unset($data['credential']);
        if (array_key_exists('access_client_secret', $data)) {
            $data['access_client_secret_encrypted'] = $data['access_client_secret'] ? Crypt::encryptString($data['access_client_secret']) : null;
            unset($data['access_client_secret']);
        }
        if ($credential !== null) {
            $data['credential_encrypted'] = Crypt::encryptString($credential);
            $data['credential_hash'] = hash('sha256', $credential);
        }
        if (isset($data['daemon_port'])) $data['api_port'] = $data['daemon_port'];
        $data['protocol'] = 'https';
        $node->update($data);
        return response()->json($node->fresh());
    }

    public function health(Request $request, Node $node, NodeClient $client): JsonResponse
    {
        $this->adminOnly($request);
        try {
            $payload = $client->health($node);
            $node->forceFill([
                'status' => 'online',
                'docker_available' => $payload['docker'],
                'daemon_version' => $payload['daemonVersion'] ?? $node->daemon_version,
                'last_heartbeat_at' => now(),
                'last_health_check' => ['checkedAt' => now()->toISOString(), 'ok' => true, 'docker' => $payload['docker']],
            ])->save();
            return response()->json(['online' => true, 'node' => $node->fresh(), 'health' => $payload]);
        } catch (Throwable $exception) {
            Log::warning('Node health check failed.', ['node_id' => $node->id, 'exception' => $exception::class]);
            $node->forceFill([
                'status' => 'offline',
                'docker_available' => false,
                'last_health_check' => ['checkedAt' => now()->toISOString(), 'ok' => false],
            ])->save();
            return response()->json(['online' => false, 'error' => 'Node health check failed.', 'node' => $node->fresh()], 503);
        }
    }

    public function restart(Request $request, Node $node, NodeClient $client): JsonResponse
    {
        $this->adminOnly($request);
        try {
            $result = $client->restartNode($node);
            return response()->json(['success' => true, 'node' => $node, 'daemon' => $result], 202);
        } catch (Throwable $exception) {
            Log::warning('Node daemon restart request failed.', ['node_id' => $node->id, 'exception' => $exception::class]);
            return response()->json(['error' => 'The node daemon restart request failed.'], 502);
        }
    }

    private function adminOnly(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, ['owner', 'admin'], true), 403);
    }
}
