<?php

namespace App\Livewire;

use App\Services\DiscordRewardsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;

class DiscordRewardsPage extends Component
{
    public array $state = [];

    public string $message = '';

    public string $error = '';

    public function mount(DiscordRewardsService $service): void
    {
        $this->loadState($service);
    }

    public function refreshState(DiscordRewardsService $service): void
    {
        $this->loadState($service);
    }

    public function claim(string $planId, DiscordRewardsService $service): void
    {
        try {
            $service->claim(auth()->user(), $planId);
            $this->loadState($service);
            $this->message = 'Claim submitted for review.';
            $this->error = '';
        } catch (Throwable $exception) {
            $this->error = $exception->getMessage();
            $this->message = '';
        }
    }

    public function unlink(DiscordRewardsService $service): void
    {
        $service->unlink(auth()->user());
        $this->loadState($service);
        $this->message = 'Discord account unlinked.';
    }

    private function loadState(DiscordRewardsService $service): void
    {
        $this->state = $service->publicUserState(auth()->user());
    }

    public function render(): View
    {
        return view('livewire.discord.rewards')->layout('layouts.app');
    }
}
