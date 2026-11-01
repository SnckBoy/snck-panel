<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Snck Panel' }} · {{ config('app.name', 'Snck Panel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
<div class="min-h-screen lg:flex">
    <aside class="border-b border-slate-800 bg-slate-900/80 p-5 lg:min-h-screen lg:w-64 lg:border-b-0 lg:border-r">
        <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-wide text-violet-300">SNCK PANEL</a>
        <p class="mt-1 text-xs text-slate-500">{{ auth()->user()->username ?? auth()->user()->name }} · {{ auth()->user()->role }}</p>
        <nav class="mt-7 flex gap-3 text-sm lg:flex-col">
            <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 hover:bg-slate-800">Dashboard</a>
            <a href="{{ route('servers.index') }}" class="rounded-lg px-3 py-2 hover:bg-slate-800">Servers</a>
            <a href="{{ route('servers.create') }}" class="rounded-lg px-3 py-2 hover:bg-slate-800">Create server</a>
            @if (auth()->user()->isStaff())
                <a href="{{ route('nodes.index') }}" class="rounded-lg px-3 py-2 hover:bg-slate-800">Nodes</a>
                <a href="{{ route('themes.index') }}" class="rounded-lg px-3 py-2 hover:bg-slate-800">Snck Theme</a>
            @endif
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="mt-7">
            @csrf
            <button type="submit" class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800">Sign out</button>
        </form>
    </aside>
    <main class="min-w-0 flex-1 p-5 sm:p-8">
        {{ $slot }}
    </main>
</div>
@livewireScripts
</body>
</html>
