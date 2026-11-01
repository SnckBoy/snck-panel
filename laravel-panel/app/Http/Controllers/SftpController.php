<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\SftpCredentialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SftpController extends Controller
{
    public function __construct(private readonly SftpCredentialService $credentials) {}

    public function show(Request $request, Server $server): JsonResponse
    {
        $this->authorize('sftp', $server);
        $result = $this->credentials->get($server);
        return $result ? response()->json($result) : response()->json(['error' => 'SFTP user not found.'], 404);
    }

    public function create(Request $request, Server $server): JsonResponse
    {
        return $this->issue($request, $server, 'sftp.created', fn () => $this->credentials->create($server));
    }

    public function reset(Request $request, Server $server): JsonResponse
    {
        return $this->issue($request, $server, 'sftp.password_reset', fn () => $this->credentials->reset($server));
    }

    public function destroy(Request $request, Server $server): JsonResponse
    {
        $this->authorize('sftp', $server);
        $this->credentials->delete($server);
        $this->audit($request->user()->id, 'sftp.deleted', $server->id);
        return response()->json(['success' => true]);
    }

    private function issue(Request $request, Server $server, string $action, callable $callback): JsonResponse
    {
        $this->authorize('sftp', $server);
        try { $result = $callback(); $this->audit($request->user()->id, $action, $server->id); return response()->json($result); }
        catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 409); }
    }

    private function audit(string $userId, string $action, string $serverId): void
    {
        DB::table('audit_events')->insert(['user_id' => $userId, 'action' => $action, 'resource_type' => 'server', 'resource_id' => $serverId, 'result' => 'success', 'metadata' => json_encode(['integration' => 'sftp']), 'created_at' => now(), 'updated_at' => now()]);
    }
}
