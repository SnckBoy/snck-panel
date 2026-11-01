<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserRoleManagementService
{
    public function assertCanEdit(User $actor, User $target): void
    {
        if (! $actor->isStaff()) {
            throw new AuthorizationException('Staff access is required.');
        }

        if ($actor->isAdmin() && $target->isOwner()) {
            throw new AuthorizationException('Administrators cannot manage Owner accounts.');
        }
    }

    public function update(User $actor, User $target, array $changes): User
    {
        return DB::transaction(function () use ($actor, $target, $changes): User {
            // Lock Owner rows in a stable order before locking the target to
            // serialize concurrent Owner changes without inconsistent lock order.
            $owners = User::query()
                ->where('role', 'owner')
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $lockedTarget = User::query()->whereKey($target->getKey())->lockForUpdate()->firstOrFail();
            $this->assertCanEdit($actor, $lockedTarget);

            $requestedRole = $changes['role'] ?? $lockedTarget->role;
            $requestedDisabled = (bool) ($changes['disabled'] ?? $lockedTarget->disabled);
            $roleChanged = $requestedRole !== $lockedTarget->role;
            $disabledChanged = $requestedDisabled !== $lockedTarget->disabled;
            $passwordChanged = isset($changes['password']) && $changes['password'] !== '';

            if (($requestedRole === 'owner' || ($lockedTarget->isOwner() && $roleChanged)) && ! $actor->isOwner()) {
                throw new AuthorizationException('Only an Owner can grant or remove the Owner role.');
            }

            if ($lockedTarget->is($actor) && ($requestedDisabled || $roleChanged)) {
                throw ValidationException::withMessages(['user' => 'You cannot disable or demote your own account.']);
            }

            if ($lockedTarget->isOwner() && ($roleChanged || ($disabledChanged && $requestedDisabled)) && $owners->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'The last Owner account cannot be demoted or disabled.']);
            }

            if ($roleChanged) {
                $lockedTarget->role = $requestedRole;
            }
            if ($disabledChanged) {
                $lockedTarget->disabled = $requestedDisabled;
            }
            if ($passwordChanged) {
                $lockedTarget->password = Hash::make($changes['password']);
                $lockedTarget->password_version = ((int) $lockedTarget->password_version) + 1;
            }
            if ($roleChanged || $disabledChanged || $passwordChanged) {
                $lockedTarget->save();
            }

            if ($roleChanged) {
                $this->audit($actor, $lockedTarget, 'admin.user_role_changed', [
                    'from' => $target->role,
                    'to' => $requestedRole,
                ]);
            }
            if ($disabledChanged) {
                $this->audit($actor, $lockedTarget, $requestedDisabled ? 'admin.user_disabled' : 'admin.user_enabled', [
                    'from' => $target->disabled,
                    'to' => $requestedDisabled,
                ]);
            }
            if ($passwordChanged) {
                $this->audit($actor, $lockedTarget, 'admin.user_password_reset');
            }

            return $lockedTarget->fresh();
        });
    }

    public function delete(User $actor, User $target): void
    {
        DB::transaction(function () use ($actor, $target): void {
            $owners = User::query()
                ->where('role', 'owner')
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $lockedTarget = User::query()->whereKey($target->getKey())->lockForUpdate()->firstOrFail();
            $this->assertCanEdit($actor, $lockedTarget);

            if ($lockedTarget->is($actor)) {
                throw ValidationException::withMessages(['user' => 'You cannot delete your own account.']);
            }

            if ($lockedTarget->isOwner()) {
                if (! $actor->isOwner()) {
                    throw new AuthorizationException('Only an Owner can delete an Owner account.');
                }

                if ($owners->count() <= 1) {
                    throw ValidationException::withMessages(['user' => 'The last Owner account cannot be deleted.']);
                }
            }

            $this->audit($actor, $lockedTarget, 'admin.user_deleted');
            $lockedTarget->delete();
        });
    }

    private function audit(User $actor, User $target, string $action, array $metadata = []): void
    {
        DB::table('audit_events')->insert([
            'user_id' => $actor->id,
            'action' => $action,
            'resource_type' => 'user',
            'resource_id' => $target->id,
            'result' => 'success',
            'metadata' => json_encode($metadata),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
