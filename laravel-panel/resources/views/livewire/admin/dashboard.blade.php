<section class="space-y-6">
    <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[.2em]" style="color:var(--snck-primary)">Owner / Admin control center</p>
        <h1 class="text-3xl font-bold tracking-tight">Administration</h1>
        <p class="max-w-3xl text-sm leading-6" style="color:var(--snck-muted)">Manage the panel, infrastructure, users, integrations and appearance from one place.</p>
    </header>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ([['Users', $userCount], ['Servers', $serverCount], ['Nodes', $nodeCount], ['Online nodes', $onlineNodeCount]] as [$label, $value])
            <article class="min-w-0 rounded-2xl border p-4 sm:p-5" style="background:var(--snck-surface);border-color:var(--snck-border);border-radius:var(--snck-radius)">
                <p class="text-sm" style="color:var(--snck-muted)">{{ $label }}</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums">{{ number_format($value) }}</p>
            </article>
        @endforeach
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['Panel settings', 'Branding, registration, platform defaults and system configuration.', 'admin.workspace', 'Configure panel'],
            ['Users & permissions', 'Manage user accounts, roles, access and account status.', 'admin.users', 'Manage users'],
            ['Nodes & allocations', 'Register nodes, view live health, manage maintenance and allocate ports.', 'nodes.index', 'Manage infrastructure'],
            ['Themes & appearance', 'Manage panel themes and visual configuration.', 'themes.index', 'Manage themes'],
            ['Cloudflare', 'Manage connected Cloudflare account and available zones.', 'cloudflare.index', 'Open Cloudflare'],
            ['Discord claims', 'Review and manage Discord reward claims.', 'admin.discord.claims', 'Manage claims'],
        ] as [$title, $description, $routeName, $action])
            <a href="{{ route($routeName) }}" class="group flex min-h-44 flex-col rounded-2xl border p-5 transition hover:-translate-y-0.5 hover:bg-white/[.03]" style="background:var(--snck-surface);border-color:var(--snck-border);border-radius:var(--snck-radius)">
                <h2 class="font-semibold">{{ $title }}</h2>
                <p class="mt-2 flex-1 text-sm leading-6" style="color:var(--snck-muted)">{{ $description }}</p>
                <span class="mt-4 text-sm font-semibold" style="color:var(--snck-primary)">{{ $action }} <span aria-hidden="true">→</span></span>
            </a>
        @endforeach
    </div>
</section>
