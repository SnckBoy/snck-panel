<?php

namespace App\Livewire;

use App\Models\ApiKey;
use App\Services\CloudflareService;
use App\Services\DiscordRewardsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class AdminWorkspace extends Component
{
    public string $section = 'settings';

    public array $settings = [];

    public array $keys = [];

    public array $zones = [];

    public array $discord = [];

    public string $cloudflareToken = '';

    public string $cloudflareAccountId = '';

    public string $keyLabel = '';

    public array $keyScopes = ['servers.read'];

    public string $newToken = '';

    public string $notice = '';

    public string $error = '';

    public string $discordBotToken = '';

    public string $discordSecret = '';

    public function mount(): void
    {
        $this->authorizeStaff();
        $this->loadAll();
    }

    public function select(string $section): void
    {
        $this->authorizeStaff();
        abort_unless(in_array($section, ['settings', 'cloudflare', 'api-keys', 'discord'], true), 404);
        $this->section = $section;
        $this->loadAll();
    }

    public function loadAll(): void
    {
        $this->authorizeStaff();
        $this->settings = DB::table('settings')->where('is_encrypted', false)->pluck('value', 'key')->map(fn ($value) => json_decode((string) $value, true))->all();
        $this->keys = ApiKey::query()->where('user_id', auth()->id())->latest()->get()->map(fn (ApiKey $key): array => ['id' => $key->id, 'label' => $key->label, 'scopes' => $key->scopes ?? [], 'revoked' => (bool) $key->revoked_at, 'expires_at' => $this->formatKeyDate($key)])->all();
        $this->discord = app(DiscordRewardsService::class)->publicState(app(DiscordRewardsService::class)->state());

        try {
            $this->zones = app(CloudflareService::class)->zones();
        } catch (Throwable) {
            $this->zones = [];
        }
    }

    public function saveSettings(): void
    {
        $this->authorizeStaff();
        $data = $this->validate(['settings.panelName' => ['nullable', 'string', 'max:80'], 'settings.enableRegistration' => ['boolean'], 'settings.enablePlayit' => ['boolean'], 'settings.enableTutorial' => ['boolean'], 'settings.reducedMotion' => ['boolean']]);
        foreach ($data['settings'] as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => json_encode($value), 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->audit('settings.update', ['keys' => array_keys($data['settings'])]);
        $this->flash('Settings saved.');
    }

    public function connectCloudflare(CloudflareService $service): void
    {
        $this->authorizeStaff();
        $this->validate(['cloudflareToken' => ['required', 'string', 'max:512'], 'cloudflareAccountId' => ['nullable', 'string', 'max:64']]);

        try {
            $service->connect($this->cloudflareToken, $this->cloudflareAccountId ?: null);
            $this->cloudflareToken = '';
            $this->loadAll();
            $this->flash('Cloudflare account connected.');
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function createKey(): void
    {
        $this->authorizeStaff();
        $this->validate(['keyLabel' => ['required', 'string', 'max:100'], 'keyScopes' => ['required', 'array', 'min:1'], 'keyScopes.*' => [Rule::in(['servers.read', 'servers.write', 'files.read', 'files.write', 'nodes.read', 'nodes.write'])]]);
        $this->newToken = 'snck_'.Str::random(48);
        ApiKey::query()->create(['user_id' => auth()->id(), 'label' => trim($this->keyLabel), 'key_hash' => hash('sha256', $this->newToken), 'scopes' => array_values(array_unique($this->keyScopes))]);
        $this->keyLabel = '';
        $this->loadAll();
        $this->audit('api_key.created');
        $this->flash('API key created. Copy the token now; it is not stored in plaintext.');
    }

    public function revokeKey(string $id): void
    {
        $this->authorizeStaff();
        ApiKey::query()->whereKey($id)->where('user_id', auth()->id())->update(['revoked_at' => now()]);
        $this->loadAll();
        $this->flash('API key revoked.');
    }

    public function saveDiscord(): void
    {
        $this->authorizeStaff();
        $this->validate(['discord.config.guildId' => ['required', 'regex:/^\d{17,20}$/'], 'discord.config.oauthClientId' => ['nullable', 'regex:/^\d{17,20}$/'], 'discord.config.oauthRedirectUri' => ['nullable', 'url'], 'discord.config.dataRetentionDays' => ['integer', 'between:30,3650']]);
        try {
            app(DiscordRewardsService::class)->updateConfig(array_merge($this->discord['config'], ['botToken' => $this->discordBotToken, 'oauthClientSecret' => $this->discordSecret]));
            $this->discordBotToken = '';
            $this->discordSecret = '';
            $this->loadAll();
            $this->flash('Discord configuration saved.');
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    private function authorizeStaff(): void
    {
        abort_unless(auth()->user()?->isStaff(), 403);
    }

    private function audit(string $action, array $metadata = []): void
    {
        DB::table('audit_events')->insert(['user_id' => auth()->id(), 'action' => $action, 'resource_type' => 'admin-workspace', 'result' => 'success', 'metadata' => json_encode($metadata), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function flash(string $message): void
    {
        $this->notice = $message;
        $this->error = '';
    }

    private function fail(string $message): void
    {
        $this->error = $message;
        $this->notice = '';
    }

    private function formatKeyDate(ApiKey $key): ?string
    {
        $date = $key->getAttribute('expires_at');

        return $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : null;
    }

    public function render(): View
    {
        $this->authorizeStaff();

        return view('livewire.admin.workspace')->layout('layouts.app');
    }
}
