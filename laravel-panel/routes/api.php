<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CloudflareController;
use App\Http\Controllers\NodeAgentController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\PlayitController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerFilesController;
use App\Http\Controllers\ServerSubuserController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// The node-agent callback authenticates exclusively with a per-node bearer
// credential; it is intentionally not a browser-session or public endpoint.
Route::post('/node-agent/heartbeat', [NodeAgentController::class, 'heartbeat'])->middleware('throttle:node-heartbeat');

// Node and allocation administration remains staff-only inside those controllers.
// Server routes use per-server policies in every action; global role middleware
// alone is not authorization for tenant-owned or delegated server access.
Route::middleware(['web', 'auth', 'account.enabled'])->group(function (): void {
    Route::get('/nodes', [NodeController::class, 'index']);
    Route::post('/nodes', [NodeController::class, 'store']);
    Route::patch('/nodes/{node}', [NodeController::class, 'update']);
    Route::post('/nodes/{node}/health', [NodeController::class, 'health']);
    Route::post('/nodes/{node}/restart', [NodeController::class, 'restart']);

    Route::get('/allocations', [AllocationController::class, 'index']);
    Route::post('/allocations', [AllocationController::class, 'store']);
    Route::post('/allocations/{allocation}/assign', [AllocationController::class, 'assign']);
    Route::post('/allocations/{allocation}/unassign', [AllocationController::class, 'unassign']);

    Route::get('/servers', [ServerController::class, 'index']);
    Route::post('/servers', [ServerController::class, 'store']);
    Route::post('/servers/{server}/status', [ServerController::class, 'status']);
    Route::get('/servers/{server}/playit', [PlayitController::class, 'status']);
    Route::post('/servers/{server}/playit/start', [PlayitController::class, 'start']);
    Route::post('/servers/{server}/playit/stop', [PlayitController::class, 'stop']);
    Route::post('/servers/{server}/playit/reset', [PlayitController::class, 'reset']);
    Route::delete('/servers/{server}', [ServerController::class, 'destroy']);
    Route::post('/servers/{server}/{action}', [ServerController::class, 'action'])
        ->whereIn('action', ['start', 'stop', 'restart', 'kill']);

    Route::get('/servers/{server}/subusers', [ServerSubuserController::class, 'index']);
    Route::put('/servers/{server}/subusers', [ServerSubuserController::class, 'store']);
    Route::delete('/servers/{server}/subusers/{subuser}', [ServerSubuserController::class, 'destroy']);

    Route::get('/servers/{server}/files', [ServerFilesController::class, 'list']);
    Route::get('/servers/{server}/files/read', [ServerFilesController::class, 'read']);
    Route::get('/servers/{server}/files/download', [ServerFilesController::class, 'download']);
    Route::post('/servers/{server}/files/write', [ServerFilesController::class, 'write']);
    Route::post('/servers/{server}/files/write-base64', [ServerFilesController::class, 'writeBase64']);
    Route::post('/servers/{server}/files/write-chunk', [ServerFilesController::class, 'writeChunk']);
    Route::post('/servers/{server}/files/replace-batch', [ServerFilesController::class, 'replaceBatch']);
    Route::post('/servers/{server}/files/mkdir', [ServerFilesController::class, 'mkdir']);
    Route::post('/servers/{server}/files/rename', [ServerFilesController::class, 'rename']);
    Route::post('/servers/{server}/files/delete', [ServerFilesController::class, 'delete']);
    Route::post('/servers/{server}/files/zip', [ServerFilesController::class, 'zip']);
    Route::post('/servers/{server}/files/unzip', [ServerFilesController::class, 'unzip']);
    Route::post('/servers/{server}/command', [ServerFilesController::class, 'command']);
    Route::get('/servers/{server}/logs', [ServerFilesController::class, 'logs']);
    Route::get('/servers/{server}/stats', [ServerFilesController::class, 'stats']);

    Route::get('/servers/{server}/backups', [BackupController::class, 'index']);
    Route::post('/servers/{server}/backups', [BackupController::class, 'store']);
    Route::get('/servers/{server}/backups/{filename}', [BackupController::class, 'download'])->where('filename', 'backup-[a-zA-Z0-9_.-]{1,180}\.tar\.gz');
    Route::delete('/servers/{server}/backups/{filename}', [BackupController::class, 'destroy'])->where('filename', 'backup-[a-zA-Z0-9_.-]{1,180}\.tar\.gz');

    Route::get('/api-keys', [ApiKeyController::class, 'index']);
    Route::post('/api-keys', [ApiKeyController::class, 'store']);
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy']);
    Route::get('/admin/settings', [SettingsController::class, 'index']);
    Route::put('/admin/settings', [SettingsController::class, 'update']);
    Route::get('/cloudflare/connection', [CloudflareController::class, 'connection']);
    Route::post('/cloudflare/connection', [CloudflareController::class, 'connect']);
    Route::get('/cloudflare/zones', [CloudflareController::class, 'zones']);
    Route::get('/cloudflare/zones/{zoneId}', [CloudflareController::class, 'zone']);
    Route::get('/cloudflare/zones/{zoneId}/dns', [CloudflareController::class, 'dns']);
    Route::post('/cloudflare/zones/{zoneId}/dns', [CloudflareController::class, 'createDns']);
    Route::put('/cloudflare/zones/{zoneId}/dns/{recordId}', [CloudflareController::class, 'updateDns']);
    Route::delete('/cloudflare/zones/{zoneId}/dns/{recordId}', [CloudflareController::class, 'deleteDns']);
});
