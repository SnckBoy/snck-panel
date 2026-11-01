<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApiKeyController extends Controller
{
    private const SCOPES = ['servers.read', 'servers.write', 'files.read', 'files.write', 'nodes.read', 'nodes.write', 'admin.settings', 'admin.users'];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = ApiKey::query()->with('user:id,username')->latest();
        if (! $user->isStaff()) {
            $query->where('user_id', $user->id);
        }

        return response()->json(['keys' => $query->get()->map(fn (ApiKey $key) => $this->publicKey($key))->values()]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'scopes' => ['required', 'array', 'min:1', 'max:8'],
            'scopes.*' => ['string', Rule::in(self::SCOPES)],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'user_id' => ['nullable', 'string', 'exists:users,id'],
        ]);
        $ownerId = $user->isStaff() && ! empty($data['user_id']) ? $data['user_id'] : $user->id;
        if (! $user->isStaff() && array_intersect($data['scopes'], ['admin.settings', 'admin.users', 'nodes.write'])) {
            abort(403, 'Only staff can issue administrative API scopes.');
        }
        $plain = 'snck_'.Str::random(48);
        $key = ApiKey::query()->create([
            'user_id' => $ownerId, 'label' => trim($data['label']), 'key_hash' => hash('sha256', $plain),
            'scopes' => array_values(array_unique($data['scopes'])), 'expires_at' => $data['expires_at'] ?? null,
        ]);

        return response()->json(['key' => $this->publicKey($key), 'token' => $plain], 201);
    }

    public function destroy(Request $request, ApiKey $apiKey): JsonResponse
    {
        abort_unless($request->user()->isStaff() || (string) $apiKey->user_id === (string) $request->user()->id, 403);
        $apiKey->forceFill(['revoked_at' => now()])->save();

        return response()->json(['success' => true]);
    }

    private function publicKey(ApiKey $key): array
    {
        return ['id' => $key->id, 'label' => $key->label, 'username' => $key->user?->username, 'scopes' => $key->scopes ?? [], 'expires_at' => $key->expires_at?->toISOString(), 'last_used_at' => $key->last_used_at?->toISOString(), 'revoked_at' => $key->revoked_at?->toISOString(), 'created_at' => $key->created_at?->toISOString()];
    }
}
