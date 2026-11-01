<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allocation extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id', 'node_id', 'ip', 'ip_version', 'port', 'port_start', 'port_end',
        'alias', 'assigned_server_id', 'is_primary', 'notes', 'legacy_payload',
    ];

    protected $hidden = ['legacy_payload'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'legacy_payload' => 'array'];
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'node_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'assigned_server_id', 'id');
    }
}
