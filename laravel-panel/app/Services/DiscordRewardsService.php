<?php

namespace App\Services;

use App\Models\DiscordRewardState;
use Illuminate\Support\Facades\Crypt;
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
}
