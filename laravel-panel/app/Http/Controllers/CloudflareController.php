<?php

namespace App\Http\Controllers;

use App\Services\CloudflareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class CloudflareController extends Controller
{
    public function __construct(private readonly CloudflareService $cloudflare) {}

    public function connection(Request $request): JsonResponse
    {
        $this->staff($request);
        try {
            $account = $this->cloudflare->account();
            return response()->json(['connected' => true, 'id' => $account->id, 'accountId' => $account->account_id, 'createdAt' => $account->created_at]);
        } catch (Throwable) {
            return response()->json(['connected' => false]);
        }
    }

    public function connect(Request $request): JsonResponse
    {
        $user = $this->staff($request);
        $data = $request->validate(['token' => ['required', 'string', 'max:4096'], 'accountId' => ['nullable', 'string', 'max:128']]);
        try {
            $account = $this->cloudflare->connect($data['token'], $data['accountId'] ?? null);
            $this->audit($user->id, 'cloudflare.connected', $account->id, ['accountId' => $account->account_id]);
            return response()->json(['id' => $account->id, 'accountId' => $account->account_id], 201);
        } catch (Throwable $error) {
            return response()->json(['error' => $error->getMessage()], 400);
        }
    }

    public function zones(Request $request): JsonResponse
    {
        $this->staff($request);
        try { return response()->json($this->cloudflare->zones($request->query('accountId'))); }
        catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 502); }
    }

    public function zone(Request $request, string $zoneId): JsonResponse
    {
        $this->staff($request);
        try { return response()->json($this->cloudflare->zone($zoneId, $request->query('accountId'))); }
        catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 502); }
    }

    public function dns(Request $request, string $zoneId): JsonResponse
    {
        $this->staff($request);
        try { return response()->json($this->cloudflare->dns($zoneId, $request->query('accountId'))); }
        catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 502); }
    }

    public function createDns(Request $request, string $zoneId): JsonResponse
    {
        $user = $this->staff($request);
        $data = $this->recordData($request);
        try {
            $record = $this->cloudflare->createDns($zoneId, $data, $request->query('accountId'));
            $this->audit($user->id, 'cloudflare.dns.created', $record['id'] ?? null, ['zoneId' => $zoneId, 'type' => $record['type'] ?? $data['type'], 'name' => $record['name'] ?? $data['name']]);
            return response()->json($record, 201);
        } catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 502); }
    }

    public function updateDns(Request $request, string $zoneId, string $recordId): JsonResponse
    {
        $user = $this->staff($request);
        $data = $this->recordData($request);
        try {
            $record = $this->cloudflare->updateDns($zoneId, $recordId, $data, $request->query('accountId'));
            $this->audit($user->id, 'cloudflare.dns.updated', $recordId, ['zoneId' => $zoneId]);
            return response()->json($record);
        } catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 502); }
    }

    public function deleteDns(Request $request, string $zoneId, string $recordId): JsonResponse
    {
        $user = $this->staff($request);
        try {
            $result = $this->cloudflare->deleteDns($zoneId, $recordId, $request->query('accountId'));
            $this->audit($user->id, 'cloudflare.dns.deleted', $recordId, ['zoneId' => $zoneId]);
            return response()->json($result);
        } catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 502); }
    }

    private function recordData(Request $request): array
    {
        $type = (string) $request->input('type');
        $data = Validator::make($request->all(), [
            'type' => ['required', Rule::in(['A', 'AAAA', 'CNAME', 'TXT', 'MX', 'SRV'])],
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required_unless:type,SRV', 'nullable', 'string', 'max:2048'],
            'ttl' => ['sometimes', 'integer', 'min:60', 'max:86400'],
            'proxied' => ['sometimes', 'boolean'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'data' => ['required_if:type,SRV', 'nullable', 'array'],
            'data.service' => ['required_if:type,SRV', 'string', 'max:255'],
            'data.proto' => ['required_if:type,SRV', 'string', 'max:255'],
            'data.name' => ['required_if:type,SRV', 'string', 'max:255'],
            'data.priority' => ['required_if:type,SRV', 'integer', 'min:0', 'max:65535'],
            'data.weight' => ['required_if:type,SRV', 'integer', 'min:0', 'max:65535'],
            'data.port' => ['required_if:type,SRV', 'integer', 'min:1', 'max:65535'],
            'data.target' => ['required_if:type,SRV', 'string', 'max:255'],
        ])->validate();

        if (in_array($type, ['SRV', 'MX', 'TXT'], true)) {
            unset($data['proxied']);
        }
        return $data;
    }

    private function staff(Request $request)
    {
        abort_unless($request->user()?->isStaff(), 403, 'Staff access is required.');
        return $request->user();
    }

    private function audit(string $userId, string $action, ?string $resourceId, array $metadata): void
    {
        DB::table('audit_events')->insert(['user_id' => $userId, 'action' => $action, 'resource_type' => 'cloudflare', 'resource_id' => $resourceId, 'result' => 'success', 'metadata' => json_encode($metadata), 'created_at' => now(), 'updated_at' => now()]);
    }
}
