<?php

namespace App\Livewire;

use App\Models\Backup;
use App\Models\Server;
use App\Models\User;
use App\Services\NodeClient;
use App\Services\PlayitService;
use App\Services\PluginManagerService;
use App\Services\SftpCredentialService;
use App\Services\ThemeCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class ServerDetail extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $serverId;

    public string $tab = 'overview';

    public string $filePath = '.';

    public string $fileContent = '';

    public string $uploadPath = '';

    public string $renamePath = '';

    public string $archiveName = 'archive.zip';

    public array $selectedFiles = [];

    public $uploadFile = null;

    public string $commandText = '';

    public string $subuserEmail = '';

    public array $permissions = ['files'];

    public array $files = [];

    public array $backups = [];

    public array $subusers = [];

    public array $players = [];

    public ?int $maxPlayers = null;

    public string $propertiesContent = '';

    public string $logFilter = '';

    public string $logs = '';

    public array $installedPlugins = [];

    public array $playit = [];

    public array $sftp = [];

    public string $marketplaceId = '';

    public string $marketplaceKind = 'plugin';

    public string $sftpPassword = '';

    public string $notice = '';

    public string $noticeType = 'success';

    public function mount(string $server): void
    {
        $this->serverId = $server;
        $this->authorizedServer('view');
        $this->refreshTabData(app(NodeClient::class));
    }

    public function setTab(string $tab, NodeClient $client): void
    {
        $server = $this->server();
        $abilities = ['overview' => 'view', 'console' => 'command', 'logs' => 'logs', 'players' => 'view', 'properties' => 'properties', 'files' => 'files', 'backups' => 'backup', 'subusers' => 'manageSubusers', 'sftp' => 'manageSubusers', 'plugins' => 'plugins', 'mods' => 'mods', 'modpacks' => 'mods', 'playit' => 'view'];
        abort_unless(isset($abilities[$tab]), 404);
        $this->authorize($abilities[$tab], $server);
        $this->tab = $tab;
        $this->refreshTabData($client);
    }

    public function refreshIntegrations(PluginManagerService $plugins, PlayitService $playit, SftpCredentialService $sftp): void
    {
        $server = $this->server();
        if (in_array($this->tab, ['plugins', 'mods'], true)) {
            $this->installedPlugins = $plugins->installed($server)['items'] ?? [];
        }
        if ($this->tab === 'playit') {
            $this->playit = $playit->status($server);
        }
        if ($this->tab === 'sftp') {
            $this->sftp = $sftp->get($server) ?? [];
        }
    }

    public function installMarketplace(PluginManagerService $plugins): void
    {
        $kind = $this->marketplaceKind === 'mod' ? 'mod' : 'plugin';
        $server = $this->authorizedServer($kind === 'plugin' ? 'plugins' : 'mods');
        $this->validate(['marketplaceId' => ['required', 'regex:/^[A-Za-z0-9_-]{2,80}$/']]);
        try {
            $plugins->install($server, $kind, ['projectId' => $this->marketplaceId]);
            $this->marketplaceId = '';
            $this->installedPlugins = $plugins->installed($server)['items'] ?? [];
            $this->notice = ucfirst($kind).' installed.';
        } catch (Throwable $exception) {
            $this->failure($exception->getMessage(), $exception);
        }
    }

    public function removePlugin(string $filename, PluginManagerService $plugins): void
    {
        $plugins->remove($this->authorizedServer('plugins'), $filename);
        $this->installedPlugins = $plugins->installed($this->server())['items'] ?? [];
    }

    public function startPlayit(PlayitService $service): void
    {
        $service->start($this->authorizedServer('view'));
        $this->playit = $service->status($this->server());
    }

    public function stopPlayit(PlayitService $service): void
    {
        $service->stop($this->authorizedServer('view'));
        $this->playit = $service->status($this->server());
    }

    public function resetPlayit(PlayitService $service): void
    {
        $service->reset($this->authorizedServer('view'));
        $this->playit = $service->status($this->server());
    }

    public function createSftp(SftpCredentialService $service): void
    {
        $result = $service->create($this->authorizedServer('manageSubusers'));
        $this->sftp = $result;
        $this->sftpPassword = (string) ($result['password'] ?? '');
    }

    public function resetSftp(SftpCredentialService $service): void
    {
        $result = $service->reset($this->authorizedServer('manageSubusers'));
        $this->sftp = $result;
        $this->sftpPassword = (string) ($result['password'] ?? '');
    }

    public function lifecycle(string $action, NodeClient $client): void
    {
        $server = $this->server();
        abort_unless(in_array($action, ['start', 'stop', 'restart', 'kill'], true), 422);
        abort_if($server->suspended && ! auth()->user()->isStaff(), 403, 'This server is suspended.');
        $this->authorize($action, $server);
        try {
            $client->serverAction($server->node, $server->id, $action);
            $inspection = $client->serverStatus($server->node, $server->id);
            $status = $this->mapStatus($inspection);
            $server->update(['status' => $status]);
            $this->notice = 'Action sent. Observed status: '.$status.'.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('Server action failed.', $exception);
        }
    }

    public function refreshLogs(NodeClient $client): void
    {
        $server = $this->authorizedServer('logs');
        try {
            $this->logs = $client->serverLogs($server->node, $server->id);
        } catch (Throwable $exception) {
            $this->failure('Logs are currently unavailable.', $exception);
        }
    }

    public function refreshPlayers(NodeClient $client): void
    {
        $server = $this->authorizedServer('view');
        try {
            $result = $client->serverRequest($server->node, $server->id, 'POST', 'command', ['command' => 'list']);
            $this->parsePlayers((string) ($result['output'] ?? $result['content'] ?? ''));
        } catch (Throwable $exception) {
            $this->failure('Player list is currently unavailable.', $exception);
        }
    }

    public function loadProperties(NodeClient $client): void
    {
        $server = $this->authorizedServer('properties');
        try {
            $result = $client->serverRequest($server->node, $server->id, 'POST', 'files/read', ['path' => 'server.properties']);
            $this->propertiesContent = (string) ($result['content'] ?? '');
            $this->notice = 'server.properties loaded.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('server.properties is not available. Start the server once to generate it.', $exception);
        }
    }

    public function saveProperties(NodeClient $client): void
    {
        $server = $this->authorizedServer('properties');
        $this->validate(['propertiesContent' => ['required', 'string', 'max:1048576']]);
        try {
            $client->serverRequest($server->node, $server->id, 'POST', 'files/write', ['path' => 'server.properties', 'content' => $this->propertiesContent]);
            $this->notice = 'server.properties saved.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('server.properties could not be saved.', $exception);
        }
    }

    public function sendCommand(NodeClient $client): void
    {
        $server = $this->authorizedServer('command');
        abort_if($server->suspended && ! auth()->user()->isStaff(), 403);
        $data = $this->validate(['commandText' => ['required', 'string', 'min:1', 'max:4096']]);
        try {
            $client->serverRequest($server->node, $server->id, 'POST', 'command', ['command' => $data['commandText']]);
            $this->commandText = '';
            $this->notice = 'Console command sent.';
            $this->noticeType = 'success';
            $this->refreshLogs($client);
        } catch (Throwable $exception) {
            $this->failure('Console command failed.', $exception);
        }
    }

    public function loadFiles(NodeClient $client): void
    {
        $server = $this->authorizedServer('files');
        $path = $this->safePath($this->filePath);
        try {
            $result = $client->serverRequest($server->node, $server->id, 'POST', 'files/list', ['path' => $path]);
            $items = $result['items'] ?? $result['files'] ?? $result;
            $this->files = is_array($items) ? $items : [];
            $this->notice = 'File list refreshed.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('Files are currently unavailable.', $exception);
        }
    }

    public function readFile(NodeClient $client): void
    {
        $server = $this->authorizedServer('files');
        $path = $this->safePath($this->filePath);
        try {
            $result = $client->serverRequest($server->node, $server->id, 'POST', 'files/read', ['path' => $path]);
            $this->fileContent = (string) ($result['content'] ?? '');
            $this->notice = 'File loaded for editing.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('File could not be read.', $exception);
        }
    }

    public function selectFile(string $name): void
    {
        $this->authorizedServer('files');
        $name = $this->safePath($name);
        $base = rtrim(str_replace('\\', '/', trim($this->filePath)), '/.');
        $this->filePath = $this->safePath($base === '' ? $name : $base.'/'.$name);
    }

    public function deleteFile(string $name, NodeClient $client): void
    {
        $server = $this->authorizedServer('files');
        $path = $this->safePath($name);
        try {
            $client->serverRequest($server->node, $server->id, 'POST', 'files/delete', ['paths' => [$path]]);
            $this->loadFiles($client);
            $this->notice = 'File or directory deleted.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('File or directory could not be deleted.', $exception);
        }
    }

    public function renameFile(NodeClient $client): void
    {
        $server = $this->authorizedServer('files');
        $oldPath = $this->safePath($this->filePath);
        $newPath = $this->safePath($this->renamePath);
        try {
            $client->serverRequest($server->node, $server->id, 'POST', 'files/rename', ['oldPath' => $oldPath, 'newPath' => $newPath]);
            $this->filePath = $newPath;
            $this->renamePath = '';
            $this->loadFiles($client);
            $this->notice = 'File or directory renamed.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('Rename failed.', $exception);
        }
    }

    public function zipSelected(NodeClient $client): void
    {
        $server = $this->authorizedServer('files');
        abort_unless(preg_match('/^[a-zA-Z0-9._-]{1,120}\.zip$/i', $this->archiveName), 422, 'Archive name must be a simple .zip filename.');
        abort_if(! $this->selectedFiles || count($this->selectedFiles) > 1000, 422, 'Select between 1 and 1,000 directory entries.');
        foreach ($this->selectedFiles as $name) {
            abort_unless(is_string($name) && ! in_array($name, ['', '.', '..'], true) && ! str_contains($name, '/') && ! str_contains($name, '\\'), 422);
        }
        try {
            $result = $client->serverRequest($server->node, $server->id, 'POST', 'files/zip', ['dirPath' => $this->safePath($this->filePath), 'fileNames' => array_values($this->selectedFiles), 'outputName' => $this->archiveName]);
            $this->selectedFiles = [];
            $this->loadFiles($client);
            $this->notice = 'Archive created: '.($result['filename'] ?? $this->archiveName);
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('Archive creation failed.', $exception);
        }
    }

    public function unzipFile(NodeClient $client): void
    {
        $server = $this->authorizedServer('files');
        $path = $this->safePath($this->filePath);
        try {
            $client->serverRequest($server->node, $server->id, 'POST', 'files/unzip', ['path' => $path]);
            $this->loadFiles($client);
            $this->notice = 'Archive extracted.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('Archive extraction failed.', $exception);
        }
    }

    public function uploadFileToServer(NodeClient $client): void
    {
        $server = $this->authorizedServer('files');
        $this->validate(['uploadFile' => ['required', 'file', 'max:51200']]);
        $original = str_replace('\\', '/', $this->uploadFile->getClientOriginalName());
        $filename = basename($original);
        $target = $this->safePath($this->uploadPath !== '' ? $this->uploadPath : $filename);
        $size = (int) $this->uploadFile->getSize();
        $stream = fopen($this->uploadFile->getRealPath(), 'rb');
        abort_unless(is_resource($stream), 422, 'Upload temporary file is unavailable.');
        $offset = 0;
        $uploadId = (string) Str::uuid();
        try {
            do {
                $chunk = fread($stream, 4 * 1024 * 1024);
                if (! is_string($chunk)) {
                    throw new \RuntimeException('Upload chunk could not be read.');
                }
                $length = strlen($chunk);
                $result = $client->serverRequest($server->node, $server->id, 'POST', 'files/write-chunk', [
                    'uploadId' => $uploadId, 'path' => $target, 'offset' => $offset, 'totalSize' => $size, 'content' => base64_encode($chunk),
                ]);
                $offset += $length;
                if ((int) ($result['offset'] ?? -1) !== $offset || (bool) ($result['complete'] ?? false) !== ($offset === $size)) {
                    throw new \RuntimeException('Node did not confirm the expected upload chunk state.');
                }
            } while ($offset < $size);
        } catch (Throwable $exception) {
            $this->failure('Upload failed.', $exception);

            return;
        } finally {
            fclose($stream);
        }
        $this->uploadFile = null;
        $this->uploadPath = '';
        $this->filePath = $target;
        $this->loadFiles($client);
        $this->notice = 'File uploaded.';
        $this->noticeType = 'success';
    }

    public function saveFile(NodeClient $client): void
    {
        $server = $this->authorizedServer('files');
        $path = $this->safePath($this->filePath);
        $this->validate(['fileContent' => ['nullable', 'string', 'max:10485760']]);
        try {
            $client->serverRequest($server->node, $server->id, 'POST', 'files/write', ['path' => $path, 'content' => $this->fileContent]);
            $this->notice = 'File saved.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('File could not be saved.', $exception);
        }
    }

    public function createDirectory(NodeClient $client): void
    {
        $server = $this->authorizedServer('files');
        $path = $this->safePath($this->filePath);
        try {
            $client->serverRequest($server->node, $server->id, 'POST', 'files/mkdir', ['path' => $path]);
            $this->loadFiles($client);
        } catch (Throwable $exception) {
            $this->failure('Directory could not be created.', $exception);
        }
    }

    public function createBackup(NodeClient $client): void
    {
        $server = $this->authorizedServer('backup');
        try {
            $result = $client->createBackup($server->node, $server->id);
            $filename = (string) ($result['filename'] ?? '');
            abort_unless(preg_match('/^backup-[a-zA-Z0-9_.-]{1,180}\.tar\.gz$/', $filename), 502, 'Node returned an invalid backup name.');
            Backup::query()->updateOrCreate(['server_id' => $server->id, 'filename' => $filename], [
                'id' => (string) Str::uuid(), 'size_bytes' => (int) ($result['sizeBytes'] ?? 0),
                'checksum' => $result['checksum'] ?? null, 'status' => 'completed', 'progress' => 100, 'completed_at' => now(),
            ]);
            $this->refreshBackups($client);
            $this->notice = 'Backup created.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('Backup creation failed.', $exception);
        }
    }

    public function deleteBackup(string $filename, NodeClient $client): void
    {
        $server = $this->authorizedServer('backup');
        abort_unless(preg_match('/^backup-[a-zA-Z0-9_.-]{1,180}\.tar\.gz$/', $filename), 422);
        try {
            $client->deleteBackup($server->node, $server->id, $filename);
            Backup::query()->where('server_id', $server->id)->where('filename', $filename)->delete();
            $this->refreshBackups($client);
            $this->notice = 'Backup deleted.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->failure('Backup deletion failed.', $exception);
        }
    }

    public function addSubuser(): void
    {
        $server = $this->authorizedServer('manageSubusers');
        $data = $this->validate([
            'subuserEmail' => ['required', 'email', 'max:255', 'exists:users,email'],
            'permissions' => ['required', 'array', 'min:1', 'max:10'],
            'permissions.*' => ['string', 'in:start,stop,restart,kill,files,backup,plugins,mods,properties,settings'],
        ]);
        $user = User::query()->where('email', $data['subuserEmail'])->firstOrFail();
        abort_if((string) $user->id === (string) $server->owner_id, 422, 'The owner already has full access.');
        $server->subusers()->syncWithoutDetaching([$user->id => ['permissions' => array_values(array_unique($data['permissions'])), 'added_by' => auth()->id()]]);
        $this->subuserEmail = '';
        $this->refreshSubusers();
        $this->notice = 'Sub-user access saved.';
        $this->noticeType = 'success';
    }

    public function removeSubuser(string $userId): void
    {
        $server = $this->authorizedServer('manageSubusers');
        abort_unless($server->subusers()->whereKey($userId)->exists(), 404);
        $server->subusers()->detach($userId);
        $this->refreshSubusers();
        $this->notice = 'Sub-user access removed.';
        $this->noticeType = 'success';
    }

    public function render(ThemeCatalog $themes): View
    {
        $server = $this->authorizedServer('view');
        $serverLayout = $themes->activeTheme()['layout'];

        return view('livewire.servers.detail', compact('server', 'serverLayout'))
            ->layout('layouts.app', ['title' => $server->name]);
    }

    private function server(): Server
    {
        return Server::query()->with(['node', 'owner', 'subusers'])->findOrFail($this->serverId);
    }

    private function authorizedServer(string $ability): Server
    {
        $server = $this->server();
        $this->authorize($ability, $server);

        return $server;
    }

    private function refreshTabData(NodeClient $client): void
    {
        if ($this->tab === 'logs' || $this->tab === 'console') {
            $this->refreshLogs($client);
        }
        if ($this->tab === 'players') {
            $this->refreshPlayers($client);
        }
        if ($this->tab === 'properties') {
            $this->loadProperties($client);
        }
        if ($this->tab === 'files') {
            $this->loadFiles($client);
        }
        if ($this->tab === 'backups') {
            $this->refreshBackups($client);
        }
        if ($this->tab === 'subusers') {
            $this->refreshSubusers();
        }
        if (in_array($this->tab, ['sftp', 'plugins', 'mods', 'playit'], true)) {
            $this->refreshIntegrations(app(PluginManagerService::class), app(PlayitService::class), app(SftpCredentialService::class));
        }
    }

    private function parsePlayers(string $output): void
    {
        $this->players = [];
        $this->maxPlayers = null;
        if (preg_match('/There are\s+(\d+)\s+of\s+a\s+max(?:imum)?\s+of\s+(\d+)\s+players?\s+online:?\s*(.*)$/im', $output, $match)) {
            $this->maxPlayers = (int) $match[2];
            $names = trim($match[3]);
            $this->players = $names === '' ? [] : array_values(array_filter(array_map('trim', preg_split('/[,、]/', $names) ?: [])));
        }
    }

    private function refreshBackups(NodeClient $client): void
    {
        $server = $this->authorizedServer('backup');
        try {
            $this->backups = $client->listBackups($server->node, $server->id);
        } catch (Throwable $exception) {
            $this->failure('Backups are currently unavailable.', $exception);
        }
    }

    private function refreshSubusers(): void
    {
        $server = $this->authorizedServer('manageSubusers');
        $this->subusers = $server->subusers()->get(['users.id', 'users.username', 'users.email'])->map(function (User $user): array {
            $permissions = $user->pivot->permissions;
            if (is_string($permissions)) {
                $permissions = json_decode($permissions, true) ?: [];
            }

            return ['id' => $user->id, 'username' => $user->username, 'email' => $user->email, 'permissions' => is_array($permissions) ? $permissions : []];
        })->all();
    }

    private function safePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        abort_if($path === '' || str_starts_with($path, '/') || str_contains($path, "\0") || preg_match('~(^|/)\.\.(/|$)~', $path), 422, 'A safe server-relative path is required.');

        return $path;
    }

    private function mapStatus(array $inspection): string
    {
        $state = $inspection['State'] ?? $inspection['state'] ?? null;
        if (! is_array($state)) {
            return 'unknown';
        }

        return match (strtolower((string) ($state['Status'] ?? $state['status'] ?? ''))) {
            'running' => 'running', 'created','restarting' => 'installing', 'exited','paused','dead','removing' => strtolower((string) ($state['Status'] ?? $state['status'])), default => 'unknown',
        };
    }

    private function failure(string $message, Throwable $exception): void
    {
        Log::warning('Livewire server operation failed.', ['server_id' => $this->serverId, 'exception' => $exception::class]);
        $this->notice = $message;
        $this->noticeType = 'error';
    }
}
