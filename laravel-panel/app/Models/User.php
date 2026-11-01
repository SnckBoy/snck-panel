<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'username', 'email', 'role', 'disabled', 'google_id',
        'password', 'password_version', 'email_verified_at', 'legacy_payload',
    ];

    protected $hidden = [
        'password', 'remember_token', 'google_id', 'legacy_payload',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_version' => 'integer',
            'disabled' => 'boolean',
            'legacy_payload' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->id ??= (string) Str::uuid();
            if ($user->username !== null) {
                $user->username = mb_strtolower(trim($user->username));
            }
            if ($user->email !== null) {
                $user->email = mb_strtolower(trim($user->email));
            }
        });
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->isOwner() || $this->isAdmin();
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class, 'owner_id');
    }

    public function sharedServers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'server_subusers', 'user_id', 'server_id')
            ->withPivot(['permissions', 'added_by'])
            ->withTimestamps();
    }
}
