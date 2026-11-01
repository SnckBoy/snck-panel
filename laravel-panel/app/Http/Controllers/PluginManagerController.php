<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\PluginManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PluginManagerController extends Controller
{
    public function __construct(private readonly PluginManagerService $manager) {}

    public function installed(Request $request, Server $server): JsonResponse
    {
        $this->authorize('plugins', $server);
        try { return response()->json($this->manager->installed($server)); }
        catch (Throwable) { return response()->json(['error' => 'Unable to read installed plugins.'], 502); }
    }

    public function remove(Request $request, Server $server, string $filename): JsonResponse
    {
        $this->authorize('plugins', $server);
        try { return response()->json($this->manager->remove($server, $filename)); }
        catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 400); }
    }

    public function installPlugin(Request $request, Server $server): JsonResponse
    {
        return $this->install($request, $server, 'plugin');
    }

    public function installMod(Request $request, Server $server): JsonResponse
    {
        $this->authorize('mods', $server);
        try { return response()->json($this->manager->install($server, 'mod', $request->all())); }
        catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 400); }
    }

    public function importModpack(Request $request, Server $server): JsonResponse
    {
        $this->authorize('mods', $server);
        $request->validate(['archive' => ['required', 'file', 'max:524288'], 'confirmReplace' => ['sometimes', 'boolean']]);
        try { return response()->json($this->manager->importModpack($server, $request->file('archive'), (bool) $request->boolean('confirmReplace'))); }
        catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 400); }
    }

    private function install(Request $request, Server $server, string $kind): JsonResponse
    {
        $this->authorize('plugins', $server);
        try { return response()->json($this->manager->install($server, $kind, $request->all())); }
        catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 400); }
    }
}
