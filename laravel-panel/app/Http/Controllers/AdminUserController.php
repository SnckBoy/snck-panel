<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use RuntimeException;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->staff($request);

        return response()->json(['users' => User::query()->orderBy('username')->get(['id', 'name', 'username', 'email', 'role', 'disabled', 'email_verified_at', 'created_at'])]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $actor = $this->staff($request);
        $data = $request->validate(['role' => ['sometimes', Rule::in(['user', 'admin', 'owner'])], 'disabled' => ['sometimes', 'boolean'], 'password' => ['sometimes', 'string', 'min:12', 'max:200']]);
        try {
            if ($user->is($actor) && (($data['disabled'] ?? false) || (($data['role'] ?? $user->role) !== $user->role))) {
                throw new RuntimeException('You cannot disable or demote your own account.');
            }
            if (($data['role'] ?? $user->role) === 'owner' && ! $actor->isOwner()) {
                throw new RuntimeException('Only the Owner can grant Owner role.');
            }
            if ($user->isOwner() && ($data['role'] ?? $user->role) !== 'owner' && ! $actor->isOwner()) {
                throw new RuntimeException('Only the Owner can change the Owner role.');
            }
            if (($data['role'] ?? $user->role) !== 'owner' && $user->isOwner() && User::query()->where('role', 'owner')->count() <= 1) {
                throw new RuntimeException('The panel must retain an Owner account.');
            }
            DB::transaction(function () use ($user, $data, $actor): void {
                if (isset($data['password'])) {
                    $user->password = Hash::make($data['password']);
                    $user->password_version = ((int) $user->password_version) + 1;
                }
                $user->fill(array_intersect_key($data, array_flip(['role', 'disabled'])));
                $user->save();
                DB::table('audit_events')->insert(['user_id' => $actor->id, 'action' => 'admin.user_updated', 'resource_type' => 'user', 'resource_id' => $user->id, 'result' => 'success', 'metadata' => json_encode(['fields' => array_keys($data)]), 'created_at' => now(), 'updated_at' => now()]);
            });

            return response()->json(['user' => $user->fresh()->only(['id', 'name', 'username', 'email', 'role', 'disabled', 'email_verified_at', 'created_at'])]);
        } catch (RuntimeException $error) {
            return response()->json(['error' => $error->getMessage()], 422);
        }
    }

    private function staff(Request $request): User
    {
        abort_unless($request->user()?->isStaff(), 403, 'Staff access is required.');

        return $request->user();
    }
}
