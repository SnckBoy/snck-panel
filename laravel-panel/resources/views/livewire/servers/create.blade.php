<section class="mx-auto max-w-5xl space-y-6">
    <header>
        <a href="{{ route('servers.index') }}" class="text-sm text-violet-300 hover:text-violet-200">← Servers</a>
        <h1 class="mt-2 text-3xl font-bold">Create server</h1>
        <p class="mt-2 text-sm text-slate-400">Provision a Minecraft server on a live, Docker-ready node and reserve one available allocation.</p>
    </header>

    @if ($notice !== '')
        <div class="rounded-xl border px-4 py-3 text-sm {{ $noticeType === 'error' ? 'border-rose-500/40 bg-rose-950/40 text-rose-200' : 'border-emerald-500/30 bg-emerald-950/30 text-emerald-200' }}">{{ $notice }}</div>
    @endif

    <form wire:submit="create" class="space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-5 sm:p-7">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="field">Server name
                <input wire:model="name" required maxlength="120" placeholder="Survival SMP">
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </label>
            <label class="field">Owner
                <select wire:model="ownerId" @disabled(!auth()->user()->isStaff())>
                    @foreach ($owners as $owner)<option value="{{ $owner->id }}">{{ $owner->username }} · {{ $owner->email }}</option>@endforeach
                </select>
                @error('ownerId')<span class="error">{{ $message }}</span>@enderror
            </label>
            <label class="field">Node
                <select wire:model.live="nodeId" required>
                    <option value="">Choose a Docker-ready node</option>
                    @foreach ($nodes as $node)
                        <option value="{{ $node->id }}">{{ $node->name }} · {{ $node->status }}{{ $node->docker_available ? ' · Docker ready' : ' · Docker unavailable' }}</option>
                    @endforeach
                </select>
                @error('nodeId')<span class="error">{{ $message }}</span>@enderror
            </label>
            <label class="field">Allocation
                <select wire:model="allocationId" required @disabled($nodeId === '')>
                    <option value="">Choose an available port</option>
                    @foreach ($allocations as $allocation)<option value="{{ $allocation->id }}">{{ $allocation->ip }}:{{ $allocation->port }}</option>@endforeach
                </select>
                @error('allocationId')<span class="error">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <label class="field">Server type
                <select wire:model="type"><option>PAPER</option><option>VANILLA</option><option>FABRIC</option><option>FORGE</option><option>NEOFORGE</option><option>SPIGOT</option><option>PURPUR</option><option>VELOCITY</option><option>BUNGEECORD</option><option>WATERFALL</option></select>
            </label>
            <label class="field">Minecraft version
                <input wire:model="version" maxlength="64" placeholder="latest">
            </label>
            <label class="field">Java version
                <select wire:model="javaVersion"><option value="8">Java 8</option><option value="11">Java 11</option><option value="17">Java 17</option><option value="21">Java 21</option><option value="25">Java 25</option></select>
            </label>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-slate-200">Resource limits</h2>
            <div class="mt-3 grid gap-4 md:grid-cols-3">
                <label class="field">Memory (MB)<input type="number" min="128" wire:model="memoryLimitMb">@error('memoryLimitMb')<span class="error">{{ $message }}</span>@enderror</label>
                <label class="field">CPU (%)<input type="number" min="1" wire:model="cpuLimitPercent">@error('cpuLimitPercent')<span class="error">{{ $message }}</span>@enderror</label>
                <label class="field">Disk metadata (MB)<input type="number" min="512" wire:model="diskLimitMb">@error('diskLimitMb')<span class="error">{{ $message }}</span>@enderror</label>
            </div>
            <p class="mt-2 text-xs text-slate-500">The current daemon contract enforces memory and CPU during provisioning. Disk is retained as server metadata until a quota-capable contract is available.</p>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-800 pt-5">
            <a href="{{ route('servers.index') }}" class="rounded-lg border border-slate-700 px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-800">Cancel</a>
            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-violet-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-400 disabled:opacity-50"><span wire:loading.remove>Create server</span><span wire:loading>Provisioning…</span></button>
        </div>
    </form>

    <style>
        .field{display:flex;flex-direction:column;gap:.45rem;font-size:.82rem;color:#cbd5e1}
        .field input,.field select{width:100%;border:1px solid #334155;border-radius:.7rem;background:#020617;padding:.65rem .75rem;color:#f1f5f9}
        .field input:focus,.field select:focus{outline:2px solid #8b5cf6;outline-offset:1px}
        .error{font-size:.75rem;color:#fda4af}
    </style>
</section>
