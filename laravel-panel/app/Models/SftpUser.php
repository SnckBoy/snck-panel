<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SftpUser extends Model
{
    protected $table = 'sftp_users';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['id', 'server_id', 'username', 'password_hash', 'legacy_payload'];

    protected $hidden = ['password_hash', 'legacy_payload'];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
