<?php

namespace App\Services;

use App\Models\Node;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NodeClient
{
    private const SERVER_ROUTES = [
        'files/list' => ['POST'],
        'files/read' => ['POST'],
        'files/write' => ['POST'],
        'files/write-base64' => ['POST'],
        'files/write-chunk' => ['POST'],
        'files/replace-batch' => ['POST'],
        'files/mkdir' => ['POST'],
        'files/rename' => ['POST'],
        'files/delete' => ['POST'],
        'files/zip' => ['POST'],
        'files/unzip' => ['POST'],
        'stats' => ['GET'],
        'command' => ['POST'],
        'backups' => ['GET', 'POST'],
    ];

    private const MAX_DOWNLOAD_BYTES = 536870912; // 512 MiB per response.

    public function health(Node $node): array
    {
        $payload = $this->jsonRequest($this->request($node)->get('/v1/health'), 'Node health request');
        if (($payload['ok'] ?? null) !== true || ! is_bool($payload['docker'] ?? null)) {
            throw new RuntimeException('Node health response is incomplete.');
        }

        return $payload;
    }

    public function restartNode(Node $node): array
    {
        return $this->jsonRequest($this->request($node)->post('/v1/daemon/restart'), 'Node restart request');
    }

    public function createServer(Node $node, array $payload): array
    {
        $this->safeServerId((string) ($payload['id'] ?? ''));
        $port = filter_var($payload['port'] ?? null, FILTER_VALIDATE_INT);
        $ram = filter_var($payload['ram'] ?? null, FILTER_VALIDATE_FLOAT);
        $cpu = filter_var($payload['cpu'] ?? null, FILTER_VALIDATE_INT);
        if ($port === false || $port < 1 || $port > 65535 || $ram === false || $ram <= 0 || $ram > 2048 || $cpu === false || $cpu < 1 || $cpu > 10000) {
            throw new RuntimeException('Invalid daemon server resource payload.');
        }
        if (! isset($payload['type']) || ! preg_match('/^[A-Z0-9_-]{1,32}$/', (string) $payload['type'])) {
            throw new RuntimeException('Invalid daemon server type.');
        }
        if (! isset($payload['rconPassword']) || strlen((string) $payload['rconPassword']) < 24) {
            throw new RuntimeException('A strong RCON password is required for daemon provisioning.');
        }
        if (isset($payload['image']) && ! preg_match('~^itzg/(minecraft-server|bungeecord):[A-Za-z0-9._-]{1,80}$~', (string) $payload['image'])) {
            throw new RuntimeException('Only supported itzg Minecraft images may be provisioned.');
        }

        return $this->jsonRequest($this->request($node)->post('/v1/servers', $payload), 'Node server creation');
    }

    public function serverAction(Node $node, string $serverId, string $action): array
    {
        if (! in_array($action, ['start', 'stop', 'restart', 'kill'], true)) {
            throw new RuntimeException('Unsupported server action.');
        }

        return $this->jsonRequest($this->request($node)->post('/v1/servers/'.$this->safeServerId($serverId).'/'.$action), 'Node server action');
    }

    public function serverStatus(Node $node, string $serverId): array
    {
        return $this->jsonRequest($this->request($node)->get('/v1/servers/'.$this->safeServerId($serverId).'/status'), 'Node server status');
    }

    public function deleteServer(Node $node, string $serverId): array
    {
        return $this->jsonRequest($this->request($node)->delete('/v1/servers/'.$this->safeServerId($serverId)), 'Node server deletion');
    }

    public function serverRequest(Node $node, string $serverId, string $method, string $operation, array $payload = []): array
    {
        $method = strtoupper($method);
        if (! isset(self::SERVER_ROUTES[$operation]) || ! in_array($method, self::SERVER_ROUTES[$operation], true)) {
            throw new RuntimeException('Daemon method/path combination is not in the frozen v1 contract.');
        }
        foreach (['files/list', 'files/read', 'files/write', 'files/write-base64', 'files/write-chunk', 'files/replace-batch', 'files/mkdir', 'files/rename', 'files/delete', 'files/zip', 'files/unzip'] as $fileOperation) {
            if ($operation === $fileOperation) {
                $this->validateFilePayload($operation, $payload);
            }
        }
        $path = '/v1/servers/'.$this->safeServerId($serverId).'/'.$operation;
        $request = $this->request($node);
        $response = $method === 'GET'
            ? $request->get($path, $payload)
            : $request->post($path, $payload);

        return $this->jsonRequest($response, 'Node server operation');
    }

    public function serverLogs(Node $node, string $serverId): string
    {
        $response = $this->request($node)->get('/v1/servers/'.$this->safeServerId($serverId).'/logs');
        if ($response->failed()) {
            throw new RuntimeException("Node server logs request failed with HTTP {$response->status()}.");
        }

        return $response->body();
    }

    public function listBackups(Node $node, string $serverId): array
    {
        $payload = $this->serverRequest($node, $serverId, 'GET', 'backups');
        if (! array_is_list($payload)) {
            throw new RuntimeException('Node returned an invalid backup inventory.');
        }

        return $payload;
    }

    public function createBackup(Node $node, string $serverId): array
    {
        return $this->serverRequest($node, $serverId, 'POST', 'backups');
    }

    public function deleteBackup(Node $node, string $serverId, string $filename): array
    {
        $path = '/v1/servers/'.$this->safeServerId($serverId).'/backups/'.$this->safeBackupFilename($filename);

        return $this->jsonRequest($this->request($node)->delete($path), 'Node backup deletion');
    }

    public function downloadServerFile(Node $node, string $serverId, string $path): string
    {
        $this->safeRelativePath($path);

        return $this->download($this->request($node), '/v1/servers/'.$this->safeServerId($serverId).'/files/download', ['path' => $path], 'Node file download');
    }

    public function downloadBackup(Node $node, string $serverId, string $filename): string
    {
        $path = '/v1/servers/'.$this->safeServerId($serverId).'/backups/'.$this->safeBackupFilename($filename);

        return $this->download($this->request($node), $path, [], 'Node backup download');
    }

    public function request(Node $node): PendingRequest
    {
        $scheme = strtolower((string) ($node->getAttribute('protocol') ?: 'https'));
        if ($scheme !== 'https') {
            throw new RuntimeException('Remote node connections must use HTTPS. HTTP and disabled certificate verification are not supported.');
        }

        $host = trim((string) ($node->fqdn ?: $node->hostname ?: $node->host ?: $node->public_ip));
        if ($host === '' || preg_match('/[\r\n\/@?#]/', $host)) {
            throw new RuntimeException('Node hostname is missing or invalid.');
        }
        $host = trim($host, '[]');
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $host = '['.$host.']';
        } elseif (! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && ! preg_match('/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)*[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?$/', $host)) {
            throw new RuntimeException('Node hostname is invalid. Use a DNS name or IP address without a scheme or path.');
        }

        $port = (int) ($node->behind_proxy ? $node->public_port : ($node->daemon_port ?: $node->api_port));
        if ($port < 1 || $port > 65535) {
            throw new RuntimeException('Node HTTPS port is invalid.');
        }
        if (! $node->credential_encrypted) {
            throw new RuntimeException('Node has no stored bearer credential; unauthenticated requests are not permitted.');
        }
        $request = Http::baseUrl('https://'.$host.':'.$port)
            ->acceptJson()
            ->timeout(60)
            ->connectTimeout(10)
            ->withOptions(['verify' => $this->verificationBundle($node), 'allow_redirects' => false])
            ->withHeaders(['User-Agent' => 'Snck-Laravel-Panel/1.0'])
            ->withToken(Crypt::decryptString($node->credential_encrypted));

        if ($node->access_client_id && $node->access_client_secret_encrypted) {
            $request->withHeaders([
                'CF-Access-Client-Id' => $node->access_client_id,
                'CF-Access-Client-Secret' => Crypt::decryptString($node->access_client_secret_encrypted),
            ]);
        }

        return $request;
    }

    private function jsonRequest(Response $response, string $operation): array
    {
        if ($response->failed()) {
            throw new RuntimeException("{$operation} failed with HTTP {$response->status()}.");
        }
        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException("{$operation} returned invalid JSON.");
        }

        return $payload;
    }

    private function download(PendingRequest $request, string $path, array $query, string $operation): string
    {
        $directory = storage_path('app/private/node-downloads');
        if (is_link($directory)) {
            throw new RuntimeException('Node download directory may not be a symbolic link.');
        }
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create private node download directory.');
        }
        @chmod($directory, 0700);
        $target = tempnam($directory, 'download-');
        if ($target === false) {
            throw new RuntimeException('Unable to allocate a private download file.');
        }
        chmod($target, 0600);
        try {
            $response = $request->withOptions([
                'sink' => $target,
                'progress' => static function (int $downloadTotal, int $downloaded): void {
                    if ($downloadTotal > self::MAX_DOWNLOAD_BYTES || $downloaded > self::MAX_DOWNLOAD_BYTES) {
                        throw new RuntimeException('Node download exceeds the 512 MiB limit.');
                    }
                },
            ])->get($path, $query);
            if ($response->failed()) {
                throw new RuntimeException("{$operation} failed with HTTP {$response->status()}.");
            }
            if (filesize($target) > self::MAX_DOWNLOAD_BYTES) {
                throw new RuntimeException('Node download exceeds the 512 MiB limit.');
            }

            return $target;
        } catch (\Throwable $exception) {
            @unlink($target);
            throw $exception;
        }
    }

    private function validateFilePayload(string $operation, array $payload): void
    {
        $paths = match ($operation) {
            'files/list', 'files/read', 'files/write', 'files/write-base64', 'files/write-chunk', 'files/mkdir', 'files/unzip' => ['path'],
            'files/rename' => ['oldPath', 'newPath'],
            'files/delete' => ['paths'],
            'files/zip' => ['fileNames'],
            'files/replace-batch' => ['files'],
        };
        foreach ($paths as $key) {
            if (! array_key_exists($key, $payload)) {
                throw new RuntimeException("Daemon file operation {$operation} requires {$key}.");
            }
            if (str_ends_with($key, 'Path') || $key === 'path') {
                $this->safeRelativePath((string) $payload[$key]);
            }
        }
    }

    private function safeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        if ($path === '' || str_starts_with($path, '/') || str_contains($path, "\0") || preg_match('~(^|/)\.\.(/|$)~', $path)) {
            throw new RuntimeException('File paths must remain relative to the server directory.');
        }

        return $path;
    }

    private function safeBackupFilename(string $filename): string
    {
        if (! preg_match('/^backup-[a-zA-Z0-9_.-]{1,180}\.tar\.gz$/', $filename)) {
            throw new RuntimeException('Invalid backup filename.');
        }

        return rawurlencode($filename);
    }

    private function safeServerId(string $id): string
    {
        if (! preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $id)) {
            throw new RuntimeException('Invalid server identifier.');
        }

        return rawurlencode($id);
    }

    private function verificationBundle(Node $node): bool|string
    {
        $pem = $node->custom_ca_pem;
        if (! $pem) {
            return true;
        }
        if (! str_contains($pem, '-----BEGIN CERTIFICATE-----') || ! @openssl_x509_read($pem)) {
            throw new RuntimeException('The node custom CA must contain a valid PEM X.509 certificate.');
        }

        $directory = storage_path('framework/node-ca');
        if (is_link($directory)) {
            throw new RuntimeException('The custom-CA directory may not be a symbolic link.');
        }
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create the custom-CA directory.');
        }
        @chmod($directory, 0700);

        $systemBundle = openssl_get_cert_locations()['default_cert_file'] ?? '/etc/ssl/certs/ca-certificates.crt';
        $systemPem = is_file($systemBundle) && is_readable($systemBundle) ? file_get_contents($systemBundle) : '';
        $contents = ($systemPem ?: '')."\n".$pem."\n";
        $path = $directory.'/'.hash('sha256', $contents).'.pem';
        if (is_link($path)) {
            throw new RuntimeException('The node custom-CA file may not be a symbolic link.');
        }
        if (! is_file($path)) {
            $temporary = tempnam($directory, '.ca-');
            if ($temporary === false) {
                throw new RuntimeException('Unable to create a temporary CA bundle.');
            }
            try {
                if (file_put_contents($temporary, $contents, LOCK_EX) === false) {
                    throw new RuntimeException('Unable to write the node CA bundle.');
                }
                chmod($temporary, 0600);
                if (! rename($temporary, $path)) {
                    throw new RuntimeException('Unable to install the node CA bundle.');
                }
            } finally {
                if (is_file($temporary)) {
                    @unlink($temporary);
                }
            }
        }

        return $path;
    }
}
