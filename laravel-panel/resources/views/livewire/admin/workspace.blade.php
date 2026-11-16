<div class="space-y-6">
    <header><p class="text-xs font-semibold uppercase tracking-[.2em] text-violet-300">Administration</p><h1 class="mt-2 text-3xl font-bold">Panel workspace</h1><p class="mt-2 text-sm text-slate-400">Manage integrations and operator settings from the Laravel UI.</p></header>
    @if($notice)<div class="rounded-xl border border-emerald-500/30 bg-emerald-950/30 px-4 py-3 text-sm text-emerald-200">{{ $notice }} @if($newToken)<code class="mt-2 block break-all rounded bg-black/40 p-2">{{ $newToken }}</code>@endif</div>@endif
    @if($error)<div class="rounded-xl border border-rose-500/30 bg-rose-950/30 px-4 py-3 text-sm text-rose-200">{{ $error }}</div>@endif
    <nav class="flex flex-wrap gap-2 border-b border-slate-800 pb-3">
        @foreach(['settings'=>'Settings','cloudflare'=>'Cloudflare','api-keys'=>'API keys','discord'=>'Discord rewards'] as $key=>$label)
            <button wire:click="select('{{ $key }}')" class="rounded-lg px-3 py-2 text-sm {{ $section === $key ? 'bg-violet-500 text-white' : 'bg-slate-900 text-slate-300' }}">{{ $label }}</button>
        @endforeach
    </nav>
    @if($section === 'settings')
        <form wire:submit="saveSettings" class="space-y-5">
            <section class="grid gap-4 rounded-2xl border border-slate-700/70 bg-slate-900/50 p-5 backdrop-blur-xl md:grid-cols-2">
                <div class="md:col-span-2">
                    <h2 class="text-lg font-semibold">Panel configuration</h2>
                    <p class="mt-1 text-sm text-slate-400">Control the public-facing panel experience and account access.</p>
                </div>
                <label class="field md:col-span-2">Panel name<input wire:model="settings.panelName" maxlength="80"></label>
                @foreach(['enableRegistration'=>'Allow public registration','enableGoogleLogin'=>'Enable Google sign-in','enablePlayit'=>'Enable Playit integration','enableTutorial'=>'Show onboarding tutorial','enableLoginAnimation'=>'Show login animation','reducedMotion'=>'Reduce interface motion'] as $key=>$label)
                    <label class="flex items-center gap-3 rounded-xl border border-slate-700/70 bg-slate-950/50 p-4 text-sm"><input type="checkbox" wire:model="settings.{{ $key }}">{{ $label }}</label>
                @endforeach
            </section>
            <section class="space-y-4 rounded-2xl border border-slate-700/70 bg-slate-900/50 p-5 backdrop-blur-xl">
                <div>
                    <h2 class="text-lg font-semibold">Default user hosting quotas</h2>
                    <p class="mt-1 text-sm text-slate-400">Limits apply to regular users. Owner and Admin accounts can provision servers without these user quotas. RAM and disk values are in MB; CPU 100 equals one CPU core.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <label class="field">Maximum servers<input type="number" min="1" max="10000" step="1" wire:model="settings.quota_max_servers" required></label>
                    <label class="field">Total RAM (MB)<input type="number" min="1" max="16777216" step="1" wire:model="settings.quota_total_ram_mb" required></label>
                    <label class="field">Total CPU (%)<input type="number" min="1" max="100000" step="1" wire:model="settings.quota_total_cpu_percent" required></label>
                    <label class="field">Total disk (MB)<input type="number" min="1" max="1099511627776" step="1" wire:model="settings.quota_total_disk_mb" required></label>
                </div>
                <p class="text-xs text-slate-500">Current safe defaults: 3 servers, 8,192 MB RAM, 400% CPU and 51,200 MB disk. Changes affect new provisioning checks.</p>
            </section>
            <button class="rounded-lg bg-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-950/30">Save panel settings</button>
        </form>
    @elseif($section === 'cloudflare')
        <section class="space-y-5 rounded-2xl border border-slate-800 bg-slate-900/60 p-5"><form wire:submit="connectCloudflare" class="grid gap-3 md:grid-cols-3"><label class="field md:col-span-2">API token<input type="password" wire:model="cloudflareToken" autocomplete="new-password"></label><label class="field">Account ID (optional)<input wire:model="cloudflareAccountId"></label><button class="rounded-lg bg-violet-500 px-4 py-2 text-sm font-semibold md:col-span-3">Verify and connect</button></form><div><h2 class="mb-2 font-semibold">Accessible zones</h2><div class="grid gap-2 md:grid-cols-3">@forelse($zones as $zone)<div class="rounded-xl border border-slate-800 bg-slate-950 p-3"><strong>{{ $zone['name'] ?? 'Unnamed zone' }}</strong><p class="text-xs text-slate-500">{{ $zone['id'] ?? '' }}</p></div>@empty<p class="text-sm text-slate-500">Connect an account to load zones.</p>@endforelse</div></div></section>
    @elseif($section === 'api-keys')
        <section class="space-y-5 rounded-2xl border border-slate-800 bg-slate-900/60 p-5"><form wire:submit="createKey" class="grid gap-3 md:grid-cols-3"><label class="field">Label<input wire:model="keyLabel"></label><label class="field md:col-span-2">Scopes<select wire:model="keyScopes" multiple class="min-h-24">@foreach(['servers.read','servers.write','files.read','files.write','nodes.read','nodes.write'] as $scope)<option value="{{ $scope }}">{{ $scope }}</option>@endforeach</select></label><button class="rounded-lg bg-violet-500 px-4 py-2 text-sm font-semibold md:col-span-3">Create key</button></form><div class="space-y-2">@forelse($keys as $key)<article class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-950 p-4"><div><strong>{{ $key['label'] }}</strong><p class="text-xs text-slate-500">{{ implode(', ', $key['scopes']) }}</p></div>@if(!$key['revoked'])<button wire:click="revokeKey('{{ $key['id'] }}')" wire:confirm="Revoke this key?" class="rounded-lg bg-rose-950/60 px-3 py-2 text-sm text-rose-200">Revoke</button>@else<span class="text-xs text-rose-300">Revoked</span>@endif</article>@empty<p class="text-sm text-slate-500">No API keys.</p>@endforelse</div></section>
    @else
        <form wire:submit="saveDiscord" class="grid gap-4 rounded-2xl border border-slate-800 bg-slate-900/60 p-5 md:grid-cols-2"><label class="field">Guild ID<input wire:model="discord.config.guildId"></label><label class="field">Announcement channel ID<input wire:model="discord.config.announcementChannelId"></label><label class="field">OAuth application ID<input wire:model="discord.config.oauthClientId"></label><label class="field">OAuth redirect URI<input wire:model="discord.config.oauthRedirectUri"></label><label class="field">Retention days<input type="number" wire:model="discord.config.dataRetentionDays"></label><label class="field">Bot token (write only)<input type="password" wire:model="discordBotToken" autocomplete="new-password"></label><label class="field">OAuth secret (write only)<input type="password" wire:model="discordSecret" autocomplete="new-password"></label><label class="flex items-center gap-2"><input type="checkbox" wire:model="discord.config.enabled"> Enable Discord integration</label><button class="rounded-lg bg-violet-500 px-4 py-2 text-sm font-semibold md:col-span-2">Save Discord configuration</button></form>
    @endif
    <style>.field{display:flex;flex-direction:column;gap:.45rem;font-size:.82rem;color:#cbd5e1}.field input,.field select{width:100%;border:1px solid #334155;border-radius:.7rem;background:#020617;padding:.65rem .75rem;color:#f1f5f9}</style>
</div>
