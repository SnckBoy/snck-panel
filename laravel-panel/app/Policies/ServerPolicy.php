<?php

namespace App\Policies;

use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ServerPolicy
{
    private const DELEGATABLE = [
        'start', 'stop', 'restart', 'kill', 'plugins', 'mods',
        'backup', 'files', 'properties', 'settings',
    ];

    public function viewAny(User $user): bool
    {
        return $user->isStaff()
            || $user->servers()->exists()
            || $user->sharedServers()->exists();
    }

    public function create(User $user): bool
    {
        return !$user->disabled;
    }

    public function view(User $user, Server $server): bool
    {
        if ($this->isPrivilegedOrOwner($user, $server)) return true;
        return $this->hasAnyDelegatedPermission($user, $server);
    }

    public function start(User $user, Server $server): bool { return $this->allows($user, $server, 'start'); }
    public function stop(User $user, Server $server): bool { return $this->allows($user, $server, 'stop'); }
    public function restart(User $user, Server $server): bool { return $this->allows($user, $server, 'restart'); }
    public function kill(User $user, Server $server): bool { return $this->allows($user, $server, 'kill'); }
    public function files(User $user, Server $server): bool { return $this->allows($user, $server, 'files'); }
    public function backup(User $user, Server $server): bool { return $this->allows($user, $server, 'backup'); }
    public function plugins(User $user, Server $server): bool { return $this->allows($user, $server, 'plugins'); }
    public function mods(User $user, Server $server): bool { return $this->allows($user, $server, 'mods'); }
    public function properties(User $user, Server $server): bool { return $this->allows($user, $server, 'properties'); }
    public function settings(User $user, Server $server): bool { return $this->allows($user, $server, 'settings'); }

    // Console/RCON is intentionally not delegated: legacy grants have no
    // console scope and a console command may alter or destroy world state.
    public function command(User $user, Server $server): bool
    {
        return $this->isPrivilegedOrOwner($user, $server);
    }

    public function logs(User $user, Server $server): bool { return $this->view($user, $server); }
    public function stats(User $user, Server $server): bool { return $this->view($user, $server); }
    public function status(User $user, Server $server): bool { return $this->view($user, $server); }

    // The legacy vocabulary has no narrowly-scoped SFTP grant. Keep credential
    // access to staff and the owning account until a separate daemon contract exists.
    public function sftp(User $user, Server $server): bool
    {
        return $this->isPrivilegedOrOwner($user, $server);
    }

    public function manageSubusers(User $user, Server $server): bool
    {
        return $this->isPrivilegedOrOwner($user, $server);
    }

    public function delete(User $user, Server $server): bool
    {
        return $this->isPrivilegedOrOwner($user, $server);
    }

    private function allows(User $user, Server $server, string $permission): bool
    {
        if ($this->isPrivilegedOrOwner($user, $server)) return true;
        if (!in_array($permission, self::DELEGATABLE, true)) return false;
        return in_array($permission, $this->delegatedPermissions($user, $server), true);
    }

    private function isPrivilegedOrOwner(User $user, Server $server): bool
    {
        return $user->isStaff() || (string) $server->owner_id === (string) $user->id;
    }

    private function hasAnyDelegatedPermission(User $user, Server $server): bool
    {
        return count(array_intersect(self::DELEGATABLE, $this->delegatedPermissions($user, $server))) > 0;
    }

    private function delegatedPermissions(User $user, Server $server): array
    {
        $raw = DB::table('server_subusers')
            ->where('server_id', $server->getKey())
            ->where('user_id', $user->getKey())
            ->value('permissions');
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($raw)) return [];

        // '*' is intentionally not a delegated permission. Only the server's
        // actual owner (or platform staff) receives complete access.
        return array_values(array_intersect($raw, self::DELEGATABLE));
    }
}
