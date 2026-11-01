<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'name', 'version', 'author', 'description', 'enabled', 'built_in', 'protected', 'config', 'layout', 'legacy_payload',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'built_in' => 'boolean',
            'protected' => 'boolean',
            'config' => 'array',
            'layout' => 'array',
            'legacy_payload' => 'array',
        ];
    }
}
