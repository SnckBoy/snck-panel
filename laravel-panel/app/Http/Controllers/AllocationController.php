<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->adminOnly($request);
        $query = Allocation::query()->with(['node', 'server']);
        if ($request->filled('node_id')) {
            $query->where('node_id', $request->string('node_id'));
        }

        return response()->json($query->orderBy('port')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'node_id' => ['required', 'string', 'exists:nodes,id'],
            'ip' => ['required', 'string', 'max:64'],
            'start_port' => ['required', 'integer', 'between:1,65535'],
            'end_port' => ['required', 'integer', 'between:1,65535', 'gte:start_port'],
            'alias' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $count = $data['end_port'] - $data['start_port'] + 1;
        abort_if($count > 10000, 422, 'Port range cannot exceed 10,000 ports.');
        $ports = range($data['start_port'], $data['end_port']);
        $existing = Allocation::query()->where('node_id', $data['node_id'])->where('ip', $data['ip'])->whereIn('port', $ports)->pluck('port')->all();
        if ($existing) {
            return response()->json(['error' => 'Some ports already exist', 'ports' => $existing], 409);
        }
        $created = DB::transaction(function () use ($data, $ports) {
            return collect($ports)->map(fn (int $port) => Allocation::create([
                'node_id' => $data['node_id'], 'ip' => $data['ip'], 'port' => $port,
                'alias' => $data['alias'] ?? null, 'notes' => $data['notes'] ?? null,
            ]));
        });

        return response()->json($created->values(), 201);
    }

    public function assign(Request $request, Allocation $allocation): JsonResponse
    {
        $this->adminOnly($request);
        $data = $request->validate(['server_id' => ['required', 'string', 'exists:servers,id']]);
        abort_if(Allocation::where('assigned_server_id', $data['server_id'])->where('id', '!=', $allocation->id)->exists(), 409, 'Server already has an allocation.');
        $server = Server::findOrFail($data['server_id']);
        abort_unless($server->node_id === $allocation->node_id, 422, 'Allocation belongs to another node.');
        $allocation->update(['assigned_server_id' => $server->id]);

        return response()->json($allocation->fresh(['server', 'node']));
    }

    public function unassign(Request $request, Allocation $allocation): JsonResponse
    {
        $this->adminOnly($request);
        $allocation->update(['assigned_server_id' => null]);

        return response()->json($allocation->fresh(['server', 'node']));
    }

    private function adminOnly(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, ['owner', 'admin'], true), 403);
    }
}
