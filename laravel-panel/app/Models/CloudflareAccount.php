<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CloudflareAccount extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'account_id', 'name', 'api_token_encrypted', 'metadata'];
    protected $hidden = ['api_token_encrypted', 'legacy_payload_encrypted'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(fn (CloudflareAccount $account) => $account->id ??= (string) Str::uuid());
    }
}
