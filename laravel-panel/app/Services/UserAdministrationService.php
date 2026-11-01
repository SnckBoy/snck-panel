<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserAdministrationService
{
    public function assertCanManage(User $actor, User $target): void
    {
        abort_unless($actor->isStaff(), 403, 'Staff access is required.');

        if ($target->isOwner() && ! $actor->isOwner()) {
            throw new AuthorizationException('Admins cannot manage Owner accounts.');
        }
    }

    public function update(User $actor, User $target, array $data): User
    {
        return DB::transaction(function () use ($actor, $target, $data): User {
            $actor = User::query()->findOrFail($actor->getKey());
            $target = User::query()->lockForUpdate()->findOrFail($target->getKey());
            $this->assertCanManage($actor, $target);

            $requestedRole = $data['role'] ?? $target->role;
            $requestedDisabled = array_key_exists('disabled', $data) ? (bool) $data['disabled'] : $target->disabled;

            if ($requestedRole === 'owner' && ! $actor->isOwner()) {
                throw new AuthorizationException('Only an Owner can grant the Owner role.');
            }

            if ($target->isOwner() && $requestedRole !== 'owner' && ! $actor->isOwner()) {
                throw new AuthorizationException('Only an Owner can remove the Owner role.');
            }

            if ($target->is($actor) && ($requestedRole !== $target->role || $requestedDisabled)) {
                throw ValidationException::withMessages(['user' => 'You cannot disable or demote your own account.']);
            }

            $owners = User::query()->where('role', 'owner')->lockForUpdate()->get(['id']);
            if ($target->isOwner() && ($requestedRole !== 'owner' || $requestedDisabled) && $owners->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'The last Owner cannot be demoted or disabled.']);
            }

            $changes = [];
            if ($requestedRole !== $target->role) {
                $changes['role'] = ['from' => $target->role, 'to' => $requestedRole];
                $target->role = $requestedRole;
            }

            if ($requestedDisabled !== $target->disabled) {
                $changes['disabled'] = ['from' => $target->disabled, 'to' => $requestedDisabled];
                $target->disabled = $requestedDisabled;
            }

            if (array_key_exists('password', $data) && $data['password'] !== null && $data['password'] !== '') {
                $target->password = Hash::make($data['password']);
                $target->password_version = ((int) $target->password_version) + 1;
                $changes['password'] = ['changed' => true];
            }

            $target->save();

            foreach (['role' => 'admin.user_role_changed', 'disabled' => 'admin.user_disabled', 'password' => 'admin.user_password_reset'] as $field => $action) {
                if (array_key_exists($field, $changes)) {
                    $this->audit($actor, $target, $action, [$field => $changes[$field]]);
                }
            }

            return $target->fresh();
        });
    }

    public function delete(User $actor, User $target): void
    {
        DB::transaction(function () use ($actor, $target): void {
            $actor = User::query()->findOrFail($actor->getKey());
            $target = User::query()->lockForUpdate()->findOrFail($target->getKey());
            $this->assertCanManage($actor, $target);

            if ($target->is($actor)) {
                throw ValidationException::withMessages(['user' => 'You cannot delete your own account.']);
            }

            $owners = User::query()->where('role', 'owner')->lockForUpdate()->get(['id']);
            if ($target->isOwner() && $owners->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'The last Owner cannot be deleted.']);
            }

            $this->audit($actor, $target, 'admin.user_deleted', ['username' => $target->username]);
            $target->delete();
        });
    }

    private function audit(User $actor, User $target, string $action, array $metadata): void
    {
        DB::table('audit_events')->insert([
            'user_id' => $actor->id,
            'action' => $action,
            'resource_type' => 'user',
            'resource_id' => $target->id,
            'result' => 'success',
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
