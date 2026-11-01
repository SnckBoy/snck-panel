<?php

namespace App\Http\Controllers;

use App\Services\DiscordRewardsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class DiscordRewardsController extends Controller
{
    public function __construct(private readonly DiscordRewardsService $rewards) {}

    public function admin(Request $request): JsonResponse
    {
        $user = $this->staff($request);
        $state = $this->rewards->state();
        return response()->json(array_merge($this->rewards->publicState($state), ['events' => $state['events'], 'claims' => $state['claims'], 'diagnostics' => array_slice($state['diagnostics'], 0, 100)]));
    }

    public function updateConfig(Request $request): JsonResponse
    {
        $user = $this->staff($request);
        try { $state = $this->rewards->updateConfig($request->all()); $this->audit($user->id, 'discord.config_updated', ['enabled' => $state['config']['enabled']]); return response()->json($this->rewards->publicState($state)); }
        catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 400); }
    }

    public function storePlan(Request $request): JsonResponse
    {
        $user = $this->staff($request);
        try {
            $plan = $this->rewards->validatePlan($request->all());
            $state = $this->rewards->state();
            if (collect($state['plans'])->contains('id', $plan['id'])) return response()->json(['error' => 'A reward plan with this ID already exists.'], 409);
            $state['plans'][] = $plan; $this->rewards->save($state); $this->audit($user->id, 'discord.reward_plan_created', ['planId' => $plan['id']]);
            return response()->json(['plan' => $plan], 201);
        } catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 400); }
    }

    public function updatePlan(Request $request, string $id): JsonResponse
    {
        $user = $this->staff($request);
        try {
            $state = $this->rewards->state();
            if (!collect($state['plans'])->contains('id', $id)) return response()->json(['error' => 'Reward plan not found.'], 404);
            $plan = $this->rewards->validatePlan($request->all(), $id);
            $state['plans'] = array_map(fn (array $row): array => $row['id'] === $id ? $plan : $row, $state['plans']); $this->rewards->save($state); $this->audit($user->id, 'discord.reward_plan_updated', ['planId' => $id]);
            return response()->json(['plan' => $plan]);
        } catch (Throwable $error) { return response()->json(['error' => $error->getMessage()], 400); }
    }

    public function deletePlan(Request $request, string $id): JsonResponse
    {
        $user = $this->staff($request); $state = $this->rewards->state();
        if (!collect($state['plans'])->contains('id', $id)) return response()->json(['error' => 'Reward plan not found.'], 404);
        if (collect($state['claims'])->contains(fn (array $claim): bool => ($claim['planId'] ?? null) === $id && in_array($claim['status'] ?? null, ['processing', 'needs_review'], true))) return response()->json(['error' => 'This plan has a claim requiring reconciliation; disable it instead.'], 409);
        $state['plans'] = array_values(array_filter($state['plans'], fn (array $row): bool => $row['id'] !== $id)); $this->rewards->save($state); $this->audit($user->id, 'discord.reward_plan_deleted', ['planId' => $id]);
        return response()->json(['success' => true]);
    }

    private function staff(Request $request)
    {
        abort_unless($request->user()?->isStaff(), 403, 'Staff access is required.'); return $request->user();
    }

    private function audit(string $userId, string $action, array $metadata): void
    {
        DB::table('audit_events')->insert(['user_id' => $userId, 'action' => $action, 'resource_type' => 'discord_rewards', 'result' => 'success', 'metadata' => json_encode($metadata), 'created_at' => now(), 'updated_at' => now()]);
    }
}
