<?php

namespace App\Services;

use App\Models\CloudflareAccount;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareService
{
    private const API = 'https://api.cloudflare.com/client/v4';

    public function connect(string $token, ?string $accountId = null): CloudflareAccount
    {
        $token = trim($token);
        if ($token === '') {
            throw new RuntimeException('Token required');
        }

        $verification = $this->request($token, 'GET', '/user/tokens/verify');
        if (($verification['result']['status'] ?? null) !== 'active') {
            throw new RuntimeException('Cloudflare token is not active');
        }

        if ($accountId !== null && $accountId !== '') {
            $this->request($token, 'GET', '/accounts/'.rawurlencode($accountId));
        }

        $accounts = $this->request($token, 'GET', '/accounts?per_page=50')['result'] ?? [];
        $chosen = $accountId ?: ($accounts[0]['id'] ?? null);
        if (! $chosen) {
            throw new RuntimeException('No Cloudflare account is accessible by this token');
        }

        $selectedAccount = collect($accounts)->firstWhere('id', $chosen);

        return CloudflareAccount::query()->updateOrCreate(['account_id' => $chosen], [
            'account_id' => $chosen,
            'name' => $selectedAccount['name'] ?? null,
            'api_token_encrypted' => Crypt::encryptString($token),
            'metadata' => ['token_status' => $verification['result']['status']],
        ]);
    }

    public function account(?string $id = null): CloudflareAccount
    {
        $account = $id
            ? CloudflareAccount::query()->find($id)
            : CloudflareAccount::query()->latest()->first();
        if (! $account) {
            throw new RuntimeException('Cloudflare account is not connected');
        }

        return $account;
    }

    public function zones(?string $accountId = null): array
    {
        $account = $this->account($accountId);

        return $this->pagedResults($this->token($account), '/zones?account.id='.rawurlencode((string) $account->account_id).'&per_page=100');
    }

    public function zone(string $zoneId, ?string $accountId = null): array
    {
        return $this->request($this->token($this->account($accountId)), 'GET', '/zones/'.rawurlencode($zoneId))['result'] ?? [];
    }

    public function dns(string $zoneId, ?string $accountId = null): array
    {
        $account = $this->account($accountId);

        return $this->pagedResults($this->token($account), '/zones/'.rawurlencode($zoneId).'/dns_records?per_page=100');
    }

    public function createDns(string $zoneId, array $data, ?string $accountId = null): array
    {
        return $this->request($this->token($this->account($accountId)), 'POST', '/zones/'.rawurlencode($zoneId).'/dns_records', $data)['result'] ?? [];
    }

    public function updateDns(string $zoneId, string $recordId, array $data, ?string $accountId = null): array
    {
        return $this->request($this->token($this->account($accountId)), 'PUT', '/zones/'.rawurlencode($zoneId).'/dns_records/'.rawurlencode($recordId), $data)['result'] ?? [];
    }

    public function deleteDns(string $zoneId, string $recordId, ?string $accountId = null): array
    {
        return $this->request($this->token($this->account($accountId)), 'DELETE', '/zones/'.rawurlencode($zoneId).'/dns_records/'.rawurlencode($recordId))['result'] ?? [];
    }

    private function token(CloudflareAccount $account): string
    {
        return Crypt::decryptString((string) $account->api_token_encrypted);
    }

    private function pagedResults(string $token, string $path): array
    {
        $results = [];
        $page = 1;
        $maxPages = 20;
        do {
            $separator = str_contains($path, '?') ? '&' : '?';
            $body = $this->request($token, 'GET', $path.$separator.'page='.$page);
            $results = array_merge($results, $body['result'] ?? []);
            $totalPages = min((int) ($body['result_info']['total_pages'] ?? $page), $maxPages);
            $page++;
        } while ($page <= $totalPages);

        return $results;
    }

    private function request(string $token, string $method, string $path, ?array $payload = null): array
    {
        $request = Http::timeout(15)->acceptJson()->withToken($token);
        /** @var Response $response */
        $response = match ($method) {
            'POST' => $request->post(self::API.$path, $payload ?? []),
            'PUT' => $request->put(self::API.$path, $payload ?? []),
            'DELETE' => $request->delete(self::API.$path),
            default => $request->get(self::API.$path),
        };
        $body = $response->json() ?: [];
        if (! $response->successful() || ($body['success'] ?? true) === false) {
            $messages = collect($body['errors'] ?? [])->pluck('message')->filter()->implode(', ');
            throw new RuntimeException($messages ?: 'Cloudflare API request failed (HTTP '.$response->status().')');
        }

        return $body;
    }
}
