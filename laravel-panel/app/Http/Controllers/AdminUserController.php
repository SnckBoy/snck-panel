<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserAdministrationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->staff($request);

        return response()->json(['users' => User::query()->orderBy('username')->get(['id', 'name', 'username', 'email', 'role', 'disabled', 'email_verified_at', 'created_at'])]);
    }

    public function update(Request $request, User $user, UserAdministrationService $administration): JsonResponse
    {
        $actor = $this->staff($request);
        $data = $request->validate([
            'role' => ['sometimes', Rule::in(['user', 'admin', 'owner'])],
            'disabled' => ['sometimes', 'boolean'],
            'password' => ['sometimes', 'string', 'min:12', 'max:200'],
        ]);

        try {
            $updated = $administration->update($actor, $user, $data);
        } catch (AuthorizationException $error) {
            return response()->json(['error' => $error->getMessage()], 403);
        } catch (ValidationException $error) {
            return response()->json(['error' => collect($error->errors())->flatten()->first()], 422);
        }

        return response()->json(['user' => $updated->only(['id', 'name', 'username', 'email', 'role', 'disabled', 'email_verified_at', 'created_at'])]);
    }

    private function staff(Request $request): User
    {
        abort_unless($request->user()?->isStaff(), 403, 'Staff access is required.');

        return $request->user();
    }
}
