<div class="space-y-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.2em] text-violet-300">Administration / Security</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight">Audit logs</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-400">A searchable, read-only history of panel and hosting activity.</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/[.04] px-4 py-3 text-right shadow-lg shadow-black/10 backdrop-blur-xl">
            <p class="text-xs text-slate-400">Events shown</p>
            <p class="mt-1 text-xl font-semibold tabular-nums">{{ number_format($events->total()) }}</p>
        </div>
    </header>

    <section class="rounded-2xl border border-white/10 bg-slate-900/55 p-4 shadow-xl shadow-black/10 backdrop-blur-xl">
        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_12rem_14rem]">
            <label class="flex flex-col gap-2 text-xs font-medium text-slate-300">
                Search events
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Action, user, server, IP or request ID" class="rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2.5 text-sm text-slate-100 outline-none transition focus:border-violet-400/70 focus:ring-2 focus:ring-violet-500/20">
            </label>
            <label class="flex flex-col gap-2 text-xs font-medium text-slate-300">
                Result
                <select wire:model.live="filterResult" class="rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2.5 text-sm text-slate-100 outline-none focus:border-violet-400/70">
                    <option value="all">All results</option>
                    <option value="success">Success</option>
                    <option value="failure">Failure</option>
                </select>
            </label>
            <label class="flex flex-col gap-2 text-xs font-medium text-slate-300">
                Action
                <select wire:model.live="filterAction" class="rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2.5 text-sm text-slate-100 outline-none focus:border-violet-400/70">
                    <option value="all">All actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}">{{ $action }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/55 shadow-xl shadow-black/10 backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="border-b border-white/10 bg-white/[.03] text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Event</th>
                        <th class="px-4 py-3 font-semibold">Actor</th>
                        <th class="px-4 py-3 font-semibold">Resource</th>
                        <th class="px-4 py-3 font-semibold">Result</th>
                        <th class="px-4 py-3 font-semibold">IP address</th>
                        <th class="px-4 py-3 font-semibold">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[.06]">
                    @forelse($events as $event)
                        <tr wire:key="audit-event-{{ $event->id }}" class="transition hover:bg-white/[.025]">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-100">{{ $event->action }}</p>
                                <p class="mt-1 text-xs text-slate-500">#{{ $event->id }}@if($event->request_id) · {{ $event->request_id }}@endif</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-slate-200">{{ $event->user?->username ?? $event->user?->name ?? 'System / deleted user' }}</p>
                                @if($event->user?->email)<p class="mt-1 text-xs text-slate-500">{{ $event->user->email }}</p>@endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-slate-200">{{ $event->resource_type ?: '—' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $event->resource_id ?: '—' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold',
                                    'border-emerald-400/20 bg-emerald-400/10 text-emerald-300' => $event->result === 'success',
                                    'border-rose-400/20 bg-rose-400/10 text-rose-300' => $event->result !== 'success',
                                ])>{{ $event->result }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-400">{{ $event->ip_address ?: '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-400">{{ $event->created_at?->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-14 text-center">
                            <p class="font-medium text-slate-200">No audit events found</p>
                            <p class="mt-1 text-xs text-slate-500">Try changing the filters or search term.</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/[.07] px-4 py-3">{{ $events->links() }}</div>
    </section>
</div>
