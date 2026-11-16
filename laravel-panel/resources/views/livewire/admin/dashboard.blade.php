<section class="space-y-6">
    <header class="space-y-2">
        <p class="text-xs font-semibold uppercase tracking-[.2em]" style="color:var(--snck-primary)">Owner / Admin control center</p>
        <h1 class="text-3xl font-bold tracking-tight">Administration</h1>
        <p class="max-w-3xl text-sm leading-6" style="color:var(--snck-muted)">Manage panel settings, users, infrastructure, integrations and appearance from one place.</p>
    </header>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ([['Users', $userCount], ['Servers', $serverCount], ['Nodes', $nodeCount], ['Online nodes', $onlineNodeCount]] as [$label, $value])
            <article class="min-w-0 rounded-2xl border p-4 backdrop-blur-xl sm:p-5" style="background:var(--snck-surface);border-color:var(--snck-border);border-radius:var(--snck-radius)">
                <p class="text-sm" style="color:var(--snck-muted)">{{ $label }}</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums">{{ number_format($value) }}</p>
            </article>
        @endforeach
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['Panel settings', 'Branding, registration, user quotas, API keys and integrations.', 'admin.workspace', 'Configure panel'],
            ['Users & permissions', 'Manage user accounts, roles, access and account status.', 'admin.users', 'Manage users'],
            ['Nodes & allocations', 'Register nodes, check health, manage maintenance and allocate ports.', 'nodes.index', 'Manage infrastructure'],
            ['Themes & appearance', 'Manage panel themes and visual configuration.', 'themes.index', 'Manage themes'],
            ['Cloudflare', 'Manage the connected Cloudflare account and available DNS zones.', 'cloudflare.index', 'Open Cloudflare'],
            ['Discord claims', 'Review and manage Discord reward claims.', 'admin.discord.claims', 'Manage claims'],
            ['Audit logs', 'Review administrative actions and security-relevant events.', 'admin.audit-logs', 'Review activity'],
        ] as [$title, $description, $routeName, $action])
            <a href="{{ route($routeName) }}" class="group flex min-h-44 flex-col rounded-2xl border p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-white/[.03]" style="background:var(--snck-surface);border-color:var(--snck-border);border-radius:var(--snck-radius)">
                <h2 class="font-semibold">{{ $title }}</h2>
                <p class="mt-2 flex-1 text-sm leading-6" style="color:var(--snck-muted)">{{ $description }}</p>
                <span class="mt-4 text-sm font-semibold" style="color:var(--snck-primary)">{{ $action }} <span aria-hidden="true">→</span></span>
            </a>
        @endforeach
    </div>
</section>
