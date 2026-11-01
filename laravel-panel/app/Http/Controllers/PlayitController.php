<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\PlayitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PlayitController extends Controller
{
    public function __construct(private readonly PlayitService $playit) {}

    public function status(Request $request, Server $server): JsonResponse
    {
        $this->requireAccess($server);
        try {
            return response()->json($this->playit->status($server));
        } catch (Throwable) {
            return response()->json(['error' => 'The Playit agent status could not be read.'], 502);
        }
    }

    public function start(Request $request, Server $server): JsonResponse
    {
        $user = $this->requireAccess($server);
        try {
            $result = $this->playit->start($server);
            $this->audit($user->id, 'playit.started', $server->id);

            return response()->json($result);
        } catch (Throwable) {
            return response()->json(['error' => 'The Playit agent could not be started.'], 502);
        }
    }

    public function stop(Request $request, Server $server): JsonResponse
    {
        $user = $this->requireAccess($server);
        try {
            $result = $this->playit->stop($server);
            $this->audit($user->id, 'playit.stopped', $server->id);

            return response()->json($result);
        } catch (Throwable) {
            return response()->json(['error' => 'The Playit agent could not be stopped.'], 502);
        }
    }

    public function reset(Request $request, Server $server): JsonResponse
    {
        $user = $this->requireAccess($server);
        try {
            $result = $this->playit->reset($server);
            $this->audit($user->id, 'playit.reset', $server->id);

            return response()->json($result);
        } catch (Throwable) {
            return response()->json(['error' => 'The Playit agent could not be reset.'], 502);
        }
    }

    private function requireAccess(Server $server)
    {
        abort_unless(request()->user()?->can('playit', $server), 403, 'Only the server owner or staff can manage Playit.');

        return request()->user();
    }

    private function audit(string $userId, string $action, string $serverId): void
    {
        DB::table('audit_events')->insert(['user_id' => $userId, 'action' => $action, 'resource_type' => 'server', 'resource_id' => $serverId, 'result' => 'success', 'metadata' => json_encode(['integration' => 'playit']), 'created_at' => now(), 'updated_at' => now()]);
    }
}
