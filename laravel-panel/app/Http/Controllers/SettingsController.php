<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private const PUBLIC_KEYS = ['panelName', 'enableRegistration', 'enableGoogleLogin', 'enablePlayit', 'enableTutorial', 'enableLoginAnimation', 'theme', 'appearance', 'accent', 'backgroundEffect', 'reducedMotion'];
    private const OWNER_KEYS = ['quota_max_servers', 'quota_total_ram_mb', 'quota_total_cpu_percent', 'quota_total_disk_mb', 'free_service_enabled', 'free_service_ram_gb', 'free_service_cpu_percent', 'free_service_disk_gb', 'free_service_duration_hours'];

    public function index(Request $request): JsonResponse
    {
        $this->staff($request);
        $rows = DB::table('settings')->get()->keyBy('key');
        $settings = [];
        foreach ($rows as $key => $row) $settings[$key] = $row->is_encrypted ? null : json_decode((string) $row->value, true);
        return response()->json(['settings' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->staff($request);
        $rules = [
            'panelName' => ['sometimes', 'string', 'max:80'],
            'enableRegistration' => ['sometimes', 'boolean'], 'enableGoogleLogin' => ['sometimes', 'boolean'],
            'enablePlayit' => ['sometimes', 'boolean'], 'enableTutorial' => ['sometimes', 'boolean'],
            'enableLoginAnimation' => ['sometimes', 'boolean'], 'reducedMotion' => ['sometimes', 'boolean'],
            'theme' => ['sometimes', 'string', 'max:64'], 'appearance' => ['sometimes', Rule::in(['dark', 'light', 'system'])],
            'accent' => ['sometimes', 'string', 'max:32'], 'backgroundEffect' => ['sometimes', 'string', 'max:32'],
            'quota_max_servers' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'quota_total_ram_mb' => ['sometimes', 'integer', 'min:0', 'max:16777216'],
            'quota_total_cpu_percent' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'quota_total_disk_mb' => ['sometimes', 'integer', 'min:0', 'max:1099511627776'],
            'free_service_enabled' => ['sometimes', 'boolean'], 'free_service_ram_gb' => ['sometimes', 'numeric', 'gt:0', 'max:1024'],
            'free_service_cpu_percent' => ['sometimes', 'integer', 'gt:0', 'max:10000'], 'free_service_disk_gb' => ['sometimes', 'numeric', 'gt:0', 'max:1048576'],
            'free_service_duration_hours' => ['sometimes', 'integer', 'gt:0', 'max:87600'],
            'google_client_id' => ['sometimes', 'nullable', 'string', 'max:255'], 'google_client_secret' => ['sometimes', 'nullable', 'string', 'max:512'],
        ];
        $data = $request->validate($rules);
        DB::transaction(function () use ($data, $user): void {
            foreach ($data as $key => $value) {
                $encrypted = in_array($key, ['google_client_secret'], true);
                DB::table('settings')->updateOrInsert(['key' => $key], ['value' => json_encode($encrypted ? Crypt::encryptString((string) $value) : $value), 'is_encrypted' => $encrypted, 'updated_at' => now(), 'created_at' => now()]);
            }
            DB::table('audit_events')->insert(['user_id' => $user->id, 'action' => 'settings.update', 'resource_type' => 'settings', 'result' => 'success', 'metadata' => json_encode(['keys' => array_keys($data)]), 'created_at' => now(), 'updated_at' => now()]);
        });
        return $this->index($request);
    }

    private function staff(Request $request)
    {
        abort_unless($request->user()?->isStaff(), 403, 'Staff access is required.');
        abort_unless($request->user()->isOwner() || $request->user()->isAdmin(), 403);
        return $request->user();
    }
}
