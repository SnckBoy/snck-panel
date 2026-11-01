<?php

namespace App\Livewire;

use App\Services\DiscordRewardsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DiscordClaimsAdmin extends Component
{
    public array $claims = [];

    public function mount(DiscordRewardsService $service): void
    {
        abort_unless(auth()->user()?->isStaff(), 403);
        $this->load($service);
    }

    public function setStatus(string $id, string $status, DiscordRewardsService $service): void
    {
        abort_unless(in_array($status, ['approved', 'rejected', 'reconciled'], true), 422);
        $state = $service->state();
        foreach ($state['claims'] as &$claim) {
            if (($claim['id'] ?? null) === $id) {
                $claim['status'] = $status;
                $claim['updatedAt'] = now()->toISOString();
                DB::table('audit_events')->insert(['user_id' => auth()->id(), 'action' => 'discord.claim_'.$status, 'resource_type' => 'discord_claim', 'result' => 'success', 'metadata' => json_encode(['claimId' => $id]), 'created_at' => now(), 'updated_at' => now()]);
            }
        } unset($claim);
        $service->save($state);
        $this->load($service);
    }

    private function load(DiscordRewardsService $service): void
    {
        $this->claims = $service->state()['claims'];
    }

    public function render(): View
    {
        return view('livewire.discord.claims-admin')->layout('layouts.app');
    }
}
