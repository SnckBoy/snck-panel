<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

class NodeAgentController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nodeId' => ['required', 'string', 'max:64'],
            'stats' => ['required', 'array'],
            'stats.docker' => ['required', 'boolean'],
            'stats.daemonVersion' => ['sometimes', 'string', 'max:64'],
            'stats.timestamp' => ['sometimes', 'date'],
        ]);
        $node = Node::query()->find($data['nodeId']);
        if (!$node) return response()->json(['error' => 'Unauthorized node.'], 401);
        $header = (string) $request->header('Authorization', '');
        $token = str_starts_with($header, 'Bearer ') ? substr($header, 7) : '';
        if ($token === '' || strlen($token) > 512 || !$this->matchesCredential($node, $token)) {
            return response()->json(['error' => 'Unauthorized node.'], 401);
        }
        if ($node->disabled) return response()->json(['error' => 'Node is disabled.'], 403);
        $stats = $data['stats'];
        $node->forceFill([
            'status' => 'online',
            'docker_available' => (bool) $stats['docker'],
            'stats' => $stats,
            'daemon_version' => $stats['daemonVersion'] ?? $node->daemon_version,
            'last_heartbeat_at' => now(),
        ])->save();

        return response()->json(['success' => true, 'nodeId' => $node->id, 'receivedAt' => now()->toISOString()]);
    }

    private function matchesCredential(Node $node, string $token): bool
    {
        $expectedHash = (string) ($node->credential_hash ?? '');
        if ($expectedHash !== '') return hash_equals($expectedHash, hash('sha256', $token));

        // Legacy Phase 2 imports may have encrypted credentials but no hash.
        // Verify the old secret once and backfill the one-way lookup hash.
        if (!$node->credential_encrypted) return false;
        try { $expected = Crypt::decryptString($node->credential_encrypted); }
        catch (Throwable) { return false; }
        if (!hash_equals($expected, $token)) return false;
        DB::table('nodes')->where('id', $node->id)->whereNull('credential_hash')->update(['credential_hash' => hash('sha256', $token), 'updated_at' => now()]);
        return true;
    }
}
