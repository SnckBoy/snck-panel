<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\NodeClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ServerFilesController extends Controller
{
    public function list(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/list', ['path' => $this->path($request->input('path'))]), $server, 'files-list');
    }

    public function read(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $path = $this->requiredPath($request->input('path'));

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/read', ['path' => $path]), $server, 'files-read');
    }

    public function write(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate(['path' => ['required', 'string', 'max:1000'], 'content' => ['required', 'string', 'max:8000000']]);
        $data['path'] = $this->requiredPath($data['path']);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/write', $data), $server, 'files-write');
    }

    public function writeBase64(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate(['path' => ['required', 'string', 'max:1000'], 'content' => ['required', 'string', 'max:11000000']]);
        $data['path'] = $this->requiredPath($data['path']);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/write-base64', $data), $server, 'files-write-base64');
    }

    public function writeChunk(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate([
            'path' => ['required', 'string', 'max:1000'],
            'uploadId' => ['required', 'string', 'regex:/^[a-zA-Z0-9_-]{8,100}$/'],
            'offset' => ['required', 'integer', 'min:0'],
            'totalSize' => ['required', 'integer', 'min:0', 'max:536870912'],
            'content' => ['required', 'string', 'max:11000000'],
        ]);
        $data['path'] = $this->requiredPath($data['path']);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/write-chunk', $data), $server, 'files-write-chunk');
    }

    public function replaceBatch(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:1000'],
            'files.*.path' => ['required', 'string', 'max:1000'],
            'files.*.content' => ['required', 'string', 'max:11000000'],
            'confirmReplace' => ['sometimes', 'boolean'],
        ]);
        foreach ($data['files'] as &$file) {
            $file['path'] = $this->requiredPath($file['path']);
        }
        unset($file);
        $total = array_sum(array_map(fn (array $file): int => strlen($file['content']), $data['files']));
        abort_if($total > 20000000, 413, 'The combined upload exceeds the 20 MB panel limit.');

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/replace-batch', $data), $server, 'files-replace-batch');
    }

    public function mkdir(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate(['path' => ['required', 'string', 'max:1000']]);
        $path = $this->requiredPath($data['path']);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/mkdir', ['path' => $path]), $server, 'files-mkdir');
    }

    public function rename(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate(['oldPath' => ['required', 'string', 'max:1000'], 'newPath' => ['required', 'string', 'max:1000']]);
        $data['oldPath'] = $this->requiredPath($data['oldPath']);
        $data['newPath'] = $this->requiredPath($data['newPath']);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/rename', $data), $server, 'files-rename');
    }

    public function delete(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate(['paths' => ['required', 'array', 'min:1', 'max:100'], 'paths.*' => ['required', 'string', 'max:1000']]);
        $data['paths'] = array_map(fn (string $path): string => $this->requiredPath($path), $data['paths']);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/delete', $data), $server, 'files-delete');
    }

    public function zip(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate([
            'dirPath' => ['nullable', 'string', 'max:1000'],
            'fileNames' => ['required', 'array', 'min:1', 'max:100'],
            'fileNames.*' => ['required', 'string', 'max:255'],
            'outputName' => ['required', 'string', 'regex:/^[a-zA-Z0-9._-]+\.zip$/i', 'max:128'],
        ]);
        $data['dirPath'] = $this->path($data['dirPath'] ?? '.');
        foreach ($data['fileNames'] as $name) {
            $this->requiredPath($name);
        }

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/zip', $data), $server, 'files-zip');
    }

    public function unzip(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate(['path' => ['required', 'string', 'max:1000']]);
        $data['path'] = $this->requiredPath($data['path']);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'files/unzip', $data), $server, 'files-unzip');
    }

    public function download(Request $request, Server $server, NodeClient $client): StreamedResponse|JsonResponse
    {
        $this->authorizeFiles($request, $server);
        $data = $request->validate(['path' => ['required', 'string', 'max:1000']]);
        $path = $this->requiredPath($data['path']);
        try {
            $temporary = $client->downloadServerFile($server->node, $server->id, $path);
            $name = basename($path);
            if (! preg_match('/^[a-zA-Z0-9._-]{1,255}$/', $name)) {
                $name = 'server-download.zip';
            }

            return response()->streamDownload(function () use ($temporary): void {
                try {
                    $handle = fopen($temporary, 'rb');
                    if ($handle !== false) {
                        fpassthru($handle);
                        fclose($handle);
                    }
                } finally {
                    @unlink($temporary);
                }
            }, $name);
        } catch (Throwable $exception) {
            Log::warning('Daemon file download failed.', ['server_id' => $server->id, 'exception' => $exception::class]);

            return response()->json(['error' => 'The node daemon could not download this file.'], 502);
        }
    }

    public function command(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        abort_if($server->suspended && ! $request->user()->isStaff(), 403, 'This server is suspended.');
        $this->authorize('command', $server);
        $data = $request->validate(['command' => ['required', 'string', 'min:1', 'max:4096']]);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'POST', 'command', $data), $server, 'command');
    }

    public function logs(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        abort_if($server->suspended && ! $request->user()->isStaff(), 403, 'This server is suspended.');
        $this->authorize('logs', $server);
        try {
            return response()->json(['content' => $client->serverLogs($server->node, $server->id)]);
        } catch (Throwable $exception) {
            return $this->failure($exception, $server, 'logs');
        }
    }

    public function stats(Request $request, Server $server, NodeClient $client): JsonResponse
    {
        abort_if($server->suspended && ! $request->user()->isStaff(), 403, 'This server is suspended.');
        $this->authorize('stats', $server);

        return $this->call(fn () => $client->serverRequest($server->node, $server->id, 'GET', 'stats'), $server, 'stats');
    }

    private function authorizeFiles(Request $request, Server $server): void
    {
        abort_if($server->suspended && ! $request->user()->isStaff(), 403, 'This server is suspended.');
        $this->authorize('files', $server);
    }

    private function path(?string $path): string
    {
        $value = str_replace('\\', '/', trim((string) ($path ?? '.')));
        if ($value === '' || $value === '/') {
            return '.';
        }

        return $this->requiredPath($value);
    }

    private function requiredPath(?string $path): string
    {
        $value = str_replace('\\', '/', trim((string) $path));
        abort_if($value === '' || str_starts_with($value, '/') || str_contains($value, "\0") || preg_match('~(^|/)\.\.(/|$)~', $value), 422, 'A safe server-relative path is required.');

        return $value;
    }

    private function call(\Closure $callback, Server $server, string $action): JsonResponse
    {
        try {
            return response()->json($callback());
        } catch (Throwable $exception) {
            return $this->failure($exception, $server, $action);
        }
    }

    private function failure(Throwable $exception, Server $server, string $action): JsonResponse
    {
        Log::warning('Node daemon server operation failed.', ['server_id' => $server->id, 'action' => $action, 'exception' => $exception::class]);

        return response()->json(['error' => 'The node daemon could not complete this operation.'], 502);
    }
}
