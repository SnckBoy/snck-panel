<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#05070b">
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
        $snckNavigation[] = ['label' => 'Administration', 'route' => 'admin.index', 'icon' => 'settings'];
        $snckNavigation[] = ['label' => 'Nodes', 'route' => 'nodes.index', 'icon' => 'nodes'];
        $snckNavigation[] = ['label' => 'Snck Theme', 'route' => 'themes.index', 'icon' => 'theme'];
        $snckNavigation[] = ['label' => 'Audit logs', 'route' => 'admin.audit-logs', 'icon' => 'audit'];
    }
@endphp
<a href="#snck-content" class="snck-skip-link">Skip to content</a>
<div class="snck-shell">
    <header class="snck-mobile-header">
        <a href="{{ route('dashboard') }}" class="snck-brand">
            <span class="snck-mark snck-mark-sm" aria-hidden="true"><svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" fill="currentColor" d="M50 97 C42 73 29 55 9 41 C21 30 34 28 45 38 L50 25 L55 38 C66 28 79 30 91 41 C71 55 58 73 50 97 Z M50 45 L60 57.5 L50 70 L40 57.5 Z"/></svg></span>
            SNCK <span>PANEL</span>
        </a>
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
        <a href="{{ route('dashboard') }}" class="snck-brand">
            <span class="snck-mark snck-mark-sm" aria-hidden="true"><svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" fill="currentColor" d="M50 97 C42 73 29 55 9 41 C21 30 34 28 45 38 L50 25 L55 38 C66 28 79 30 91 41 C71 55 58 73 50 97 Z M50 45 L60 57.5 L50 70 L40 57.5 Z"/></svg></span>
            SNCK <span>PANEL</span>
        </a>
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

    <main class="snck-main" id="snck-content">
        <div class="snck-main-inner">{{ $slot }}</div>
    </main>
</div>
@livewireScripts
</body>
</html>
