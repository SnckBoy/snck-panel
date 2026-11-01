<?php

namespace App\Services;

use App\Models\DiscordRewardState;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class DiscordRewardsService
{
    public function state(): array
    {
        $row = DiscordRewardState::query()->find('default');
        $payload = $row?->payload ?? [];

        return array_replace_recursive(['config' => ['enabled' => false, 'guildId' => null, 'announcementChannelId' => null, 'oauthClientId' => null, 'oauthRedirectUri' => null, 'minimumAccountAgeDays' => 0, 'dataRetentionDays' => 365, 'botTokenEncrypted' => null, 'oauthClientSecretEncrypted' => null], 'plans' => [], 'events' => [], 'claims' => [], 'diagnostics' => [], 'linkedAccounts' => []], $payload);
    }

    public function save(array $state): array
    {
        DiscordRewardState::query()->updateOrCreate(['id' => 'default'], ['payload' => $state]);

        return $state;
    }

    public function publicState(array $state): array
    {
        $config = $state['config'];
        unset($config['botTokenEncrypted'], $config['oauthClientSecretEncrypted']);
        $config['botConfigured'] = ! empty($state['config']['botTokenEncrypted']);
        $config['oauthSecretConfigured'] = ! empty($state['config']['oauthClientSecretEncrypted']);

        return ['config' => $config, 'plans' => $state['plans']];
    }

    public function updateConfig(array $input): array
    {
        $state = $this->state();
        $config = $state['config'];
        $guildId = trim((string) ($input['guildId'] ?? ''));
        $channelId = trim((string) ($input['announcementChannelId'] ?? ''));
        if (! preg_match('/^\d{17,20}$/', $guildId) || ($channelId !== '' && ! preg_match('/^\d{17,20}$/', $channelId))) {
            throw new RuntimeException('Discord guild and channel IDs must be valid snowflakes.');
        }
        $oauthId = trim((string) ($input['oauthClientId'] ?? $config['oauthClientId'] ?? ''));
        if ($oauthId !== '' && ! preg_match('/^\d{17,20}$/', $oauthId)) {
            throw new RuntimeException('Discord OAuth Client ID must be a valid application ID.');
        }
        $redirect = trim((string) ($input['oauthRedirectUri'] ?? $config['oauthRedirectUri'] ?? ''));
        if ($redirect !== '') {
            $url = parse_url($redirect);
            if (! $url || (($url['scheme'] ?? '') !== 'https' && ! (($url['scheme'] ?? '') === 'http' && in_array($url['host'] ?? '', ['localhost', '127.0.0.1'], true))) || isset($url['user'])) {
                throw new RuntimeException('OAuth redirect must be HTTPS, except localhost during development.');
            }
        }
        $retention = (int) ($input['dataRetentionDays'] ?? $config['dataRetentionDays'] ?? 365);
        $age = (int) ($input['minimumAccountAgeDays'] ?? 0);
        if ($retention < 30 || $retention > 3650 || $age < 0 || $age > 3650) {
            throw new RuntimeException('Retention and account age must be whole days within the supported range.');
        }
        $bot = trim((string) ($input['botToken'] ?? ''));
        $secret = trim((string) ($input['oauthClientSecret'] ?? ''));
        if ($bot !== '' && (strlen($bot) < 40 || strlen($bot) > 200)) {
            throw new RuntimeException('Bot token has an invalid length.');
        }
        if ($secret !== '' && (strlen($secret) < 10 || strlen($secret) > 300)) {
            throw new RuntimeException('OAuth client secret has an invalid length.');
        }
        if (($input['enabled'] ?? false) && $bot === '' && empty($config['botTokenEncrypted'])) {
            throw new RuntimeException('Enter the bot token before enabling the bot.');
        }
        if (($input['enabled'] ?? false) && $oauthId === '') {
            throw new RuntimeException('Configure the OAuth application ID before enabling the bot.');
        }
        if (($input['enabled'] ?? false) && $secret === '' && empty($config['oauthClientSecretEncrypted'])) {
            throw new RuntimeException('Configure the OAuth client secret before enabling the bot.');
        }
        $config = array_merge($config, ['enabled' => (bool) ($input['enabled'] ?? false), 'guildId' => $guildId, 'announcementChannelId' => $channelId ?: null, 'oauthClientId' => $oauthId ?: null, 'oauthRedirectUri' => $redirect ?: null, 'minimumAccountAgeDays' => $age, 'dataRetentionDays' => $retention]);
        if ($bot !== '') {
            $config['botTokenEncrypted'] = Crypt::encryptString($bot);
        }
        if ($secret !== '') {
            $config['oauthClientSecretEncrypted'] = Crypt::encryptString($secret);
        }
        $state['config'] = $config;

        return $this->save($state);
    }

    public function validatePlan(array $input, ?string $forcedId = null): array
    {
        $id = strtolower($forcedId ?? (string) ($input['id'] ?? ''));
        $maxTotalClaimsInput = $input['maxTotalClaims'] ?? null;
        $plan = ['id' => $id, 'name' => trim((string) ($input['name'] ?? '')), 'description' => trim((string) ($input['description'] ?? '')), 'requiredInvites' => (int) ($input['requiredInvites'] ?? 0), 'allowCreate' => (bool) ($input['allowCreate'] ?? false), 'allowUpgrade' => (bool) ($input['allowUpgrade'] ?? false), 'ram' => (float) ($input['ram'] ?? 0), 'cpu' => (float) ($input['cpu'] ?? 0), 'disk' => (float) ($input['disk'] ?? 0), 'durationHours' => (float) ($input['durationHours'] ?? 0), 'allowedNodeIds' => array_values($input['allowedNodeIds'] ?? []), 'maxClaimsPerUser' => (int) ($input['maxClaimsPerUser'] ?? 1), 'maxTotalClaims' => ($maxTotalClaimsInput === '' || $maxTotalClaimsInput === null) ? null : (int) $maxTotalClaimsInput, 'enabled' => (bool) ($input['enabled'] ?? false), 'sortOrder' => (int) ($input['sortOrder'] ?? 0)];
        if (! preg_match('/^[a-z0-9][a-z0-9-]{1,47}$/', $id) || strlen($plan['name']) < 2 || strlen($plan['name']) > 64 || strlen($plan['description']) > 300 || $plan['requiredInvites'] < 1 || $plan['requiredInvites'] > 5000 || (! $plan['allowCreate'] && ! $plan['allowUpgrade']) || $plan['ram'] <= 0 || $plan['ram'] > 1024 || $plan['cpu'] <= 0 || $plan['cpu'] > 10000 || $plan['disk'] <= 0 || $plan['disk'] > 1048576 || $plan['durationHours'] < 0 || $plan['durationHours'] > 87600 || $plan['maxClaimsPerUser'] < 1 || $plan['maxClaimsPerUser'] > 50 || ($plan['maxTotalClaims'] !== null && ($plan['maxTotalClaims'] < 1 || $plan['maxTotalClaims'] > 1000000)) || $plan['sortOrder'] < 0 || count($plan['allowedNodeIds']) > 200 || count(array_unique($plan['allowedNodeIds'])) !== count($plan['allowedNodeIds'])) {
            throw new RuntimeException('Invalid reward plan.');
        }
        foreach ($plan['allowedNodeIds'] as $nodeId) {
            if (! is_string($nodeId) || strlen($nodeId) > 120) {
                throw new RuntimeException('Invalid reward plan node list.');
            }
        }

        return $plan;
    }

    public function oauthUrl(string $state): string
    {
        $config = $this->state()['config'];
        if (empty($config['oauthClientId']) || empty($config['oauthRedirectUri'])) {
            throw new RuntimeException('Discord OAuth is not configured.');
        }

        return 'https://discord.com/oauth2/authorize?'.http_build_query(['client_id' => $config['oauthClientId'], 'redirect_uri' => $config['oauthRedirectUri'], 'response_type' => 'code', 'scope' => 'identify guilds', 'state' => $state]);
    }

    public function link(User $user, string $code): array
    {
        $config = $this->state()['config'];
        $secret = Crypt::decryptString((string) ($config['oauthClientSecretEncrypted'] ?? ''));
        $token = Http::asForm()->timeout(15)->post('https://discord.com/api/oauth2/token', ['client_id' => $config['oauthClientId'], 'client_secret' => $secret, 'grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => $config['oauthRedirectUri']]);
        if ($token->failed() || ! $token->json('access_token')) {
            throw new RuntimeException('Discord OAuth token exchange failed.');
        }
        $access = (string) $token->json('access_token');
        $profile = Http::withToken($access)->timeout(15)->get('https://discord.com/api/users/@me');
        if ($profile->failed() || ! $profile->json('id')) {
            throw new RuntimeException('Discord profile lookup failed.');
        }
        $state = $this->state();
        $state['linkedAccounts'][(string) $user->id] = ['discordId' => (string) $profile->json('id'), 'username' => (string) ($profile->json('global_name') ?: $profile->json('username')), 'tokenEncrypted' => Crypt::encryptString($access), 'linkedAt' => now()->toISOString(), 'inviteCount' => (int) ($state['linkedAccounts'][(string) $user->id]['inviteCount'] ?? 0)];
        $this->save($state);

        return $state['linkedAccounts'][(string) $user->id];
    }

    public function unlink(User $user): void
    {
        $state = $this->state();
        unset($state['linkedAccounts'][(string) $user->id]);
        $this->save($state);
    }

    public function publicUserState(User $user): array
    {
        $state = $this->state();
        $account = $state['linkedAccounts'][(string) $user->id] ?? null;
        if ($account) {
            unset($account['tokenEncrypted']);
        }

        return ['account' => $account, 'plans' => array_values(array_filter($state['plans'], fn (array $plan): bool => (bool) ($plan['enabled'] ?? false))), 'claims' => array_values(array_filter($state['claims'], fn (array $claim): bool => ($claim['userId'] ?? null) === (string) $user->id))];
    }

    public function claim(User $user, string $planId): array
    {
        $state = $this->state();
        $account = $state['linkedAccounts'][(string) $user->id] ?? null;
        $plan = collect($state['plans'])->firstWhere('id', $planId);
        if (! $account || ! $plan || ! ($plan['enabled'] ?? false)) {
            throw new RuntimeException('Discord account or reward plan is unavailable.');
        }
        if ((int) ($account['inviteCount'] ?? 0) < (int) $plan['requiredInvites']) {
            throw new RuntimeException('Invite requirement has not been met.');
        }
        $active = collect($state['claims'])->first(fn (array $claim): bool => ($claim['userId'] ?? null) === (string) $user->id && ($claim['planId'] ?? null) === $planId && in_array($claim['status'] ?? '', ['pending', 'processing', 'approved'], true));
        if ($active) {
            throw new RuntimeException('This reward has already been claimed.');
        }
        $claim = ['id' => (string) Str::uuid(), 'userId' => (string) $user->id, 'planId' => $planId, 'status' => 'pending', 'createdAt' => now()->toISOString(), 'updatedAt' => now()->toISOString()];
        $state['claims'][] = $claim;
        $this->save($state);

        return $claim;
    }

    public function reconcileMembers(): int
    {
        $state = $this->state();
        $config = $state['config'];
        $token = Crypt::decryptString((string) ($config['botTokenEncrypted'] ?? ''));
        $guild = (string) ($config['guildId'] ?? '');
        if ($token === '' || $guild === '') {
            throw new RuntimeException('Discord bot and guild are not configured.');
        }
        $members = [];
        $after = '0';
        do {
            $response = Http::withToken($token, 'Bot')->timeout(20)->get('https://discord.com/api/guilds/'.$guild.'/members', ['limit' => 1000, 'after' => $after]);
            if ($response->failed()) {
                throw new RuntimeException('Discord member reconciliation failed.');
            }
            $page = $response->json();
            $members = array_merge($members, is_array($page) ? $page : []);
            $last = end($page);
            $after = count($page) === 1000 ? (string) ($last['user']['id'] ?? '') : '';
        } while ($after !== '' && count($members) < 10000);
        $memberIds = collect($members)->pluck('user.id')->map(fn ($id) => (string) $id)->all();
        foreach ($state['linkedAccounts'] as &$account) {
            $account['inGuild'] = in_array((string) ($account['discordId'] ?? ''), $memberIds, true);
        }
        unset($account);
        $this->save($state);

        return count($members);
    }

    public function cleanupRetention(): int
    {
        $state = $this->state();
        $cutoff = now()->subDays((int) $state['config']['dataRetentionDays']);
        $before = count($state['claims']);
        $state['claims'] = array_values(array_filter($state['claims'], fn (array $claim): bool => ! isset($claim['createdAt']) || now()->parse($claim['createdAt'])->greaterThanOrEqualTo($cutoff) || in_array($claim['status'] ?? '', ['pending', 'processing'], true)));
        $this->save($state);

        return $before - count($state['claims']);
    }
}
