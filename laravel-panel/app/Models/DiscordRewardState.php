<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordRewardState extends Model
{
    protected $table = 'discord_reward_state';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'payload'];
    protected function casts(): array { return ['payload' => 'array']; }
}
