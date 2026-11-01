<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use ZipArchive;

class PluginManagerService
{
    public function __construct(private readonly NodeClient $client) {}

    public function installed(Server $server): array
    {
        $entries = $this->client->serverRequest($server->node, $server->id, 'POST', 'files/list', ['path' => 'plugins']);
        $items = array_values(array_filter(is_array($entries) ? $entries : [], fn ($entry): bool => is_array($entry) && ($entry['isFile'] ?? false) && preg_match('/\.jar$/i', (string) ($entry['name'] ?? ''))));

        return ['items' => array_map(fn (array $entry): array => ['filename' => basename((string) $entry['name']), 'size' => (int) ($entry['size'] ?? 0)], $items)];
    }

    public function remove(Server $server, string $filename): array
    {
        if (! preg_match('/^[a-zA-Z0-9._-]+\.jar$/i', $filename)) {
            throw new RuntimeException('Invalid plugin filename.');
        }
        $this->client->serverRequest($server->node, $server->id, 'POST', 'files/delete', ['paths' => ['plugins/'.$filename]]);

        return ['success' => true, 'filename' => $filename];
    }

    public function install(Server $server, string $kind, array $input): array
    {
        if (! in_array($kind, ['plugin', 'mod'], true)) {
            throw new RuntimeException('Unsupported marketplace type.');
        }
        $project = (string) ($input['projectId'] ?? $input['pluginId'] ?? '');
        if (! preg_match('/^[A-Za-z0-9_-]{2,80}$/', $project)) {
            throw new RuntimeException('Invalid Modrinth project ID.');
        }
        $gameVersion = trim((string) ($input['gameVersion'] ?? $server->version ?? ''));
        $loader = strtolower(trim((string) ($input['loader'] ?? $server->type ?? '')));
        $versions = Http::timeout(20)->get('https://api.modrinth.com/v2/project/'.rawurlencode($project).'/version', ['game_versions' => json_encode(array_values(array_filter([$gameVersion]))), 'loaders' => json_encode(array_values(array_filter([$loader])))]);
        if ($versions->failed() || ! is_array($versions->json()) || ! $versions->json()) {
            throw new RuntimeException('No compatible Modrinth release was found.');
        }
        $version = $versions->json()[0];
        $file = collect($version['files'] ?? [])->first(fn ($row): bool => is_array($row) && str_ends_with(strtolower((string) ($row['filename'] ?? '')), '.jar'));
        if (! $file || ! filter_var($file['url'] ?? null, FILTER_VALIDATE_URL) || ! str_starts_with($file['url'], 'https://cdn.modrinth.com/')) {
            throw new RuntimeException('Modrinth did not return a safe JAR download.');
        }
        $download = Http::timeout(60)->get($file['url']);
        if ($download->failed()) {
            throw new RuntimeException('Marketplace download failed.');
        }
        $bytes = $download->body();
        if (! is_string($bytes) || strlen($bytes) > 64 * 1024 * 1024) {
            throw new RuntimeException('Marketplace file exceeds the 64 MiB limit.');
        }
        $filename = basename((string) ($file['filename'] ?? ($project.'.jar')));
        if (! preg_match('/^[a-zA-Z0-9._-]+\.jar$/i', $filename)) {
            throw new RuntimeException('Marketplace returned an unsafe filename.');
        }
        $folder = $kind === 'plugin' ? 'plugins' : 'mods';
        $this->client->serverRequest($server->node, $server->id, 'POST', 'files/write-base64', ['path' => $folder.'/'.$filename, 'content' => base64_encode($bytes)]);

        return ['success' => true, 'filename' => $filename, 'version' => $version['version_number'] ?? null];
    }

    public function importModpack(Server $server, UploadedFile $archive, bool $confirmReplace): array
    {
        $name = strtolower($archive->getClientOriginalName());
        if (! str_ends_with($name, '.zip') && ! str_ends_with($name, '.mrpack')) {
            throw new RuntimeException('Only ZIP and MRPACK archives are supported.');
        }
        if ($archive->getSize() > 512 * 1024 * 1024) {
            throw new RuntimeException('Modpack archive exceeds the 512 MiB limit.');
        }
        $zip = new ZipArchive;
        if ($zip->open($archive->getRealPath()) !== true) {
            throw new RuntimeException('Invalid modpack archive.');
        }
        $files = [];
        $total = 0;
        $prefix = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->statIndex($i, ZipArchive::FL_UNCHANGED);
            $path = str_replace('\\', '/', (string) ($entry['name'] ?? ''));
            if ($path === '' || str_ends_with($path, '/') || str_starts_with($path, '/') || str_contains($path, "\0") || preg_match('~(^|/)\.\.(?:/|$)~', $path)) {
                throw new RuntimeException('Archive contains an unsafe path.');
            } if ((int) ($entry['size'] ?? 0) > 128 * 1024 * 1024 || ($total += (int) ($entry['size'] ?? 0)) > 512 * 1024 * 1024) {
                throw new RuntimeException('Expanded modpack exceeds the 512 MiB limit.');
            } if ($name === 'modpack.mrpack' && $path === 'modrinth.index.json') {
                continue;
            } if ($prefix === null && str_starts_with($path, 'overrides/')) {
                $prefix = 'overrides/';
            } if ($prefix === 'overrides/' && ! str_starts_with($path, 'overrides/')) {
                continue;
            } $content = $zip->getFromIndex($i);
            if ($content === false) {
                throw new RuntimeException('Unable to read archive entry.');
            } $relative = $prefix ? substr($path, strlen($prefix)) : $path;
            if ($relative !== '') {
                $files[] = ['path' => $relative, 'content' => base64_encode($content)];
            }
        }
        $zip->close();
        if (! $files) {
            throw new RuntimeException('Modpack contains no installable files.');
        }
        $status = $this->client->serverStatus($server->node, $server->id);
        if (($status['State']['Running'] ?? $status['state']['running'] ?? false) === true) {
            throw new RuntimeException('Stop the server before importing a modpack.');
        }
        $result = $this->client->serverRequest($server->node, $server->id, 'POST', 'files/replace-batch', ['files' => $files, 'confirmReplace' => $confirmReplace]);

        return ['success' => true, 'entries' => count($files), 'daemon' => $result];
    }
}
