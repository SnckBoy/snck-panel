<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\Server;
use App\Services\NodeClient;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupController extends Controller
{
    private const FILENAME = '/^backup-[a-zA-Z0-9_.-]{1,180}\.tar\.gz$/';

    public function index(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeServer($request, $server);
        try {
            $remote = $client->listBackups($server->node, $server->id);
            $response = [];
            foreach ($remote as $item) {
                if (!is_array($item) || !isset($item['filename']) || !preg_match(self::FILENAME, (string) $item['filename'])) continue;
                $filename = (string) $item['filename'];
                $backup = $this->record($server, $filename, [
                    'size_bytes' => max(0, (int) ($item['size'] ?? $item['sizeBytes'] ?? 0)),
                    'status' => 'completed',
                    'progress' => 100,
                    'completed_at' => $this->date($item['createdAt'] ?? null),
                ]);
                $response[] = ['filename' => $filename, 'size_bytes' => $backup->size_bytes, 'created_at' => $backup->completed_at, 'status' => 'completed'];
            }
            return response()->json($response);
        } catch (Throwable $exception) {
            return $this->failure($exception, $server, 'backup-list');
        }
    }

    public function store(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeServer($request, $server);
        try {
            $result = $client->createBackup($server->node, $server->id);
            $filename = (string) ($result['filename'] ?? '');
            abort_unless(preg_match(self::FILENAME, $filename), 502, 'Node returned an invalid backup identifier.');
            $backup = $this->record($server, $filename, [
                'size_bytes' => max(0, (int) ($result['sizeBytes'] ?? $result['size_bytes'] ?? 0)),
                'checksum' => is_string($result['checksum'] ?? null) ? $result['checksum'] : null,
                'status' => 'completed',
                'progress' => 100,
                'completed_at' => now(),
            ]);
            return response()->json($backup, 201);
        } catch (Throwable $exception) {
            return $this->failure($exception, $server, 'backup-create');
        }
    }

    public function download(Request $request, Server $server, string $filename, NodeClient $client): StreamedResponse|JsonResponse
    {
        $this->authorizeServer($request, $server);
        abort_unless(preg_match(self::FILENAME, $filename), 422, 'Invalid backup filename.');
        try {
            $temporary = $client->downloadBackup($server->node, $server->id, $filename);
            return response()->streamDownload(function () use ($temporary): void {
                try { $handle = fopen($temporary, 'rb'); if ($handle !== false) { fpassthru($handle); fclose($handle); } }
                finally { @unlink($temporary); }
            }, $filename, ['Content-Type' => 'application/gzip']);
        } catch (Throwable $exception) {
            return $this->failure($exception, $server, 'backup-download');
        }
    }

    public function destroy(Request $request, Server $server, string $filename, NodeClient $client): JsonResponse
    {
        $this->authorizeServer($request, $server);
        abort_unless(preg_match(self::FILENAME, $filename), 422, 'Invalid backup filename.');
        try {
            $client->deleteBackup($server->node, $server->id, $filename);
            $server->backups()->where('filename', $filename)->delete();
            return response()->json(['success' => true]);
        } catch (Throwable $exception) {
            return $this->failure($exception, $server, 'backup-delete');
        }
    }

    private function authorizeServer(Request $request, Server $server): void
    {
        abort_if($server->suspended && !$request->user()->isStaff(), 403, 'This server is suspended.');
        $this->authorize('backup', $server);
    }

    private function record(Server $server, string $filename, array $attributes): Backup
    {
        $id = 'bkp-'.substr(hash('sha256', $server->id.'|'.$filename), 0, 60);
        return Backup::query()->updateOrCreate(
            ['id' => $id],
            ['server_id' => $server->id, 'filename' => $filename, 'node_backup_id' => $filename, ...$attributes],
        );
    }

    private function date(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') return now();
        try { return Carbon::parse($value); } catch (Throwable) { return now(); }
    }

    private function failure(Throwable $exception, Server $server, string $action): JsonResponse
    {
        Log::warning('Node daemon backup operation failed.', ['server_id' => $server->id, 'action' => $action, 'exception' => $exception::class]);
        return response()->json(['error' => 'The node daemon could not complete this backup operation.'], 502);
    }
}
