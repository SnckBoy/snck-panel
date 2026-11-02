<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#080d1b">
    <title>{{ $title ?? 'Snck Panel' }} · {{ config('app.name', 'Snck Panel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="snck-app min-h-screen bg-slate-950 text-slate-100 antialiased">
@php
    $snckNavigation = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
        ['label' => 'Servers', 'route' => 'servers.index', 'icon' => 'servers'],
        ['label' => 'Create server', 'route' => 'servers.create', 'icon' => 'plus'],
    ];
    if (auth()->user()->isStaff()) {
        $snckNavigation[] = ['label' => 'Nodes', 'route' => 'nodes.index', 'icon' => 'nodes'];
        $snckNavigation[] = ['label' => 'Snck Theme', 'route' => 'themes.index', 'icon' => 'theme'];
        $snckNavigation[] = ['label' => 'Admin', 'route' => 'admin.index', 'icon' => 'settings'];
    }
@endphp
<div class="snck-shell">
    <header class="snck-mobile-header">
        <a href="{{ route('dashboard') }}" class="snck-brand">SNCK <span>PANEL</span></a>
        <details class="snck-mobile-menu">
            <summary aria-label="Open navigation menu"><span></span><span></span><span></span></summary>
            <nav class="snck-mobile-nav" aria-label="Main navigation">
                @foreach ($snckNavigation as $item)
                    <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['route'])) aria-current="page" @endif>
                        <span class="snck-nav-icon" aria-hidden="true">@include('components.nav-icon', ['name' => $item['icon']])</span>{{ $item['label'] }}
                    </a>
                @endforeach
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="snck-signout"><span class="snck-nav-icon" aria-hidden="true">@include('components.nav-icon', ['name' => 'signout'])</span>Sign out</button>
                </form>
            </nav>
        </details>
    </header>

    <aside class="snck-sidebar">
        <a href="{{ route('dashboard') }}" class="snck-brand">SNCK <span>PANEL</span></a>
        <div class="snck-user">
            <span class="snck-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->username ?? auth()->user()->name, 0, 1)) }}</span>
            <span><strong>{{ auth()->user()->username ?? auth()->user()->name }}</strong><small>{{ auth()->user()->role }}</small></span>
        </div>
        <p class="snck-nav-label">WORKSPACE</p>
        <nav class="snck-nav" aria-label="Main navigation">
            @foreach ($snckNavigation as $item)
                <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['route'])) class="is-active" aria-current="page" @endif>
                    <span class="snck-nav-icon" aria-hidden="true">@include('components.nav-icon', ['name' => $item['icon']])</span>{{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="snck-sidebar-bottom">
            <div class="snck-sidebar-status"><span></span>Panel workspace</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="snck-signout"><span class="snck-nav-icon" aria-hidden="true">@include('components.nav-icon', ['name' => 'signout'])</span>Sign out</button>
            </form>
        </div>
    </aside>

    <main class="snck-main">
        <div class="snck-main-inner">{{ $slot }}</div>
    </main>
</div>
@livewireScripts
</body>
</html>
