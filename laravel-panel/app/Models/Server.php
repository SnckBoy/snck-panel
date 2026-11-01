<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id', 'identifier', 'owner_id', 'node_id', 'name', 'description',
        'container_id', 'image', 'type', 'version', 'java_version', 'status',
        'memory_limit_mb', 'ram_gb', 'cpu_limit_percent', 'cpu_percent',
        'disk_limit_mb', 'disk_gb', 'port', 'ip_alias', 'theme', 'runtime',
        'startup_command', 'environment_json', 'suspended', 'suspended_until',
        'discord_reward_suspension', 'expires_at', 'root_path', 'legacy_payload',
    ];

    protected $hidden = [
        'environment_json', 'startup_command', 'root_path',
        'discord_reward_suspension', 'legacy_payload',
    ];

    protected function casts(): array
    {
        return [
            'environment_json' => 'array',
            'discord_reward_suspension' => 'array',
            'legacy_payload' => 'array',
            'suspended' => 'boolean',
            'suspended_until' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isStaff()) return $query;

        return $query->where(function (Builder $visible) use ($user): void {
            $visible->where('owner_id', $user->id)
                ->orWhereHas('subusers', fn (Builder $members) => $members->whereKey($user->id));
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'node_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class, 'assigned_server_id', 'id');
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class, 'server_id', 'id');
    }

    public function subusers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'server_subusers', 'server_id', 'user_id')
            ->withPivot(['permissions', 'added_by'])
            ->withTimestamps();
    }
}
