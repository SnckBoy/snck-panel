<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserRoleManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->staff($request);

        return response()->json(['users' => User::query()->orderBy('username')->get(['id', 'name', 'username', 'email', 'role', 'disabled', 'email_verified_at', 'created_at'])]);
    }

    public function update(Request $request, User $user, UserRoleManagementService $roles): JsonResponse
    {
        $actor = $this->staff($request);
        $data = $request->validate([
            'role' => ['sometimes', 'required', 'in:user,admin,owner'],
            'disabled' => ['sometimes', 'required', 'boolean'],
            'password' => ['sometimes', 'required', 'string', 'min:12', 'max:200'],
        ]);

        $updated = $roles->update($actor, $user, $data);

        return response()->json(['user' => $updated->only(['id', 'name', 'username', 'email', 'role', 'disabled', 'email_verified_at', 'created_at'])]);
    }

    private function staff(Request $request): User
    {
        abort_unless($request->user()?->isStaff(), 403, 'Staff access is required.');

        return $request->user();
    }
}
