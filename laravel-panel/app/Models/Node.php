<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id', 'name', 'description', 'host', 'fqdn', 'hostname', 'public_ip', 'internal_ip',
        'location', 'visibility', 'is_local', 'public_port', 'daemon_port', 'api_port', 'sftp_port',
        'behind_proxy', 'custom_ca_pem', 'access_client_id', 'access_client_secret_encrypted',
        'credential_encrypted', 'credential_hash', 'setup_token_hash', 'setup_token_expires_at',
        'setup_token_used', 'daemon_version', 'status', 'last_heartbeat_at', 'stats',
        'last_health_check', 'docker_available', 'disabled', 'maintenance', 'free_service_locked',
        'memory_limit_mb', 'disk_limit_mb', 'cpu_limit_percent', 'memory_overallocate',
        'disk_overallocate', 'server_directory', 'docker_host', 'cloudflare_zone_id',
        'cloudflare_record_id', 'legacy_payload',
    ];

    protected $hidden = [
        'access_client_secret_encrypted', 'credential_encrypted', 'credential_hash',
        'setup_token_hash', 'custom_ca_pem', 'legacy_payload',
    ];

    protected function casts(): array
    {
        return [
            'is_local' => 'boolean',
            'behind_proxy' => 'boolean',
            'docker_available' => 'boolean',
            'disabled' => 'boolean',
            'maintenance' => 'boolean',
            'free_service_locked' => 'boolean',
            'setup_token_used' => 'boolean',
            'setup_token_expires_at' => 'datetime',
            'last_heartbeat_at' => 'datetime',
            'stats' => 'array',
            'last_health_check' => 'array',
            'legacy_payload' => 'array',
        ];
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class, 'node_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class, 'node_id');
    }
}
