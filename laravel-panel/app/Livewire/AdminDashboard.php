<?php

namespace App\Livewire;

use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AdminDashboard extends Component
{
    public function mount(): void
    {
        $this->authorizeStaff();
    }

    public function render(): View
    {
        $this->authorizeStaff();

        return view('livewire.admin.dashboard', [
            'userCount' => User::query()->count(),
            'serverCount' => Server::query()->count(),
            'nodeCount' => Node::query()->count(),
            'onlineNodeCount' => Node::query()->where('status', 'online')->count(),
        ])->layout('layouts.app', ['title' => 'Administration']);
    }

    private function authorizeStaff(): void
    {
        abort_unless(auth()->user() instanceof User && auth()->user()->isStaff(), 403, 'Owner or Admin access is required.');
    }
}
