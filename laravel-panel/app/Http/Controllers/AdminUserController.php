<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdminUserRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->staff($request);

        return response()->json([
            'users' => User::query()
                ->orderBy('username')
                ->get(['id', 'name', 'username', 'email', 'role', 'disabled', 'email_verified_at', 'created_at']),
        ]);
    }

    public function update(Request $request, User $user, AdminUserRoleService $roleService): JsonResponse
    {
        $actor = $this->staff($request);
        $data = $request->validate([
            'role' => ['sometimes', 'required', Rule::in(['user', 'admin', 'owner'])],
            'disabled' => ['sometimes', 'boolean'],
            'password' => ['sometimes', 'required', 'string', 'min:12', 'max:200'],
        ]);

        $updated = $roleService->update($actor, $user, $data);

        return response()->json([
            'user' => $updated->only(['id', 'name', 'username', 'email', 'role', 'disabled', 'email_verified_at', 'created_at']),
        ]);
    }

    public function destroy(Request $request, User $user, AdminUserRoleService $roleService): JsonResponse
    {
        $actor = $this->staff($request);
        $roleService->delete($actor, $user);

        return response()->json(['success' => true]);
    }

    private function staff(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User && $actor->isStaff(), 403, 'Staff access is required.');

        return $actor;
    }
}
