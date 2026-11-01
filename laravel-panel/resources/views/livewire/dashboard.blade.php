<section class="space-y-7">
    @php
        $sections = [
            'welcome' => 'Welcome / fleet hero',
            'quick-actions' => 'Quick actions',
            'metrics' => 'Metric cards',
            'system-health' => 'System health',
            'server-inventory' => 'Server inventory',
        ];
        $dashboardOrder = $themeLayout['dashboardOrder'] ?? array_keys($sections);
        $hiddenDashboard = $themeLayout['hiddenDashboard'] ?? [];
        $metricOrder = $themeLayout['metricsOrder'] ?? ['cpu','ram','net','nodes'];
        $hiddenMetrics = $themeLayout['hiddenMetrics'] ?? [];
        $metricValues = ['cpu' => 'Not reported', 'ram' => 'Not reported', 'net' => (string) $onlineCount, 'nodes' => 'Not reported'];
        $metricLabels = ['cpu' => 'Panel CPU', 'ram' => 'Host memory', 'net' => 'Servers online', 'nodes' => 'Active containers'];
    @endphp
    @foreach ($dashboardOrder as $section)
        @continue(in_array($section, $hiddenDashboard, true))
        @if ($section === 'welcome')
            <header class="rounded-2xl border border-slate-800 p-6 sm:p-8" style="background:linear-gradient(130deg,color-mix(in srgb,var(--snck-primary) 19%,var(--snck-surface)),var(--snck-surface));border-radius:var(--snck-radius)"><p class="text-xs font-semibold uppercase tracking-[.2em]" style="color:var(--snck-primary)">Operations overview</p><h1 class="mt-3 text-3xl font-bold">Welcome, {{ auth()->user()->username ?? auth()->user()->name }}</h1><p class="mt-2 text-sm" style="color:var(--snck-muted)">Manage your Minecraft server fleet and inspect only verified node and server state.</p></header>
        @elseif ($section === 'quick-actions')
            <section class="flex flex-wrap gap-3"><a href="{{ route('servers.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-white" style="background:var(--snck-primary)">View servers</a>@if (auth()->user()->isStaff())<a href="{{ route('nodes.index') }}" class="rounded-xl border px-4 py-3 text-sm" style="border-color:var(--snck-border)">Manage nodes</a><a href="{{ route('allocations.index') }}" class="rounded-xl border px-4 py-3 text-sm" style="border-color:var(--snck-border)">Allocations</a>@endif</section>
        @elseif ($section === 'metrics')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach ($metricOrder as $metric)@continue(in_array($metric,$hiddenMetrics,true))<article class="rounded-2xl border p-5" style="background:var(--snck-surface);border-color:var(--snck-border);border-radius:var(--snck-radius)"><p class="text-sm" style="color:var(--snck-muted)">{{ $metricLabels[$metric] ?? $metric }}</p><p class="mt-3 text-3xl font-semibold">{{ $metricValues[$metric] ?? 'Not reported' }}</p></article>@endforeach</div>
        @elseif ($section === 'system-health')
            <article class="rounded-2xl border p-5" style="background:var(--snck-surface);border-color:var(--snck-border);border-radius:var(--snck-radius)"><h2 class="font-semibold">System health</h2><p class="mt-2 text-sm" style="color:var(--snck-muted)">Server counts come from PostgreSQL. Node health is based on authenticated probes and heartbeats; host CPU and memory telemetry are not available in this release.</p>@if (auth()->user()->isStaff())<a href="{{ route('nodes.index') }}" class="mt-3 inline-block text-sm" style="color:var(--snck-primary)">Inspect node health →</a>@endif</article>
        @elseif ($section === 'server-inventory')
            <section class="space-y-3"><div class="flex items-end justify-between"><div><h2 class="text-xl font-semibold">Your accessible servers</h2><p class="mt-1 text-sm" style="color:var(--snck-muted)">Only owned or explicitly shared servers appear here.</p></div><a href="{{ route('servers.index') }}" class="text-sm" style="color:var(--snck-primary)">All servers →</a></div><div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">@forelse ($serverPreview as $server)<a href="{{ route('servers.show',$server->id) }}" class="rounded-xl border p-4 hover:bg-white/5" style="background:color-mix(in srgb,var(--snck-surface) calc(var(--snck-opacity)*100%),transparent);border-color:var(--snck-border);border-radius:var(--snck-radius)"><div class="flex justify-between gap-3"><strong>{{ $server->name }}</strong><span class="text-xs" style="color:var(--snck-primary)">{{ $server->status }}</span></div><p class="mt-2 text-xs" style="color:var(--snck-muted)">{{ $server->node?->name ?? 'Unknown node' }} · {{ $server->version ?: 'Version not set' }}</p></a>@empty<p class="rounded-xl border border-slate-800 p-5 text-sm text-slate-500">No accessible servers yet.</p>@endforelse</div></section>
        @endif
    @endforeach
</section>
