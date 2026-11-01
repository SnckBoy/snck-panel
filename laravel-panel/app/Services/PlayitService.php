<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use RuntimeException;

class PlayitService
{
    private const AGENT_URL = 'https://github.com/playit-cloud/playit-agent/releases/download/v0.15.26/playit-linux-amd64';

    public function status(Server $server): array
    {
        $name = $this->processName($server);
        $processes = $this->run(['npx', 'pm2', 'jlist'], allowFailure: true);
        $status = 'stopped';
        foreach ($this->decodeProcessList($processes['stdout']) as $process) {
            if (($process['name'] ?? null) === $name && ($process['pm2_env']['status'] ?? null) === 'online') {
                $status = 'running';
                break;
            }
        }
        if ($status !== 'running') return ['status' => 'stopped', 'claimLink' => null, 'logs' => ''];

        $logs = $this->run(['npx', 'pm2', 'logs', $name, '--nostream', '--lines', '100'], allowFailure: true)['stdout'];
        $logs = preg_replace('/\x1b\[[0-9;]*[a-zA-Z]|\x1b./', '', $logs) ?? $logs;
        preg_match_all('~https://playit\.gg/claim/[a-zA-Z0-9]+~', $logs, $matches);
        $links = $matches[0] ?? [];
        return ['status' => $status, 'claimLink' => $links ? end($links) : null, 'logs' => implode("\n", array_slice(explode("\n", $logs), -50))];
    }

    public function start(Server $server): array
    {
        $paths = $this->paths($server);
        File::ensureDirectoryExists($paths['directory']);
        $this->run(['npx', 'pm2', 'delete', $paths['process']], allowFailure: true);
        $this->run(['npx', 'pm2', 'flush', $paths['process']], allowFailure: true);
        if (!is_file($paths['binary'])) {
            $response = Http::timeout(30)->get(self::AGENT_URL);
            if (!$response->successful() || $response->body() === '') throw new RuntimeException('Playit agent download failed.');
            if (file_put_contents($paths['binary'], $response->body(), LOCK_EX) === false) throw new RuntimeException('Playit agent could not be saved.');
            chmod($paths['binary'], 0750);
        }
        $this->run(['npx', 'pm2', 'start', $paths['binary'], '--name', $paths['process'], '--', '-s', '--secret_path', $paths['secret']], allowFailure: false);
        $this->run(['npx', 'pm2', 'save'], allowFailure: false);
        return ['success' => true];
    }

    public function stop(Server $server): array
    {
        $this->run(['npx', 'pm2', 'delete', $this->processName($server)], allowFailure: true);
        $this->run(['npx', 'pm2', 'save'], allowFailure: true);
        return ['success' => true];
    }

    public function reset(Server $server): array
    {
        $paths = $this->paths($server);
        $this->run(['npx', 'pm2', 'delete', $paths['process']], allowFailure: true);
        $this->run(['npx', 'pm2', 'flush', $paths['process']], allowFailure: true);
        if (is_file($paths['secret'])) unlink($paths['secret']);
        $this->run(['npx', 'pm2', 'save'], allowFailure: true);
        return ['success' => true];
    }

    private function paths(Server $server): array
    {
        $directory = storage_path('app/servers/'.$server->getKey().'/playit');
        return ['directory' => $directory, 'binary' => $directory.'/playit-agent', 'secret' => $directory.'/playit.toml', 'process' => $this->processName($server)];
    }

    private function processName(Server $server): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $server->name) ?: $server->getKey();
        return 'playit_'.$safeName;
    }

    private function decodeProcessList(string $stdout): array
    {
        $start = strpos($stdout, '[');
        $end = strrpos($stdout, ']');
        if ($start === false || $end === false || $end < $start) return [];
        $decoded = json_decode(substr($stdout, $start, $end - $start + 1), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function run(array $command, bool $allowFailure): array
    {
        $process = new Process($command, base_path());
        $process->setTimeout(60);
        $process->run();
        if (!$allowFailure && !$process->isSuccessful()) {
            throw new RuntimeException('Playit process command failed.');
        }
        return ['stdout' => $process->getOutput(), 'stderr' => $process->getErrorOutput(), 'code' => $process->getExitCode()];
    }
}
