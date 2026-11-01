<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class ServerSubuserController extends Controller
{
    private const PERMISSIONS = ['start', 'stop', 'restart', 'kill', 'plugins', 'mods', 'backup', 'files', 'properties', 'settings'];

    public function index(Request $request, Server $server): JsonResponse
    {
        $this->authorize('manageSubusers', $server);
        $members = $server->subusers()->select(['users.id', 'users.name', 'users.username', 'users.email'])
            ->orderBy('users.username')->get();
        return response()->json($members);
    }

    public function store(Request $request, Server $server): JsonResponse
    {
        $this->authorize('manageSubusers', $server);
        $data = $request->validate([
            'user_id' => ['required', 'string', 'exists:users,id'],
            'permissions' => ['required', 'array', 'max:10'],
            'permissions.*' => ['required', 'string', Rule::in(self::PERMISSIONS)],
        ]);
        $user = User::query()->findOrFail($data['user_id']);
        abort_if($user->disabled, 422, 'Disabled accounts cannot be added as server sub-users.');
        abort_if((string) $user->id === (string) $server->owner_id, 422, 'The server owner is not a sub-user.');
        $permissions = array_values(array_unique($data['permissions']));

        DB::transaction(function () use ($server, $user, $permissions, $request): void {
            DB::table('server_subusers')->updateOrInsert(
                ['server_id' => $server->id, 'user_id' => $user->id],
                ['added_by' => (string) $request->user()->id, 'permissions' => json_encode($permissions, JSON_THROW_ON_ERROR), 'updated_at' => now(), 'created_at' => now()],
            );
            DB::table('audit_events')->insert([
                'user_id' => (string) $request->user()->id,
                'action' => 'server.subuser.upsert',
                'resource_type' => 'server',
                'resource_id' => $server->id,
                'result' => 'success',
                'ip_address' => $request->ip(),
                'metadata' => json_encode(['subuser_id' => $user->id, 'permissions' => $permissions], JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json(['user' => $user->only(['id', 'name', 'username', 'email']), 'permissions' => $permissions], 201);
    }

    public function destroy(Request $request, Server $server, User $subuser): JsonResponse
    {
        $this->authorize('manageSubusers', $server);
        abort_if((string) $subuser->id === (string) $server->owner_id, 422, 'The server owner is not a sub-user.');
        $deleted = DB::transaction(function () use ($server, $subuser, $request): int {
            $deleted = DB::table('server_subusers')->where('server_id', $server->id)->where('user_id', $subuser->id)->delete();
            if ($deleted) {
                DB::table('audit_events')->insert([
                    'user_id' => (string) $request->user()->id,
                    'action' => 'server.subuser.remove',
                    'resource_type' => 'server',
                    'resource_id' => $server->id,
                    'result' => 'success',
                    'ip_address' => $request->ip(),
                    'metadata' => json_encode(['subuser_id' => $subuser->id], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return $deleted;
        });
        abort_unless($deleted > 0, 404, 'Server sub-user not found.');
        return response()->json(['success' => true]);
    }
}
