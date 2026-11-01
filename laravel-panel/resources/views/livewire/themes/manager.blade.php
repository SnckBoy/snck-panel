<div class="space-y-8">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-semibold uppercase tracking-[.22em] text-violet-300">Appearance & structure</p><h1 class="mt-2 text-3xl font-bold">Snck Theme</h1><p class="mt-2 max-w-3xl text-sm text-slate-400">Customize colors, typography, layout, navigation, dashboard sections, and server tabs. Themes never change permissions or expose routes.</p></div>
        <button wire:click="newTheme" class="rounded-xl bg-violet-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-400">New custom theme</button>
    </header>
    @if ($notice !== '')<div class="rounded-xl border px-4 py-3 text-sm {{ $noticeType === 'error' ? 'border-rose-500/40 bg-rose-950/40 text-rose-200' : 'border-emerald-500/30 bg-emerald-950/30 text-emerald-200' }}">{{ $notice }}</div>@endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($themes as $theme)
            <article class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">
                <div class="h-2" style="background: {{ $theme['config']['primary'] }}"></div>
                <div class="space-y-4 p-5">
                    <div class="flex items-start justify-between gap-3"><div><h2 class="font-semibold">{{ $theme['name'] }}</h2><p class="mt-1 text-xs text-slate-400">{{ $theme['built_in'] ? 'System theme' : 'Custom theme' }} · v{{ $theme['version'] }}</p></div>
                        @if ($activeId === $theme['id'])<span class="rounded-full bg-emerald-400/10 px-2.5 py-1 text-xs text-emerald-300">Active</span>@endif
                    </div>
                    <p class="min-h-10 text-sm text-slate-400">{{ $theme['description'] }}</p>
                    <div class="flex gap-2" aria-label="Theme color preview">
                        @foreach (['primary','secondary','accent','background','surface','text'] as $color)<span title="{{ $color }}" class="h-7 flex-1 rounded-md border border-white/10" style="background: {{ $theme['config'][$color] }}"></span>@endforeach
                    </div>
                    <div class="flex flex-wrap gap-2 border-t border-slate-800 pt-4">
                        <button wire:click="apply('{{ $theme['id'] }}')" @disabled(!$theme['enabled'] || $activeId === $theme['id']) class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-medium hover:bg-slate-700 disabled:opacity-40">Apply</button>
                        <button wire:click="edit('{{ $theme['id'] }}')" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-medium hover:bg-slate-700">{{ $theme['built_in'] ? 'View / clone' : 'Edit' }}</button>
                        <button wire:click="cloneTheme('{{ $theme['id'] }}')" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-medium hover:bg-slate-700">Clone</button>
                        <a href="{{ route('themes.export', ['theme' => $theme['id']]) }}" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-medium hover:bg-slate-700">Export ZIP</a>
                        @if ($theme['id'] !== 'snck-default')<button wire:click="toggle('{{ $theme['id'] }}')" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-medium hover:bg-slate-700">{{ $theme['enabled'] ? 'Disable' : 'Enable' }}</button>@endif
                        @if (!$theme['built_in'])<button wire:click="delete('{{ $theme['id'] }}')" wire:confirm="Delete this custom theme?" class="rounded-lg bg-rose-950/60 px-3 py-2 text-xs font-medium text-rose-200 hover:bg-rose-900">Delete</button>@endif
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,.8fr)]">
        <form wire:submit="save" class="space-y-6 rounded-2xl border border-slate-800 bg-slate-900/60 p-5 sm:p-7">
            <div><p class="text-xs font-semibold uppercase tracking-[.2em] text-violet-300">Theme builder</p><h2 class="mt-2 text-xl font-semibold">{{ $themeId === '' ? 'Create a theme' : 'Edit theme' }}</h2><p class="mt-1 text-sm text-slate-400">Built-ins are protected; use Clone to create an editable copy.</p></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="field">Theme ID<input wire:model="themeId" maxlength="48" placeholder="my-theme" @if ($themeId !== '') readonly @endif></label>
                <label class="field">Name<input wire:model="name" maxlength="64" placeholder="My Theme"></label>
                <label class="field">Author<input wire:model="author" maxlength="64"></label>
                <label class="field">Version<input wire:model="version" maxlength="32"></label>
                <label class="field sm:col-span-2">Description<textarea wire:model="description" maxlength="240" rows="2"></textarea></label>
            </div>
            <div>
                <h3 class="mb-3 font-semibold">Colors and appearance</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (['primary','secondary','accent','background','surface','sidebar','navbar','text','muted','border','success','warning','error'] as $key)
                        <label class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/70 p-3 text-sm capitalize"><input type="color" wire:model.live="config.{{ $key }}" class="h-9 w-11 cursor-pointer rounded border-0 bg-transparent p-0"><span>{{ str_replace('_',' ',$key) }}</span><span class="ml-auto font-mono text-[10px] text-slate-500">{{ $config[$key] ?? '' }}</span></label>
                    @endforeach
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="field">Corner radius<input type="number" step="1" min="0" max="32" wire:model="config.radius"></label>
                    <label class="field">Font scale<input type="number" step="0.01" min="0.85" max="1.25" wire:model="config.fontScale"></label>
                    <label class="field">Font family<select wire:model="config.fontFamily"><option value="system">System</option><option value="inter">Inter</option><option value="mono">Monospace</option></select></label>
                    <label class="field">Density<select wire:model="config.density"><option value="compact">Compact</option><option value="comfortable">Comfortable</option><option value="spacious">Spacious</option></select></label>
                    <label class="field">Elevation<select wire:model="config.elevation"><option value="flat">Flat</option><option value="soft">Soft</option><option value="glow">Glow</option></select></label>
                    <label class="field">Surface opacity<input type="number" min="55" max="100" step="1" wire:model="config.surfaceOpacity"></label>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <label class="field">Panel shell<select wire:model="layout.structure.shell"><option value="snck">Snck</option><option value="pterodactyl">Pterodactyl-inspired</option></select></label>
                <label class="field">Navigation placement<select wire:model="layout.structure.navigationMode"><option value="sidebar">Sidebar</option><option value="topbar">Top bar</option></select></label>
                <label class="field">Sidebar width<select wire:model="layout.structure.sidebarMode"><option value="expanded">Expanded</option><option value="compact">Compact</option></select></label>
                <label class="field">Content width<select wire:model="layout.structure.contentWidth"><option value="standard">Standard</option><option value="wide">Wide</option><option value="full">Full</option></select></label>
                <label class="field">Dashboard arrangement<select wire:model="layout.structure.dashboardMode"><option value="overview">Operations overview</option><option value="server-first">Server-first</option></select></label>
                <label class="field">Server navigation style<select wire:model="layout.structure.serverNavigationMode"><option value="sidebar">Sidebar</option><option value="tabs">Tabs</option></select></label>
            </div>
            @php
                $groups = [
                    'navigation' => ['title' => 'Panel navigation', 'order' => 'navigationOrder', 'hidden' => 'hiddenNavigation', 'labels' => ['overview'=>'Overview','servers'=>'Servers','nodes'=>'Nodes','deploy'=>'Create server','admin-control'=>'Admin control','fleet'=>'Server fleet','api-keys'=>'API keys','cloudflare'=>'Cloudflare','snck-theme'=>'Snck Theme','settings'=>'Settings']],
                    'dashboard' => ['title' => 'Dashboard sections', 'order' => 'dashboardOrder', 'hidden' => 'hiddenDashboard', 'labels' => ['welcome'=>'Welcome / fleet hero','quick-actions'=>'Quick actions','metrics'=>'Metric cards','system-health'=>'System health','server-inventory'=>'Server inventory']],
                    'metrics' => ['title' => 'Metric cards', 'order' => 'metricsOrder', 'hidden' => 'hiddenMetrics', 'labels' => ['cpu'=>'Panel CPU','ram'=>'Host memory','net'=>'Servers online','nodes'=>'Active containers']],
                    'server' => ['title' => 'Server management navigation', 'order' => 'structure.serverNavigationOrder', 'hidden' => 'structure.hiddenServerNavigation', 'labels' => ['overview'=>'Overview','console'=>'Console','players'=>'Players','properties'=>'Properties','files'=>'File manager','sftp'=>'SFTP details','subusers'=>'Sub-users','plugins'=>'Plugins','mods'=>'Mods','modpacks'=>'Modpacks','settings'=>'Settings','backups'=>'Backups','playit'=>'Playit tunnel']],
                ];
            @endphp
            @foreach ($groups as $groupKey => $group)
                <div><h3 class="mb-3 font-semibold">{{ $group['title'] }}</h3><div class="space-y-2">
                    @foreach (data_get($layout, $group['order'], []) as $index => $item)
                        <div wire:key="{{ $groupKey }}-{{ $item }}" class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-800 bg-slate-950/50 p-2">
                            <label class="flex min-w-40 flex-1 items-center gap-2 text-sm"><input type="checkbox" @checked(!in_array($item, data_get($layout, $group['hidden'], []), true)) wire:click="toggleHidden('{{ $groupKey }}','{{ $item }}')"><span>{{ $group['labels'][$item] ?? $item }}</span></label>
                            @if (in_array($groupKey, ['navigation','server'], true))<input class="w-36 rounded-lg border border-slate-700 bg-slate-900 px-2 py-1.5 text-xs" value="{{ $layout['labels'][$item] ?? '' }}" placeholder="Custom label" wire:change="setLabel('{{ $item }}', $event.target.value)">@endif
                            <button type="button" aria-label="Move up" wire:click="moveItem('{{ $groupKey }}','{{ $item }}',-1)" @disabled($index===0) class="rounded-md bg-slate-800 px-2 py-1 disabled:opacity-30">↑</button>
                            <button type="button" aria-label="Move down" wire:click="moveItem('{{ $groupKey }}','{{ $item }}',1)" @disabled($index===count(data_get($layout,$group['order'],[]))-1) class="rounded-md bg-slate-800 px-2 py-1 disabled:opacity-30">↓</button>
                        </div>
                    @endforeach
                </div></div>
            @endforeach
            <div class="space-y-3"><h3 class="font-semibold">Custom navigation shortcuts</h3><div class="grid gap-3 sm:grid-cols-3"><label class="field">Label<input wire:model="shortcutLabel" maxlength="32"></label><label class="field">HTTPS URL<input wire:model="shortcutUrl" placeholder="https://example.com"></label><label class="field">Internal target<select wire:model="shortcutTarget"><option value="">Choose target</option>@foreach ($groups['navigation']['labels'] as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label></div><button type="button" wire:click="addShortcut" class="rounded-lg bg-slate-800 px-3 py-2 text-sm">Add shortcut</button>
                <div class="space-y-2">@foreach (($layout['customNavigation'] ?? []) as $shortcut)<div class="flex items-center justify-between rounded-lg border border-slate-800 p-3 text-sm"><span>{{ $shortcut['label'] }} <span class="text-slate-500">{{ $shortcut['url'] ?? ('→ '.$shortcut['target']) }}</span></span><button type="button" wire:click="removeShortcut('{{ $shortcut['id'] }}')" class="text-rose-300">Remove</button></div>@endforeach</div>
            </div>
            <div class="flex flex-wrap gap-3 border-t border-slate-800 pt-5"><button type="submit" class="rounded-xl bg-violet-500 px-5 py-2.5 font-semibold text-white hover:bg-violet-400">Save custom theme</button><button type="button" wire:click="restoreDefault" class="rounded-xl border border-slate-700 px-4 py-2.5 text-sm hover:bg-slate-800">Restore Snck Default</button></div>
        </form>

        <aside class="space-y-5">
            <div class="rounded-2xl border border-slate-800 p-5" style="background: {{ $config['background'] ?? '#0a0a0f' }}; color: {{ $config['text'] ?? '#f4f3fa' }}">
                <p class="text-xs uppercase tracking-[.2em]" style="color: {{ $config['muted'] ?? '#aaa7b8' }}">Live preview · {{ $layout['structure']['shell'] ?? 'snck' }} shell</p><div class="mt-4 rounded-xl p-4" style="background: {{ $config['surface'] ?? '#14141d' }}; border: 1px solid {{ $config['border'] ?? '#302d3b' }}; border-radius: {{ (int) ($config['radius'] ?? 16) }}px"><div class="flex items-center gap-3"><span class="h-3 w-3 rounded-full" style="background: {{ $config['primary'] ?? '#9275ff' }}"></span><strong>{{ $name ?: 'Theme preview' }}</strong></div><p class="mt-3 text-sm" style="color: {{ $config['muted'] ?? '#aaa7b8' }}">Panel appearance preview</p><button type="button" class="mt-4 rounded-lg px-3 py-2 text-sm font-semibold text-white" style="background: {{ $config['primary'] ?? '#9275ff' }}">Primary action</button></div>
            </div>
            <form wire:submit="import" class="space-y-3 rounded-2xl border border-slate-800 bg-slate-900/60 p-5"><h3 class="font-semibold">Import theme package</h3><p class="text-sm text-slate-400">ZIP up to 2 MiB. Only one `theme.json` is allowed; assets, scripts, traversal, and symlinks are rejected.</p><input type="file" wire:model="package" accept=".zip" class="block w-full text-sm text-slate-300"><div wire:loading wire:target="package,import" class="text-xs text-violet-300">Validating package…</div><button class="rounded-lg bg-slate-800 px-3 py-2 text-sm" type="submit">Validate and import</button></form>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5"><h3 class="font-semibold">Safety boundary</h3><p class="mt-2 text-sm leading-6 text-slate-400">Themes are declarative JSON. They can reorder and hide supported navigation or dashboard items and add HTTPS shortcuts. They cannot execute code, alter authorization, or make hidden features accessible.</p></div>
        </aside>
    </section>
    <style>.field{display:flex;flex-direction:column;gap:.45rem;font-size:.82rem;color:#cbd5e1}.field input,.field select,.field textarea{width:100%;border:1px solid #334155;border-radius:.7rem;background:#020617;padding:.65rem .75rem;color:#f1f5f9}.field input:focus,.field select:focus,.field textarea:focus{outline:2px solid #8b5cf6;border-color:transparent}</style>
</div>
