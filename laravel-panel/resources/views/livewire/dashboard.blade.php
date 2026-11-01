<section class="space-y-6">
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
            <header class="rounded-2xl border border-slate-800 p-5 sm:p-7" style="background:radial-gradient(circle at 88% 12%,color-mix(in srgb,var(--snck-primary) 18%,transparent),transparent 34%),linear-gradient(130deg,color-mix(in srgb,var(--snck-primary) 12%,var(--snck-surface)),var(--snck-surface));border-color:var(--snck-border);border-radius:var(--snck-radius)">
                <p class="text-xs font-semibold uppercase tracking-[.18em]" style="color:var(--snck-primary)">Operations overview</p>
                <h1 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Welcome, {{ auth()->user()->username ?? auth()->user()->name }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6" style="color:var(--snck-muted)">Manage your Minecraft server fleet and inspect verified node and server state.</p>
            </header>
        @elseif ($section === 'quick-actions')
            <section class="flex flex-wrap gap-2.5">
                <a href="{{ route('servers.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition hover:brightness-110" style="background:var(--snck-primary)">View servers</a>
                @if (auth()->user()->isStaff())
                    <a href="{{ route('nodes.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition hover:bg-white/5" style="border-color:var(--snck-border)">Manage nodes</a>
                    <a href="{{ route('allocations.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition hover:bg-white/5" style="border-color:var(--snck-border)">Allocations</a>
                @endif
            </section>
        @elseif ($section === 'metrics')
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($metricOrder as $metric)
                    @continue(in_array($metric,$hiddenMetrics,true))
                    <article class="min-w-0 rounded-2xl border p-4 sm:p-5" style="background:var(--snck-surface);border-color:var(--snck-border);border-radius:var(--snck-radius)">
                        <p class="text-sm" style="color:var(--snck-muted)">{{ $metricLabels[$metric] ?? $metric }}</p>
                        <p class="mt-3 break-words text-2xl font-semibold tracking-tight sm:text-3xl">{{ $metricValues[$metric] ?? 'Not reported' }}</p>
                    </article>
                @endforeach
            </div>
        @elseif ($section === 'system-health')
            <article class="rounded-2xl border p-4 sm:p-5" style="background:var(--snck-surface);border-color:var(--snck-border);border-radius:var(--snck-radius)">
                <h2 class="font-semibold">System health</h2>
                <p class="mt-2 text-sm leading-6" style="color:var(--snck-muted)">Server counts come from PostgreSQL. Node health is based on authenticated probes and heartbeats; host CPU and memory telemetry are not available in this release.</p>
                @if (auth()->user()->isStaff())
                    <a href="{{ route('nodes.index') }}" class="mt-3 inline-flex min-h-10 items-center text-sm font-semibold" style="color:var(--snck-primary)">Inspect node health →</a>
                @endif
            </article>
        @elseif ($section === 'server-inventory')
            <section class="space-y-3">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <div><h2 class="text-lg font-semibold sm:text-xl">Your accessible servers</h2><p class="mt-1 text-sm" style="color:var(--snck-muted)">Only owned or explicitly shared servers appear here.</p></div>
                    <a href="{{ route('servers.index') }}" class="inline-flex min-h-10 items-center text-sm font-semibold" style="color:var(--snck-primary)">All servers →</a>
                </div>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($serverPreview as $server)
                        <a href="{{ route('servers.show',$server->id) }}" class="min-w-0 rounded-xl border p-4 transition hover:bg-white/5" style="background:color-mix(in srgb,var(--snck-surface) calc(var(--snck-opacity)*100%),transparent);border-color:var(--snck-border);border-radius:var(--snck-radius)">
                            <div class="flex flex-wrap justify-between gap-2"><strong class="break-words">{{ $server->name }}</strong><span class="text-xs" style="color:var(--snck-primary)">{{ $server->status }}</span></div>
                            <p class="mt-2 break-words text-xs" style="color:var(--snck-muted)">{{ $server->node?->name ?? 'Unknown node' }} · {{ $server->version ?: 'Version not set' }}</p>
                        </a>
                    @empty
                        <p class="rounded-xl border p-5 text-sm" style="border-color:var(--snck-border);color:var(--snck-muted)">No accessible servers yet.</p>
                    @endforelse
                </div>
            </section>
        @endif
    @endforeach
</section>
