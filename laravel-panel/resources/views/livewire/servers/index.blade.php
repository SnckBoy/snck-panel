<section class="mx-auto max-w-6xl space-y-6">
    <header class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-[.2em] text-violet-300">Minecraft</p><h1 class="mt-2 text-3xl font-semibold">Servers</h1><p class="mt-2 text-sm text-slate-400">Your owned and explicitly shared servers. Actions remain protected by per-server policies.</p></div><a href="{{ route('servers.create') }}" class="rounded-xl bg-violet-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-400">Create server</a></header>
    <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">
        @if ($servers->isEmpty())<div class="p-8 text-center text-sm text-slate-400">No accessible servers are in the migrated database yet.</div>
        @else
            <div class="overflow-x-auto"><table class="w-full min-w-[42rem] text-left text-sm"><thead class="border-b border-slate-800 bg-slate-950/60 text-xs uppercase tracking-wide text-slate-400"><tr><th class="px-5 py-4">Server</th><th class="px-5 py-4">Node</th><th class="px-5 py-4">Version</th><th class="px-5 py-4">Status</th><th></th></tr></thead><tbody class="divide-y divide-slate-800">
                @foreach ($servers as $server)<tr><td class="px-5 py-4"><a href="{{ route('servers.show', $server->id) }}" class="font-semibold text-violet-200 hover:text-violet-100">{{ $server->name }}</a><p class="mt-1 font-mono text-[10px] text-slate-500">{{ $server->identifier }}</p></td><td class="px-5 py-4 text-slate-300">{{ $server->node?->name ?? 'Unknown node' }}</td><td class="px-5 py-4 text-slate-300">{{ $server->version ?: '—' }}</td><td class="px-5 py-4"><span class="rounded-full border border-slate-700 px-2.5 py-1 text-xs">{{ $server->status }}</span></td><td class="px-5 py-4 text-right"><a href="{{ route('servers.show', $server->id) }}" class="text-sm text-violet-300">Manage →</a></td></tr>@endforeach
            </tbody></table></div><div class="border-t border-slate-800 p-4">{{ $servers->links() }}</div>
        @endif
    </div>
</section>
