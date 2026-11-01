<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

class ImportLegacyJson extends Command
{
    protected $signature = 'snck:import-json {path : Path to the legacy .data directory} {--dry-run : Validate and report without writing rows or creating a backup} {--force : Allow reapplying a source snapshot that was imported before} {--backup-dir= : Private directory for the pre-import .data ZIP backup}';

    protected $description = 'Validate and import legacy Snck JSON into PostgreSQL; preserve the source and never dual-write';

    private array $counts = [];

    public function handle(): int
    {
        try {
            if (! config('app.key')) {
                throw new \RuntimeException('APP_KEY must be generated before import.');
            }
            if (DB::connection()->getDriverName() !== 'pgsql') {
                throw new \RuntimeException('The Phase 2 importer requires PostgreSQL.');
            }
            if (! class_exists(ZipArchive::class)) {
                throw new \RuntimeException('PHP ext-zip is required for the mandatory pre-import backup.');
            }

            $source = realpath((string) $this->argument('path'));
            if (! $source || ! is_dir($source) || is_link((string) $this->argument('path'))) {
                throw new \InvalidArgumentException('Legacy data path must be an existing, real directory (not a symbolic link).');
            }

            $snapshot = $this->readSnapshot($source);
            $this->validateSnapshot($snapshot);
            $sourceHash = $this->snapshotHash($snapshot);
            $this->counts = $this->countSnapshot($snapshot);

            if ($this->option('dry-run')) {
                $this->info('Dry run passed. No database rows were changed and no backup was created.');
                $this->showCounts();

                return self::SUCCESS;
            }

            $alreadyImported = DB::table('legacy_import_runs')->where('source_sha256', $sourceHash)->exists();
            if ($alreadyImported && ! $this->option('force')) {
                throw new \RuntimeException('This exact JSON snapshot was imported already. Use --force only to intentionally reapply it.');
            }

            $backupPath = $this->createBackup($source, $snapshot);
            DB::transaction(function () use ($snapshot, $source, $sourceHash, $backupPath): void {
                $this->archiveSnapshot($snapshot);
                $this->importUsers($snapshot['users.json']['value'] ?? []);
                $this->importNodes($snapshot['nodes.json']['value'] ?? []);
                $this->importServers($snapshot['servers.json']['value'] ?? [], $snapshot['nodes.json']['value'] ?? []);
                $this->importServerSubusers($snapshot['servers.json']['value'] ?? []);
                $this->importAllocations($snapshot['allocations.json']['value'] ?? [], $snapshot['servers.json']['value'] ?? []);
                $this->importBackups($snapshot['backups.json']['value'] ?? [], $snapshot['servers.json']['value'] ?? []);
                $this->importApiKeys($snapshot['api_keys.json']['value'] ?? []);
                $this->importSftpUsers($snapshot['sftp_users.json']['value'] ?? [], $snapshot['servers.json']['value'] ?? []);
                $this->importSettings($snapshot['settings.json']['value'] ?? []);
                $this->importThemes($snapshot['themes.json']['value'] ?? []);
                $this->importCloudflareAccounts($snapshot['cloudflare_accounts.json']['value'] ?? []);
                $this->importDiscordRewards($snapshot['discord_rewards.json']['value'] ?? null);
                $this->importAuditLogs($snapshot['audit_logs.json']['value'] ?? []);
                $this->importNotifications($snapshot['notifications.json']['value'] ?? []);
                $this->importServerTransfers($snapshot['server_transfers.json']['value'] ?? []);

                DB::table('legacy_import_runs')->insert([
                    'id' => (string) Str::uuid(),
                    'source_sha256' => $sourceHash,
                    'source_label' => basename($source),
                    'backup_path' => $backupPath,
                    'counts' => json_encode($this->counts, JSON_THROW_ON_ERROR),
                    'completed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }, 3);

            $this->info('Import committed atomically. The source .data directory was not modified.');
            $this->line('Pre-import backup: '.$backupPath);
            $this->showCounts();

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            $this->line('Database transaction rolled back; source JSON was left unchanged.');

            return self::FAILURE;
        }
    }

    private function readSnapshot(string $root): array
    {
        $files = glob($root.DIRECTORY_SEPARATOR.'*.json') ?: [];
        sort($files, SORT_STRING);
        $snapshot = [];
        $totalBytes = 0;
        foreach ($files as $path) {
            if (is_link($path) || ! is_file($path)) {
                throw new \RuntimeException('Top-level JSON inputs must be regular files, not symlinks.');
            }
            $size = filesize($path);
            if ($size === false || $size > 100 * 1024 * 1024) {
                throw new \RuntimeException('JSON input exceeds the 100 MiB per-file limit: '.basename($path));
            }
            $totalBytes += $size;
            if ($totalBytes > 512 * 1024 * 1024) {
                throw new \RuntimeException('JSON inputs exceed the 512 MiB total import limit.');
            }
            $raw = file_get_contents($path);
            if ($raw === false) {
                throw new \RuntimeException('Unable to read JSON input: '.basename($path));
            }
            $rootText = ltrim($raw);
            if ($rootText === '' || ! in_array($rootText[0], ['{', '['], true)) {
                throw new \RuntimeException('JSON root must be an object or array: '.basename($path));
            }
            try {
                $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new \RuntimeException('Invalid JSON in '.basename($path).': '.$exception->getMessage(), previous: $exception);
            }
            if (! is_array($decoded)) {
                throw new \RuntimeException('JSON root must be an object or array: '.basename($path));
            }
            $snapshot[basename($path)] = [
                'value' => $decoded,
                'raw' => $raw,
                'sha256' => hash('sha256', $raw),
                'is_list' => $rootText[0] === '[',
            ];
        }

        return $snapshot;
    }

    private function validateSnapshot(array $snapshot): void
    {
        foreach (['users.json', 'nodes.json', 'servers.json', 'allocations.json', 'backups.json', 'api_keys.json', 'sftp_users.json', 'themes.json', 'audit_logs.json', 'cloudflare_accounts.json', 'notifications.json', 'server_transfers.json'] as $filename) {
            if (isset($snapshot[$filename]) && ! $snapshot[$filename]['is_list']) {
                throw new \RuntimeException($filename.' must contain a JSON array.');
            }
        }
        if (isset($snapshot['settings.json']) && $snapshot['settings.json']['is_list']) {
            throw new \RuntimeException('settings.json must contain a JSON object.');
        }

        $users = $snapshot['users.json']['value'] ?? [];
        $nodes = $snapshot['nodes.json']['value'] ?? [];
        $servers = $snapshot['servers.json']['value'] ?? [];
        $userIds = [];
        $nodeIds = [];
        $serverIds = [];
        $usernames = [];
        $emails = [];
        foreach ($users as $index => $row) {
            if (! is_array($row)) {
                throw new \RuntimeException("users.json row {$index} must be an object.");
            }
            $id = $this->requiredId($row['id'] ?? null, "users.json row {$index}");
            $userIds[$id] = true;
            $role = (string) ($row['role'] ?? 'user');
            if (! in_array($role, ['owner', 'admin', 'user'], true)) {
                throw new \RuntimeException("Unsupported role '{$role}' for user {$id}.");
            }
            $username = isset($row['username']) && trim((string) $row['username']) !== '' ? Str::lower(trim((string) $row['username'])) : null;
            if ($username && isset($usernames[$username])) {
                throw new \RuntimeException("Duplicate normalized username '{$username}'.");
            }
            if ($username) {
                $usernames[$username] = true;
            }
            $email = isset($row['email']) && trim((string) $row['email']) !== '' ? Str::lower(trim((string) $row['email'])) : null;
            if ($email && (! filter_var($email, FILTER_VALIDATE_EMAIL) || isset($emails[$email]))) {
                throw new \RuntimeException("Invalid or duplicate normalized email for user {$id}.");
            }
            if ($email) {
                $emails[$email] = true;
            }
        }
        $ownerCount = count(array_filter($users, fn (array $row) => ($row['role'] ?? 'user') === 'owner'));
        if ($ownerCount > 1) {
            throw new \RuntimeException('Source contains more than one owner account; resolve this role conflict before importing.');
        }

        foreach ($nodes as $index => $row) {
            if (! is_array($row)) {
                throw new \RuntimeException("nodes.json row {$index} must be an object.");
            }
            $id = $this->requiredId($row['id'] ?? null, "nodes.json row {$index}");
            $nodeIds[$id] = true;
            $isLocal = (bool) ($row['isLocal'] ?? false);
            $protocol = strtolower((string) ($row['protocol'] ?? 'https'));
            if (! $isLocal && ($protocol !== 'https' || ($row['tls'] ?? true) === false || ($row['tlsVerify'] ?? true) === false)) {
                throw new \RuntimeException("Node {$id} is configured without verified HTTPS. Configure HTTPS and a trusted/custom CA before importing; certificate verification cannot be disabled.");
            }
            if (! $isLocal && trim((string) ($row['hostname'] ?? $row['host'] ?? $row['fqdn'] ?? $row['publicIp'] ?? '')) === '') {
                throw new \RuntimeException("Remote node {$id} has no hostname or IP address.");
            }
            if (! empty($row['customCaPem']) && (! str_contains((string) $row['customCaPem'], '-----BEGIN CERTIFICATE-----') || ! @openssl_x509_read((string) $row['customCaPem']))) {
                throw new \RuntimeException("Node {$id} customCaPem is not a valid PEM X.509 certificate.");
            }
            foreach (['credential', 'cloudflareAccessClientSecret'] as $secretKey) {
                if (! empty($row[$secretKey])) {
                    $this->decryptLegacySecret((string) $row[$secretKey]);
                }
            }
        }
        $localNodeId = null;
        foreach ($nodes as $node) {
            if (($node['isLocal'] ?? false) === true) {
                $localNodeId = (string) $node['id'];
                break;
            }
        }
        foreach ($servers as $index => $row) {
            if (! is_array($row)) {
                throw new \RuntimeException("servers.json row {$index} must be an object.");
            }
            $id = $this->requiredId($row['id'] ?? null, "servers.json row {$index}");
            $serverIds[$id] = true;
            $ownerId = (string) ($row['ownerId'] ?? $row['owner'] ?? '');
            $nodeId = (string) ($row['nodeId'] ?? $localNodeId ?? '');
            if ($ownerId === '' || ! isset($userIds[$ownerId])) {
                throw new \RuntimeException("Server {$id} has no valid owner. Resolve its ownerId/owner before importing.");
            }
            if ($nodeId === '' || ! isset($nodeIds[$nodeId])) {
                throw new \RuntimeException("Server {$id} references missing node '{$nodeId}'. Resolve nodeId before importing.");
            }
        }
        foreach ($snapshot['allocations.json']['value'] ?? [] as $index => $row) {
            if (! is_array($row)) {
                throw new \RuntimeException("allocations.json row {$index} must be an object.");
            }
            $nodeId = (string) ($row['nodeId'] ?? '');
            if ($nodeId === '' || ! isset($nodeIds[$nodeId])) {
                throw new \RuntimeException("Allocation row {$index} references missing node '{$nodeId}'.");
            }
            $serverId = (string) ($row['assignedServerId'] ?? $row['serverId'] ?? '');
            if ($serverId !== '' && ! isset($serverIds[$serverId])) {
                throw new \RuntimeException("Allocation row {$index} references missing server '{$serverId}'.");
            }
            $start = (int) ($row['portStart'] ?? $row['startPort'] ?? $row['port'] ?? 0);
            $end = (int) ($row['portEnd'] ?? $row['endPort'] ?? $row['port'] ?? 0);
            if ($start < 1 || $end > 65535 || $end < $start || $end - $start > 9999) {
                throw new \RuntimeException("Allocation row {$index} has an invalid port or range.");
            }
        }
        foreach ($snapshot['backups.json']['value'] ?? [] as $index => $row) {
            $serverId = (string) ($row['serverId'] ?? $row['server_id'] ?? '');
            if ($serverId === '' || ! isset($serverIds[$serverId])) {
                throw new \RuntimeException("Backup row {$index} references missing server '{$serverId}'.");
            }
        }
        foreach ($snapshot['api_keys.json']['value'] ?? [] as $index => $row) {
            $this->requiredId($row['id'] ?? null, "api_keys.json row {$index}");
            $userId = (string) ($row['created_by'] ?? $row['createdBy'] ?? '');
            if ($userId === '' || ! isset($userIds[$userId])) {
                throw new \RuntimeException("API key row {$index} references missing creator '{$userId}'.");
            }
            if (empty($row['key_hash']) && empty($row['keyHash'])) {
                throw new \RuntimeException("API key row {$index} has no persisted key hash.");
            }
        }
        foreach ($snapshot['sftp_users.json']['value'] ?? [] as $index => $row) {
            $this->requiredId($row['id'] ?? null, "sftp_users.json row {$index}");
            $serverId = (string) ($row['serverId'] ?? $row['server_id'] ?? '');
            if ($serverId === '' || ! isset($serverIds[$serverId])) {
                throw new \RuntimeException("SFTP row {$index} references missing server '{$serverId}'.");
            }
        }
        foreach ($snapshot['cloudflare_accounts.json']['value'] ?? [] as $index => $row) {
            if (! is_array($row) || empty($row['id']) || empty($row['token'])) {
                throw new \RuntimeException("Cloudflare account row {$index} is missing an id or token.");
            }
            $this->decryptLegacySecret((string) $row['token']);
        }
        foreach ($servers as $server) {
            foreach (($server['subUsers'] ?? []) as $memberIndex => $member) {
                $userId = (string) ($member['userId'] ?? '');
                if ($userId === '' || ! isset($userIds[$userId])) {
                    throw new \RuntimeException("Server {$server['id']} sub-user {$memberIndex} references missing user '{$userId}'.");
                }
            }
        }
        foreach ($snapshot['notifications.json']['value'] ?? [] as $index => $row) {
            $userId = (string) ($row['userId'] ?? $row['user_id'] ?? '');
            if ($userId === '' || ! isset($userIds[$userId])) {
                throw new \RuntimeException("Notification row {$index} references missing user '{$userId}'.");
            }
        }
        foreach ($snapshot['server_transfers.json']['value'] ?? [] as $index => $row) {
            $serverId = (string) ($row['serverId'] ?? $row['server_id'] ?? '');
            $sourceNodeId = (string) ($row['sourceNodeId'] ?? $row['source_node_id'] ?? '');
            $destinationNodeId = (string) ($row['destinationNodeId'] ?? $row['destination_node_id'] ?? '');
            if (! isset($serverIds[$serverId]) || ! isset($nodeIds[$sourceNodeId]) || ! isset($nodeIds[$destinationNodeId])) {
                throw new \RuntimeException("Server transfer row {$index} references a missing server or node.");
            }
        }
        $this->counts['users'] = count($users);
        $this->counts['nodes'] = count($nodes);
        $this->counts['servers'] = count($servers);
        $this->counts['allocations'] = $this->expandedAllocationCount($snapshot['allocations.json']['value'] ?? []);
    }

    private function importUsers(array $rows): void
    {
        foreach ($rows as $row) {
            $id = (string) $row['id'];
            $password = $this->normalizeBcryptHash((string) ($row['passwordHash'] ?? $row['password_hash'] ?? $row['password'] ?? ''));
            $passwordInfo = $password !== '' ? password_get_info($password) : ['algo' => null];
            $usableHash = $passwordInfo['algo'] !== null;
            if (! $usableHash) {
                $password = Hash::make(Str::random(64));
            }
            $username = isset($row['username']) && trim((string) $row['username']) !== '' ? Str::lower(trim((string) $row['username'])) : null;
            $email = isset($row['email']) && trim((string) $row['email']) !== '' ? Str::lower(trim((string) $row['email'])) : null;
            DB::table('users')->updateOrInsert(['id' => $id], [
                'name' => mb_substr((string) ($row['name'] ?? $username ?? 'Imported User'), 0, 128),
                'username' => $username,
                'email' => $email,
                'role' => (string) ($row['role'] ?? 'user'),
                'disabled' => (bool) ($row['disabled'] ?? false) || ! $usableHash,
                'google_id' => $row['googleId'] ?? $row['google_id'] ?? null,
                'password_version' => max(0, (int) ($row['passwordVersion'] ?? $row['password_version'] ?? 0)),
                'email_verified_at' => $this->time($row['emailVerifiedAt'] ?? $row['email_verified_at'] ?? null),
                'password' => $password,
                'remember_token' => null,
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? $row['created_at'] ?? null) ?? now(),
            ]);
        }
        $ownerId = null;
        foreach ($rows as $row) {
            if (($row['role'] ?? 'user') === 'owner') {
                $ownerId = (string) $row['id'];
                break;
            }
        }
        if ($ownerId !== null) {
            DB::table('setup_state')->where('id', 1)->update(['owner_user_id' => $ownerId, 'completed_at' => now()]);
        }
    }

    /**
     * Node's bcrypt/bcryptjs emit $2a$ or $2b$ hashes. PHP's password API
     * consistently recognizes the equivalent $2y$ marker; the bcrypt
     * algorithm, cost, salt, and digest are unchanged by this prefix rewrite.
     */
    private function normalizeBcryptHash(string $hash): string
    {
        return preg_match('/^\$2[ab]\$/', $hash) === 1
            ? '$2y$'.substr($hash, 4)
            : $hash;
    }

    private function importNodes(array $rows): void
    {
        foreach ($rows as $row) {
            $id = (string) $row['id'];
            $credential = ! empty($row['credential']) ? Crypt::encryptString($this->decryptLegacySecret((string) $row['credential'])) : null;
            $accessSecret = ! empty($row['cloudflareAccessClientSecret']) ? Crypt::encryptString($this->decryptLegacySecret((string) $row['cloudflareAccessClientSecret'])) : null;
            $heartbeat = $row['lastHeartbeat'] ?? $row['last_heartbeat'] ?? null;
            DB::table('nodes')->updateOrInsert(['id' => $id], [
                'name' => mb_substr((string) ($row['name'] ?? 'Imported node'), 0, 128),
                'description' => $row['description'] ?? null,
                'host' => $row['hostname'] ?? $row['host'] ?? $row['fqdn'] ?? $row['publicIp'] ?? null,
                'fqdn' => $row['fqdn'] ?? $row['hostname'] ?? null,
                'hostname' => $row['hostname'] ?? $row['fqdn'] ?? null,
                'public_ip' => $row['publicIp'] ?? null,
                'internal_ip' => $row['internalIp'] ?? null,
                'location' => $row['location'] ?? null,
                'visibility' => ($row['visibility'] ?? 'public') === 'private' ? 'private' : 'public',
                'is_local' => (bool) ($row['isLocal'] ?? false),
                'public_port' => $this->port($row['publicPort'] ?? 443, 'public port'),
                'daemon_port' => $this->port($row['apiPort'] ?? $row['daemonPort'] ?? 6768, 'daemon port'),
                'api_port' => $this->port($row['apiPort'] ?? $row['daemonPort'] ?? 6768, 'API port'),
                'sftp_port' => $this->port($row['sftpPort'] ?? 2022, 'SFTP port'),
                'behind_proxy' => (bool) ($row['behindProxy'] ?? false),
                'custom_ca_pem' => $row['customCaPem'] ?? null,
                'access_client_id' => $row['cloudflareAccessClientId'] ?? null,
                'access_client_secret_encrypted' => $accessSecret,
                'credential_encrypted' => $credential,
                'credential_hash' => $row['credentialHash'] ?? null,
                'daemon_version' => $row['daemonVersion'] ?? null,
                // Never carry a cached green state across a migration cutover.
                'status' => 'offline',
                'last_heartbeat_at' => $this->time($heartbeat),
                'stats' => null,
                'last_health_check' => null,
                'docker_available' => false,
                'disabled' => (bool) ($row['disabled'] ?? false),
                'maintenance' => (bool) ($row['maintenance'] ?? false),
                'free_service_locked' => (bool) ($row['freeServiceLocked'] ?? false),
                'memory_limit_mb' => $this->nullableInt($row['memory'] ?? null),
                'disk_limit_mb' => $this->nullableInt(isset($row['disk']) ? round((float) $row['disk'] * 1024) : null),
                'cpu_limit_percent' => $this->nullableInt($row['cpu'] ?? null),
                'memory_overallocate' => (float) ($row['memoryOverallocate'] ?? 0),
                'disk_overallocate' => (float) ($row['diskOverallocate'] ?? 0),
                'server_directory' => $row['serverDirectory'] ?? '/var/lib/snck/servers',
                'docker_host' => $row['dockerHost'] ?? '/var/run/docker.sock',
                'cloudflare_zone_id' => $row['cloudflareZoneId'] ?? null,
                'cloudflare_record_id' => $row['cloudflareRecordId'] ?? null,
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function importServers(array $rows, array $nodeRows): void
    {
        $localNodeId = null;
        foreach ($nodeRows as $node) {
            if (($node['isLocal'] ?? false) === true) {
                $localNodeId = (string) $node['id'];
                break;
            }
        }
        foreach ($rows as $row) {
            $id = (string) $row['id'];
            $ownerId = (string) ($row['ownerId'] ?? $row['owner']);
            $nodeId = (string) ($row['nodeId'] ?? $localNodeId);
            $ram = $this->nullableFloat($row['ram'] ?? $row['ramGb'] ?? $row['memory_gb'] ?? null);
            $memoryMb = $this->nullableInt($row['memoryLimit'] ?? $row['memory_limit_mb'] ?? null) ?? ($ram === null ? 1024 : (int) round($ram * 1024));
            $diskGb = $this->nullableFloat($row['disk'] ?? $row['diskGb'] ?? null);
            $diskMb = $this->nullableInt($row['diskLimit'] ?? $row['disk_limit_mb'] ?? null) ?? ($diskGb === null ? 10240 : (int) round($diskGb * 1024));
            $identifier = mb_substr((string) ($row['identifier'] ?? ('srv-'.substr(hash('sha256', $id), 0, 20))), 0, 64);
            DB::table('servers')->updateOrInsert(['id' => $id], [
                'identifier' => $identifier,
                'owner_id' => $ownerId,
                'node_id' => $nodeId,
                'name' => mb_substr((string) ($row['name'] ?? 'Imported server'), 0, 128),
                'description' => $row['description'] ?? null,
                'container_id' => $row['containerId'] ?? $row['container_id'] ?? null,
                'image' => $row['image'] ?? 'itzg/minecraft-server:latest',
                'type' => $row['type'] ?? $row['serverType'] ?? null,
                'version' => $row['version'] ?? $row['minecraftVersion'] ?? null,
                'java_version' => $row['javaVersion'] ?? null,
                'status' => $row['status'] ?? 'offline',
                'memory_limit_mb' => max(1, $memoryMb),
                'ram_gb' => $ram,
                'cpu_limit_percent' => max(1, (int) ($row['cpuLimit'] ?? $row['cpu'] ?? 100)),
                'cpu_percent' => $this->nullableFloat($row['cpu'] ?? null),
                'disk_limit_mb' => max(1, $diskMb),
                'disk_gb' => $diskGb,
                'port' => isset($row['port']) ? $this->port($row['port'], 'server port') : null,
                'ip_alias' => $row['ipAlias'] ?? null,
                'theme' => $row['theme'] ?? null,
                'runtime' => $row['runtime'] ?? 'docker',
                'startup_command' => $row['startupCommand'] ?? null,
                'environment_json' => isset($row['environment']) ? json_encode($row['environment'], JSON_THROW_ON_ERROR) : null,
                'suspended' => (bool) ($row['suspend'] ?? $row['suspended'] ?? false),
                'suspended_until' => $this->time($row['suspendedUntil'] ?? null),
                'discord_reward_suspension' => isset($row['discordRewardSuspension']) ? json_encode($row['discordRewardSuspension'], JSON_THROW_ON_ERROR) : null,
                'expires_at' => $this->time($row['expiresAt'] ?? null),
                'root_path' => $row['rootPath'] ?? null,
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function importAllocations(array $rows, array $serverRows): void
    {
        foreach ($rows as $index => $row) {
            $id = (string) ($row['id'] ?? ('legacy-allocation-'.$index));
            $serverId = (string) ($row['assignedServerId'] ?? $row['serverId'] ?? '');
            $start = (int) ($row['portStart'] ?? $row['startPort'] ?? $row['port']);
            $end = (int) ($row['portEnd'] ?? $row['endPort'] ?? $row['port']);
            for ($port = $start; $port <= $end; $port++) {
                $allocationId = $start === $end ? $id : mb_substr($id.'-'.$port, 0, 64);
                $assigned = $serverId;
                if ($assigned === '') {
                    foreach ($serverRows as $server) {
                        if ((string) ($server['nodeId'] ?? '') === (string) $row['nodeId'] && (int) ($server['port'] ?? 0) === $port) {
                            $assigned = (string) $server['id'];
                            break;
                        }
                    }
                }
                $ip = (string) $row['ip'];
                DB::table('allocations')->updateOrInsert(['id' => $allocationId], [
                    'node_id' => (string) $row['nodeId'],
                    'ip' => $ip,
                    'ip_version' => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 6 : (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 4 : null),
                    'port' => $port,
                    'port_start' => $start,
                    'port_end' => $end,
                    'alias' => $row['alias'] ?? null,
                    'assigned_server_id' => $assigned !== '' ? $assigned : null,
                    'is_primary' => (bool) ($row['primary'] ?? $row['isPrimary'] ?? false),
                    'notes' => $row['notes'] ?? null,
                    'updated_at' => now(),
                    'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
                ]);
            }
        }
    }

    private function importServerSubusers(array $serverRows): void
    {
        $allowed = ['start', 'stop', 'restart', 'kill', 'plugins', 'mods', 'backup', 'files', 'properties', 'settings', '*'];
        foreach ($serverRows as $server) {
            foreach (($server['subUsers'] ?? []) as $member) {
                $permissions = array_values(array_intersect((array) ($member['permissions'] ?? []), $allowed));
                DB::table('server_subusers')->updateOrInsert(
                    ['server_id' => (string) $server['id'], 'user_id' => (string) $member['userId']],
                    ['added_by' => null, 'permissions' => json_encode($permissions, JSON_THROW_ON_ERROR), 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    private function importBackups(array $rows, array $servers): void
    {
        foreach ($rows as $index => $row) {
            $serverId = (string) ($row['serverId'] ?? $row['server_id'] ?? '');
            if ($serverId === '') {
                throw new \RuntimeException("backups.json row {$index} has no serverId.");
            }
            $id = (string) ($row['id'] ?? ('legacy-backup-'.substr(hash('sha256', $serverId.'|'.($row['filename'] ?? $index)), 0, 36)));
            DB::table('backups')->updateOrInsert(['id' => $id], [
                'server_id' => $serverId,
                'filename' => mb_substr((string) ($row['filename'] ?? $row['name'] ?? 'legacy-backup'), 0, 255),
                'path' => $row['path'] ?? $row['storagePath'] ?? null,
                'node_backup_id' => $row['nodeBackupId'] ?? null,
                'size_bytes' => max(0, (int) ($row['sizeBytes'] ?? $row['size'] ?? 0)),
                'checksum' => $row['checksum'] ?? null,
                'status' => $row['status'] ?? 'completed',
                'progress' => min(100, max(0, (int) ($row['progress'] ?? 100))),
                'completed_at' => $this->time($row['completedAt'] ?? $row['createdAt'] ?? null),
                'failed_at' => $this->time($row['failedAt'] ?? null),
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function importApiKeys(array $rows): void
    {
        foreach ($rows as $index => $row) {
            $id = $this->requiredId($row['id'] ?? null, "api_keys.json row {$index}");
            $userId = (string) ($row['created_by'] ?? $row['createdBy'] ?? '');
            if ($userId === '') {
                throw new \RuntimeException("API key {$id} has no creator; resolve it before import.");
            }
            $hash = (string) ($row['key_hash'] ?? $row['keyHash'] ?? '');
            if ($hash === '') {
                throw new \RuntimeException("API key {$id} has no stored hash; raw API keys are not imported.");
            }
            DB::table('api_keys')->updateOrInsert(['id' => $id], [
                'user_id' => $userId,
                'label' => mb_substr((string) ($row['label'] ?? 'Imported key'), 0, 100),
                'key_hash' => $hash,
                'scopes' => json_encode($row['scopes'] ?? [], JSON_THROW_ON_ERROR),
                'expires_at' => $this->time($row['expires_at'] ?? $row['expiresAt'] ?? null),
                'last_used_at' => $this->time($row['last_used_at'] ?? $row['lastUsedAt'] ?? null),
                'revoked_at' => (bool) ($row['revoked'] ?? false) ? ($this->time($row['revokedAt'] ?? null) ?? now()) : null,
                'updated_at' => now(),
                'created_at' => $this->time($row['created_at'] ?? $row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function importSftpUsers(array $rows, array $servers): void
    {
        foreach ($rows as $index => $row) {
            $id = $this->requiredId($row['id'] ?? null, "sftp_users.json row {$index}");
            $serverId = (string) ($row['serverId'] ?? $row['server_id'] ?? '');
            $username = trim((string) ($row['username'] ?? ''));
            $hash = $this->normalizeBcryptHash((string) ($row['passwordHash'] ?? $row['password_hash'] ?? ''));
            if ($serverId === '' || $username === '' || $hash === '') {
                throw new \RuntimeException("SFTP record {$id} is missing its server, username, or password hash.");
            }
            if (password_get_info($hash)['algo'] === null) {
                throw new \RuntimeException("SFTP record {$id} contains an unsupported password hash.");
            }
            DB::table('sftp_users')->updateOrInsert(['id' => $id], [
                'server_id' => $serverId,
                'username' => $username,
                'password_hash' => $hash,
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function importSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }
            $sensitive = $this->containsSensitiveField($value);
            $stored = $sensitive
                ? json_encode(Crypt::encryptString(json_encode($value, JSON_THROW_ON_ERROR)), JSON_THROW_ON_ERROR)
                : json_encode($value, JSON_THROW_ON_ERROR);
            DB::table('settings')->updateOrInsert(['key' => mb_substr($key, 0, 128)], [
                'value' => $stored,
                'is_encrypted' => $sensitive,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }

    private function importThemes(array $rows): void
    {
        foreach ($rows as $index => $row) {
            if (! is_array($row) || empty($row['id'])) {
                throw new \RuntimeException("themes.json row {$index} has no theme id.");
            }
            DB::table('themes')->updateOrInsert(['id' => (string) $row['id']], [
                'name' => mb_substr((string) ($row['name'] ?? $row['id']), 0, 128),
                'version' => mb_substr((string) ($row['version'] ?? '1.0.0'), 0, 32),
                'author' => isset($row['author']) ? mb_substr((string) $row['author'], 0, 128) : null,
                'description' => $row['description'] ?? null,
                'enabled' => (bool) ($row['enabled'] ?? true),
                'built_in' => (bool) ($row['builtIn'] ?? $row['built_in'] ?? false),
                'protected' => (bool) ($row['protected'] ?? false),
                'config' => json_encode($row['config'] ?? new \stdClass, JSON_THROW_ON_ERROR),
                'layout' => json_encode($row['layout'] ?? new \stdClass, JSON_THROW_ON_ERROR),
                'legacy_payload' => null,
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function importCloudflareAccounts(array $rows): void
    {
        foreach ($rows as $index => $row) {
            if (! is_array($row) || empty($row['id'])) {
                throw new \RuntimeException("cloudflare_accounts.json row {$index} has no id.");
            }
            $token = (string) ($row['token'] ?? '');
            if ($token === '') {
                throw new \RuntimeException("Cloudflare account {$row['id']} has no encrypted token.");
            }
            $plain = $this->decryptLegacySecret($token);
            DB::table('cloudflare_accounts')->updateOrInsert(['id' => (string) $row['id']], [
                'account_id' => $row['accountId'] ?? null,
                'name' => $row['name'] ?? null,
                'api_token_encrypted' => Crypt::encryptString($plain),
                'legacy_payload_encrypted' => Crypt::encryptString(json_encode($row, JSON_THROW_ON_ERROR)),
                'metadata' => json_encode(array_diff_key($row, array_flip(['token'])), JSON_THROW_ON_ERROR),
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function importDiscordRewards(mixed $state): void
    {
        if ($state === null) {
            return;
        }
        DB::table('discord_reward_state')->updateOrInsert(['id' => 'default'], [
            // The legacy object includes integration credentials and identity
            // material. Preserve it losslessly, encrypted with Laravel APP_KEY.
            'payload' => json_encode(Crypt::encryptString(json_encode($state, JSON_THROW_ON_ERROR)), JSON_THROW_ON_ERROR),
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }

    private function importAuditLogs(array $rows): void
    {
        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                throw new \RuntimeException("audit_logs.json row {$index} must be an object.");
            }
            $actorId = $row['actorId'] ?? $row['userId'] ?? null;
            if ($actorId !== null && ! DB::table('users')->where('id', (string) $actorId)->exists()) {
                $actorId = null;
            }
            DB::table('audit_events')->updateOrInsert(['request_id' => (string) ($row['id'] ?? ('legacy-audit-'.$index))], [
                'user_id' => $actorId,
                'action' => mb_substr((string) ($row['action'] ?? 'legacy.unknown'), 0, 128),
                'resource_type' => $row['resourceType'] ?? null,
                'resource_id' => isset($row['resourceId']) ? (string) $row['resourceId'] : null,
                'result' => (string) ($row['result'] ?? 'success'),
                'request_id' => (string) ($row['id'] ?? ('legacy-audit-'.$index)),
                'ip_address' => $row['ip'] ?? $row['ipAddress'] ?? null,
                'metadata' => json_encode($row['metadata'] ?? [], JSON_THROW_ON_ERROR),
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function importNotifications(array $rows): void
    {
        foreach ($rows as $index => $row) {
            $id = $this->requiredId($row['id'] ?? null, "notifications.json row {$index}");
            $userId = (string) ($row['userId'] ?? $row['user_id'] ?? '');
            if ($userId === '' || ! DB::table('users')->where('id', $userId)->exists()) {
                throw new \RuntimeException("Notification {$id} references a missing user.");
            }
            DB::table('notifications')->updateOrInsert(['id' => $id], [
                'user_id' => $userId,
                'type' => mb_substr((string) ($row['type'] ?? 'legacy'), 0, 64),
                'title' => mb_substr((string) ($row['title'] ?? 'Notification'), 0, 255),
                'message' => (string) ($row['message'] ?? ''),
                'dedupe_key' => $row['dedupeKey'] ?? null,
                'read_at' => $this->time($row['readAt'] ?? null),
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function importServerTransfers(array $rows): void
    {
        foreach ($rows as $index => $row) {
            $id = $this->requiredId($row['id'] ?? null, "server_transfers.json row {$index}");
            DB::table('server_transfers')->updateOrInsert(['id' => $id], [
                'server_id' => (string) ($row['serverId'] ?? $row['server_id'] ?? ''),
                'source_node_id' => (string) ($row['sourceNodeId'] ?? $row['source_node_id'] ?? ''),
                'destination_node_id' => (string) ($row['destinationNodeId'] ?? $row['destination_node_id'] ?? ''),
                'status' => mb_substr((string) ($row['status'] ?? 'unknown'), 0, 32),
                'error' => $row['error'] ?? null,
                'completed_at' => $this->time($row['completedAt'] ?? null),
                'updated_at' => now(),
                'created_at' => $this->time($row['createdAt'] ?? null) ?? now(),
            ]);
        }
    }

    private function archiveSnapshot(array $snapshot): void
    {
        foreach ($snapshot as $filename => $file) {
            $value = $file['value'];
            $rows = $file['is_list'] ? $value : [$value];
            foreach ($rows as $index => $row) {
                $rowKey = $file['is_list'] ? 'row:'.str_pad((string) $index, 8, '0', STR_PAD_LEFT) : 'singleton';
                $encoded = json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                $encrypted = Crypt::encryptString($encoded);
                DB::table('legacy_json_records')->updateOrInsert(
                    ['source_file' => $filename, 'record_key' => $rowKey],
                    [
                        'payload' => json_encode($encrypted, JSON_THROW_ON_ERROR),
                        'sha256' => hash('sha256', $encoded),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    private function createBackup(string $source, array $snapshot): string
    {
        $directory = (string) ($this->option('backup-dir') ?: storage_path('app/private/legacy-import-backups'));
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        if ($directory === '' || is_link($directory)) {
            throw new \RuntimeException('Backup directory must be a real private directory.');
        }
        $sourcePrefix = rtrim($source, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $resolvedDir = realpath($directory);
        if ($resolvedDir && (str_starts_with($resolvedDir.DIRECTORY_SEPARATOR, $sourcePrefix) || $resolvedDir === $source)) {
            throw new \RuntimeException('Backup directory may not be inside the legacy source directory.');
        }
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Unable to create private backup directory.');
        }
        @chmod($directory, 0700);

        $target = $directory.DIRECTORY_SEPARATOR.'snck-data-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(6)).'.zip';
        $zip = new ZipArchive;
        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            throw new \RuntimeException('Unable to open the pre-import ZIP backup.');
        }
        $manifest = ['created_at' => now()->toIso8601String(), 'source_label' => basename($source), 'json_files' => []];
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $item) {
                $path = $item->getPathname();
                if ($item->isLink()) {
                    throw new \RuntimeException('The legacy data backup contains a symbolic link; resolve it before importing.');
                }
                $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($source) + 1));
                if ($item->isDir()) {
                    $zip->addEmptyDir($relative);
                } elseif ($item->isFile()) {
                    if (! $zip->addFile($path, $relative)) {
                        throw new \RuntimeException('Unable to add a source file to the pre-import backup.');
                    }
                }
            }
            foreach ($snapshot as $filename => $file) {
                $manifest['json_files'][$filename] = $file['sha256'];
            }
            $zip->addFromString('__phase2_backup_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        } catch (Throwable $exception) {
            $zip->close();
            @unlink($target);
            throw $exception;
        }
        if (! $zip->close()) {
            @unlink($target);
            throw new \RuntimeException('Unable to finalize the pre-import ZIP backup.');
        }
        chmod($target, 0600);

        return $target;
    }

    private function decryptLegacySecret(string $stored): string
    {
        // Current root records use Laravel-incompatible AES-256-GCM data:
        // base64url(iv).base64url(tag).base64url(ciphertext), keyed from
        // NODE_ENCRYPTION_KEY with SHA-256. Plain pre-encryption secrets are
        // accepted only as source values and immediately re-encrypted by Laravel.
        $parts = explode('.', $stored);
        if (count($parts) !== 3) {
            return $stored;
        }
        $legacyKey = (string) config('snck.legacy_node_encryption_key');
        if ($legacyKey === '') {
            throw new \RuntimeException('Set LEGACY_NODE_ENCRYPTION_KEY to the old panel NODE_ENCRYPTION_KEY for encrypted node/Cloudflare credentials, then remove it after import.');
        }
        [$ivPart, $tagPart, $cipherPart] = $parts;
        $decode = static function (string $value): string|false {
            $standard = strtr($value, '-_', '+/');
            $standard .= str_repeat('=', (4 - strlen($standard) % 4) % 4);

            return base64_decode($standard, true);
        };
        $iv = $decode($ivPart);
        $tag = $decode($tagPart);
        $cipher = $decode($cipherPart);
        if ($iv === false || $tag === false || $cipher === false || strlen($iv) !== 12 || strlen($tag) !== 16) {
            throw new \RuntimeException('Legacy encrypted credential has an invalid AES-256-GCM envelope.');
        }
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', hash('sha256', $legacyKey, true), OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new \RuntimeException('Legacy credential decryption failed; verify LEGACY_NODE_ENCRYPTION_KEY.');
        }

        return $plain;
    }

    private function containsSensitiveField(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }
        foreach ($value as $key => $nested) {
            if (is_string($key) && preg_match('/token|secret|password|credential|private.?key/i', $key)) {
                return true;
            }
            if ($this->containsSensitiveField($nested)) {
                return true;
            }
        }

        return false;
    }

    private function requiredId(mixed $value, string $label): string
    {
        $id = trim((string) $value);
        if ($id === '' || mb_strlen($id) > 64) {
            throw new \RuntimeException($label.' has a missing or invalid stable ID (maximum 64 characters).');
        }

        return $id;
    }

    private function port(mixed $value, string $label): int
    {
        $port = filter_var($value, FILTER_VALIDATE_INT);
        if ($port === false || $port < 1 || $port > 65535) {
            throw new \RuntimeException('Invalid '.$label.'.');
        }

        return $port;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value) || ! is_finite((float) $value)) {
            throw new \RuntimeException('A numeric legacy resource value is invalid.');
        }

        return (int) $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value) || ! is_finite((float) $value)) {
            throw new \RuntimeException('A numeric legacy resource value is invalid.');
        }

        return (float) $value;
    }

    private function time(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }
        try {
            return Carbon::parse($value)->utc()->format('Y-m-d H:i:s');
        } catch (Throwable) {
            throw new \RuntimeException('Invalid timestamp in legacy JSON: '.$value);
        }
    }

    private function expandedAllocationCount(array $rows): int
    {
        return array_sum(array_map(fn (array $row) => (int) ($row['portEnd'] ?? $row['endPort'] ?? $row['port'] ?? 0) - (int) ($row['portStart'] ?? $row['startPort'] ?? $row['port'] ?? 0) + 1, $rows));
    }

    private function snapshotHash(array $snapshot): string
    {
        $pieces = [];
        foreach ($snapshot as $filename => $file) {
            $pieces[] = $filename.'='.$file['sha256'];
        }

        return hash('sha256', implode("\n", $pieces));
    }

    private function countSnapshot(array $snapshot): array
    {
        $counts = [];
        foreach ($snapshot as $filename => $file) {
            $counts[$filename] = $file['is_list'] ? count($file['value']) : 1;
        }
        $counts['legacy_json_records'] = array_sum($counts);
        $counts['allocations_expanded'] = $this->expandedAllocationCount($snapshot['allocations.json']['value'] ?? []);

        return $counts;
    }

    private function showCounts(): void
    {
        if (! $this->counts) {
            return;
        }
        $this->table(['Collection', 'Rows/files'], array_map(fn ($key, $value) => [$key, $value], array_keys($this->counts), array_values($this->counts)));
    }
}
