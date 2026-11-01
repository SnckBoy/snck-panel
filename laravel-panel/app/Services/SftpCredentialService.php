<?php

namespace App\Services;

use App\Models\Server;
use App\Models\SftpUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class SftpCredentialService
{
    public function get(Server $server): ?array
    {
        $user = SftpUser::query()->where('server_id', $server->id)->first();
        return $user ? $this->public($server, $user) : null;
    }

    public function create(Server $server): array
    {
        if (SftpUser::query()->where('server_id', $server->id)->exists()) throw new RuntimeException('An SFTP account already exists for this server.');
        return $this->issue($server, null);
    }

    public function reset(Server $server): array
    {
        $user = SftpUser::query()->where('server_id', $server->id)->first();
        if (!$user) throw new RuntimeException('SFTP user not found.');
        return $this->issue($server, $user);
    }

    public function delete(Server $server): void
    {
        SftpUser::query()->where('server_id', $server->id)->delete();
    }

    private function issue(Server $server, ?SftpUser $user): array
    {
        $username = $user?->username ?: 'sftp_'.preg_replace('/[^a-zA-Z0-9_-]/', '_', Str::limit($server->identifier ?: $server->id, 48, ''));
        $password = Str::password(32, true, true, false, false);
        if (!$user) $user = new SftpUser(['id' => (string) Str::uuid(), 'server_id' => $server->id, 'username' => $username]);
        $user->password_hash = Hash::make($password); $user->save();
        return array_merge($this->public($server, $user), ['password' => $password]);
    }

    private function public(Server $server, SftpUser $user): array
    {
        $node = $server->node;
        return ['host' => $node?->fqdn ?: $node?->hostname ?: $node?->public_ip, 'port' => (int) ($node?->sftp_port ?: 2022), 'username' => $user->username, 'password' => null];
    }
}
