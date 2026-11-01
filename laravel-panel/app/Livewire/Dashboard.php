<?php

namespace App\Livewire;

use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use App\Services\ThemeCatalog;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(ThemeCatalog $themes): View
    {
        /** @var User $user */
        $user = auth()->user();
        $servers = Server::query()->visibleTo($user);
        $serverCount = (clone $servers)->count();
        $onlineCount = (clone $servers)->where('status', 'online')->count();
        $nodeCount = $user->isStaff() ? Node::query()->count() : null;
        $serverPreview = (clone $servers)->with('node:id,name')->orderByDesc('created_at')->limit(5)->get();
        $themeLayout = $themes->activeTheme()['layout'];

        return view('livewire.dashboard', compact('serverCount', 'onlineCount', 'nodeCount', 'serverPreview', 'themeLayout'))
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
