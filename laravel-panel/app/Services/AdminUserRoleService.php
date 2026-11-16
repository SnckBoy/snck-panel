<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminUserRoleService
{
    /**
     * @param array{role?: string, disabled?: bool, password?: string} $changes
     */
    public function update(User $actor, User $target, array $changes): User
    {
        Validator::make($changes, [
            'role' => ['sometimes', 'required', Rule::in(['user', 'admin', 'owner'])],
            'disabled' => ['sometimes', 'boolean'],
            'password' => ['sometimes', 'nullable', 'string', 'min:12', 'max:200'],
        ])->validate();

        return DB::transaction(function () use ($actor, $target, $changes): User {
            $owners = $this->lockOwners();
            $target = User::query()->lockForUpdate()->findOrFail($target->getKey());
            $this->authorizeActor($actor);

            if ($target->isOwner() && ! $actor->isOwner()) {
                throw new AuthorizationException('Admins cannot modify an Owner account.');
            }

            $nextRole = $changes['role'] ?? $target->role;
            $nextDisabled = array_key_exists('disabled', $changes)
                ? (bool) $changes['disabled']
                : (bool) $target->disabled;

            if ($nextRole === 'owner' && ! $actor->isOwner()) {
                throw new AuthorizationException('Only the Owner can grant the Owner role.');
            }

            if ($target->is($actor) && ($nextDisabled || $nextRole !== $target->role)) {
                throw ValidationException::withMessages(['user' => 'You cannot disable or demote your own account.']);
            }

            if ($target->isOwner() && $nextRole !== 'owner' && $owners->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'The last Owner account cannot be demoted.']);
            }

            if ($target->isOwner() && $nextDisabled && ! $target->disabled
                && $owners->where('disabled', false)->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'The panel must retain at least one enabled Owner account.']);
            }

            $events = [];
            if ($nextRole !== $target->role) {
                $events[] = ['action' => 'admin.user_role_changed', 'metadata' => ['from' => $target->role, 'to' => $nextRole]];
                $target->role = $nextRole;
            }

            if ($nextDisabled !== (bool) $target->disabled) {
                $events[] = ['action' => $nextDisabled ? 'admin.user_disabled' : 'admin.user_enabled', 'metadata' => ['disabled' => $nextDisabled]];
                $target->disabled = $nextDisabled;
            }

            if (isset($changes['password']) && $changes['password'] !== '') {
                $target->password = Hash::make($changes['password']);
                $target->password_version = ((int) $target->password_version) + 1;
                $events[] = ['action' => 'admin.user_password_changed', 'metadata' => ['password_version' => $target->password_version]];
            }

            if ($events !== []) {
                $target->save();
                foreach ($events as $event) {
                    $this->audit($actor, $target, $event['action'], $event['metadata']);
                }
            }

            return $target->fresh();
        });
    }

    public function delete(User $actor, User $target): void
    {
        DB::transaction(function () use ($actor, $target): void {
            $owners = $this->lockOwners();
            $target = User::query()->lockForUpdate()->findOrFail($target->getKey());
            $this->authorizeActor($actor);

            if ($target->is($actor)) {
                throw ValidationException::withMessages(['user' => 'You cannot delete your own account.']);
            }

            if ($target->isOwner() && ! $actor->isOwner()) {
                throw new AuthorizationException('Admins cannot delete an Owner account.');
            }

            if ($target->isOwner() && $owners->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'The last Owner account cannot be deleted.']);
            }

            if ($target->isOwner() && ! $target->disabled && $owners->where('disabled', false)->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'The panel must retain at least one enabled Owner account.']);
            }

            if ($target->servers()->exists()) {
                throw ValidationException::withMessages(['user' => 'Transfer or delete this user’s servers before deleting the account.']);
            }

            $this->audit($actor, $target, 'admin.user_deleted', []);
            $target->delete();
        });
    }

    private function lockOwners()
    {
        return User::query()->where('role', 'owner')->orderBy('id')->lockForUpdate()->get();
    }

    private function authorizeActor(User $actor): void
    {
        if (! $actor->isStaff()) {
            throw new AuthorizationException('Staff access is required.');
        }
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
