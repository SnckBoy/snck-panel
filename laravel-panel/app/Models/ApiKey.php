<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'user_id', 'label', 'key_hash', 'scopes', 'expires_at', 'last_used_at', 'revoked_at', 'legacy_payload'];
    protected $hidden = ['key_hash', 'legacy_payload'];
    protected function casts(): array
    {
        return ['scopes' => 'array', 'expires_at' => 'datetime', 'last_used_at' => 'datetime', 'revoked_at' => 'datetime', 'legacy_payload' => 'array'];
    }
    protected static function booted(): void
    {
        static::creating(fn (ApiKey $key) => $key->id ??= (string) Str::uuid());
    }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
