<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminUserRoleService
{
    /**
     * Apply an administrator-managed account update and record each security-sensitive change.
     *
     * @param array{role?: string, disabled?: bool, password?: string} $changes
     */
    public function update(User $actor, User $target, array $changes): User
    {
        return DB::transaction(function () use ($actor, $target, $changes): User {
            // Lock the owner set before checking the invariant so concurrent updates
            // cannot independently remove the final active owner.
            $owners = User::query()
                ->where('role', 'owner')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $target = User::query()->lockForUpdate()->findOrFail($target->getKey());

            if (! $actor->isStaff()) {
                throw new AuthorizationException('Staff access is required.');
            }

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

            if ($target->isOwner() && $nextRole !== 'owner' && ! $actor->isOwner()) {
                throw new AuthorizationException('Only the Owner can remove the Owner role.');
            }

            if ($target->is($actor) && ($nextDisabled || $nextRole !== $target->role)) {
                throw ValidationException::withMessages([
                    'user' => 'You cannot disable or demote your own account.',
                ]);
            }

            $isRemovingActiveOwner = $target->isOwner()
                && ($nextRole !== 'owner' || $nextDisabled)
                && ! $target->disabled;

            if ($isRemovingActiveOwner) {
                $activeOwners = $owners->where('disabled', false)->count();

                if ($activeOwners <= 1) {
                    throw ValidationException::withMessages([
                        'user' => 'The panel must retain at least one enabled Owner account.',
                    ]);
                }
            }

            $audit = [];
            if ($nextRole !== $target->role) {
                $audit[] = [
                    'action' => 'admin.user_role_changed',
                    'metadata' => ['from' => $target->role, 'to' => $nextRole],
                ];
                $target->role = $nextRole;
            }

            if ($nextDisabled !== (bool) $target->disabled) {
                $audit[] = [
                    'action' => $nextDisabled ? 'admin.user_disabled' : 'admin.user_enabled',
                    'metadata' => ['disabled' => $nextDisabled],
                ];
                $target->disabled = $nextDisabled;
            }

            if (isset($changes['password']) && $changes['password'] !== '') {
                $target->password = Hash::make($changes['password']);
                $target->password_version = ((int) $target->password_version) + 1;
                $audit[] = [
                    'action' => 'admin.user_password_changed',
                    'metadata' => ['password_version' => $target->password_version],
                ];
            }

            if ($audit !== []) {
                $target->save();

                foreach ($audit as $event) {
                    DB::table('audit_events')->insert([
                        'user_id' => $actor->id,
                        'action' => $event['action'],
                        'resource_type' => 'user',
                        'resource_id' => $target->id,
                        'result' => 'success',
                        'metadata' => json_encode($event['metadata'], JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return $target->fresh();
        });
    }
}
