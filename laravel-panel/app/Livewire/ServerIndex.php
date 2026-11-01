<?php

namespace App\Livewire;

use App\Models\Server;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ServerIndex extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $servers = Server::query()
            ->visibleTo($user)
            ->with('node:id,name,fqdn')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.servers.index', compact('servers'))
            ->layout('layouts.app', ['title' => 'Servers']);
    }
}
