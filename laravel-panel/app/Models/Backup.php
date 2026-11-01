<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id', 'server_id', 'filename', 'path', 'node_backup_id', 'size_bytes',
        'checksum', 'status', 'progress', 'completed_at', 'failed_at', 'legacy_payload',
    ];

    protected $hidden = ['path', 'legacy_payload'];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'progress' => 'integer',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'legacy_payload' => 'array',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id', 'id');
    }
}
