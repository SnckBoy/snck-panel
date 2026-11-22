<?php

namespace Pterodactyl\Services\Nodes;

use Pterodactyl\Models\Node;
use Pterodactyl\Models\Location;
use Illuminate\Support\Facades\Log;

class LocalNodeProvisioningService
{
    private const SETUP_HELPER = '/usr/local/sbin/snck-local-node-bootstrap';
    private const CONFIG_FILE = '/etc/pterodactyl/config.yml';
    private const API_PORT = 8080;
    private const SFTP_PORT = 2022;

    public function __construct(
        private NodeCreationService $creationService,
    ) {
    }

    /**
     * Create the panel record and start the privileged, asynchronous host bootstrap.
     *
     * The method deliberately returns a pending node, not an online node. Online state
     * is established only by an authenticated Wings health request afterwards.
     *
     * @throws \RuntimeException when the host is not safe or ready for provisioning
     */
    public function provision(string $host, string $scheme): Node
    {
        $host = $this->normalizeHost($host);
        $scheme = strtolower($scheme);
        $this->validatePanelEndpoint($host, $scheme);
        $this->validateHost($host, $scheme);

        $existing = Node::query()
            ->whereRaw('LOWER(fqdn) = ?', [$host])
            ->where('daemonListen', self::API_PORT)
            ->first();
        if ($existing) {
            throw new \RuntimeException('A node already exists for this hostname and API port.');
        }

        if (is_file(self::CONFIG_FILE)) {
            throw new \RuntimeException('An existing Wings configuration was found. Automatic setup will not overwrite it.');
        }

        foreach ([self::API_PORT, self::SFTP_PORT] as $port) {
            if (!$this->isPortAvailable($port)) {
                throw new \RuntimeException("TCP port {$port} is already in use.");
            }
        }

        if (!is_executable(self::SETUP_HELPER) || !is_file('/etc/sudoers.d/snck-local-node')) {
            throw new \RuntimeException('The local Wings setup helper is not installed. Run the panel updater first.');
        }

        $resources = $this->resources();
        $location = Location::query()->firstOrCreate(
            ['short' => 'local'],
            ['long' => 'Local node on the Panel host']
        );

        $node = $this->creationService->handle([
            'name' => 'Local Node',
            'description' => 'Automatically provisioned on the Panel host.',
            'location_id' => $location->id,
            'public' => true,
            'fqdn' => $host,
            'scheme' => $scheme,
            'behind_proxy' => false,
            'memory' => $resources['memory'],
            'memory_overallocate' => 0,
            'disk' => $resources['disk'],
            'disk_overallocate' => 0,
            'daemonBase' => '/var/lib/pterodactyl/volumes',
            'daemonSFTP' => self::SFTP_PORT,
            'daemonListen' => self::API_PORT,
            'maintenance_mode' => false,
            'upload_size' => 10240,
        ]);

        try {
            $this->startBootstrap($node);
        } catch (\RuntimeException $exception) {
            $node->delete();
            throw $exception;
        }

        return $node;
    }

    /**
     * Return conservative host capacity values for the node record.
     *
     * @return array{memory: int, disk: int, cpu: int}
     */
    public function resources(): array
    {
        $memory = 512;
        $meminfo = @file_get_contents('/proc/meminfo');
        if (is_string($meminfo) && preg_match('/^MemTotal:\s+(\d+)/m', $meminfo, $matches)) {
            $memory = max(512, (int) floor(((int) $matches[1]) / 1024) - 2048);
        }

        $diskBytes = @disk_free_space('/var/lib/pterodactyl') ?: @disk_free_space('/');
        $disk = max(1024, (int) floor(((float) $diskBytes) / 1048576) - 10240);
        $cpu = 1;
        $cpuInfo = @file_get_contents('/proc/cpuinfo');
        if (is_string($cpuInfo)) {
            $cpu = max(1, preg_match_all('/^processor\s*:/m', $cpuInfo));
        }

        return ['memory' => $memory, 'disk' => $disk, 'cpu' => $cpu];
    }

    public function isPrivateHost(string $hostname): bool
    {
        $resolved = filter_var($hostname, FILTER_VALIDATE_IP) ? $hostname : gethostbyname($hostname);
        $ip = filter_var($resolved, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if (!$ip) {
            return false;
        }

        $ipLong = ip2long($resolved);
        $isTailnet = $ipLong !== false && (($ipLong & 0xFFC00000) === ip2long('100.64.0.0'));

        return filter_var($resolved, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false || $isTailnet;
    }

    public function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host === '' || strlen($host) > 253 || !preg_match('/^[a-z0-9][a-z0-9.:-]*[a-z0-9]$/', $host)) {
            throw new \RuntimeException('The panel hostname is invalid.');
        }

        return $host;
    }

    private function validatePanelEndpoint(string $host, string $scheme): void
    {
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new \RuntimeException('The local node scheme must be HTTP or HTTPS.');
        }

        $appUrl = (string) config('app.url');
        $appScheme = strtolower((string) parse_url($appUrl, PHP_URL_SCHEME));
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        if ($appScheme !== 'https' && (!is_string($appHost) || !$this->isPrivateHost($appHost))) {
            throw new \RuntimeException('APP_URL must use HTTPS or a private LAN/Tailscale address.');
        }

        if ($scheme !== 'https' && !$this->isPrivateHost($host)) {
            throw new \RuntimeException('HTTP is allowed only for private LAN or Tailscale node addresses.');
        }
    }

    private function validateHost(string $host, string $scheme): void
    {
        if ($scheme !== 'https') {
            return;
        }

        $certificate = '/etc/letsencrypt/live/' . $host;
        if (!is_file($certificate . '/fullchain.pem') || !is_file($certificate . '/privkey.pem')) {
            throw new \RuntimeException('HTTPS is enabled but the matching Let\'s Encrypt certificate is missing.');
        }
    }

    private function isPortAvailable(int $port): bool
    {
        $socket = @stream_socket_client('tcp://127.0.0.1:' . $port, $errno, $errstr, 0.5);
        if (is_resource($socket)) {
            fclose($socket);

            return false;
        }

        return true;
    }

    private function startBootstrap(Node $node): void
    {
        $process = proc_open(
            ['/usr/bin/sudo', '-n', self::SETUP_HELPER],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        if (!is_resource($process)) {
            throw new \RuntimeException('The privileged local Wings bootstrap could not be started.');
        }

        fwrite($pipes[0], $node->id . PHP_EOL . $node->fqdn . PHP_EOL . $node->getYamlConfiguration());
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            Log::error('Local Wings bootstrap could not be queued.', [
                'node_id' => $node->id,
                'output' => trim((string) $output),
                'error' => trim((string) $errors),
            ]);
            throw new \RuntimeException('Local Wings setup could not be started. Check sudo permissions and the local-node setup log.');
        }
    }
}
