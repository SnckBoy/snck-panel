<?php

namespace App\Console\Commands;

use App\Models\Node;
use Illuminate\Console\Command;

class MarkStaleNodesOffline extends Command
{
    protected $signature = 'snck:nodes-mark-offline';

    protected $description = 'Mark nodes offline when their last heartbeat is older than 45 seconds';

    public function handle(): int
    {
        $cutoff = now()->subSeconds(45);

        $affected = Node::query()
            ->where('status', '!=', 'offline')
            ->where(function ($query) use ($cutoff): void {
                $query->whereNull('last_heartbeat_at')
                    ->orWhere('last_heartbeat_at', '<', $cutoff);
            })
            ->update([
                'status' => 'offline',
                'docker_available' => false,
                'updated_at' => now(),
            ]);

        $this->info("Marked {$affected} stale node(s) offline.");

        return self::SUCCESS;
    }
}
