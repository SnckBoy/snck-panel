<?php

namespace App\Console\Commands;

use App\Services\DiscordRewardsService;
use Illuminate\Console\Command;
use Throwable;

class DiscordMaintenance extends Command
{
    protected $signature = 'discord:maintenance {--reconcile : Reconcile linked accounts against the Discord guild} {--cleanup : Remove expired retained claims}';

    protected $description = 'Run Discord member reconciliation and retention maintenance';

    public function handle(DiscordRewardsService $service): int
    {
        try {
            if ($this->option('reconcile')) {
                $this->info('Reconciled '.$service->reconcileMembers().' Discord members.');
            }
            if ($this->option('cleanup')) {
                $this->info('Removed '.$service->cleanupRetention().' expired claims.');
            }
            if (! $this->option('reconcile') && ! $this->option('cleanup')) {
                $this->error('Select --reconcile or --cleanup.');
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
