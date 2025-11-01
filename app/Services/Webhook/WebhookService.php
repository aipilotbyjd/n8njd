<?php

namespace App\Services\Webhook;

use App\Models\Webhook;
use App\Services\Execution\ExecutionService;
use Illuminate\Support\Str;

class WebhookService
{
    protected $executionService;

    public function __construct(ExecutionService $executionService)
    {
        $this->executionService = $executionService;
    }

    public function getWebhooksByOrg(string $orgId)
    {
        return Webhook::where('org_id', $orgId)->get();
    }

    public function createWebhook(array $data, string $orgId): Webhook
    {
        $data['id'] = Str::uuid();
        $data['org_id'] = $orgId;

        return Webhook::create($data);
    }

    public function getWebhook(string $id): ?Webhook
    {
        return Webhook::find($id);
    }

    public function updateWebhook(string $id, array $data): ?Webhook
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            return null;
        }

        $webhook->update($data);

        return $webhook;
    }

    public function deleteWebhook(string $id): bool
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            return false;
        }

        return $webhook->delete();
    }

    public function handleIncomingWebhook(string $workflowId, string $path, $request)
    {
        $webhook = Webhook::where('workflow_id', $workflowId)
            ->where('path', $path)
            ->where('active', true)
            ->first();

        if (!$webhook) {
            return response()->json(['error' => 'Webhook not found or inactive'], 404);
        }

        if (!WebhookAuthenticator::validateIpWhitelist($request, $webhook->ip_whitelist)) {
            return response()->json(['error' => 'IP not whitelisted'], 403);
        }

        if (!WebhookAuthenticator::authenticate($request, $webhook->auth_config ?? [], $webhook->auth_type ?? 'none')) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }

        $webhook->last_triggered_at = now();
        $webhook->trigger_count = ($webhook->trigger_count ?? 0) + 1;
        $webhook->save();

        $workflow = $webhook->workflow;

        if (!$workflow || !$workflow->active) {
            return response()->json(['error' => 'Workflow not found or inactive'], 404);
        }

        $data = is_array($request) ? $request : $request->all();

        $this->executionService->executeWorkflow(
            $workflowId,
            $webhook->org_id,
            $workflow->user_id,
            $data,
            'webhook'
        );

        return response()->json([
            'message' => 'Webhook received and workflow execution queued',
            'workflow_id' => $workflowId,
            'webhook_path' => $path,
        ], 202);
    }

    public function test(string $id)
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            return ['status' => 'error', 'message' => 'Webhook not found'];
        }

        try {
            $testData = ['test' => true, 'timestamp' => now()->toIso8601String()];

            $this->executionService->executeWorkflow(
                $webhook->workflow_id,
                $webhook->org_id,
                $webhook->workflow->user_id,
                $testData,
                'test'
            );

            return [
                'status' => 'success',
                'message' => 'Test webhook triggered successfully',
                'webhook_id' => $id,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Test failed: ' . $e->getMessage(),
            ];
        }
    }

    public function getTestUrl(string $id)
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            return ['error' => 'Webhook not found'];
        }

        return [
            'url' => url("/api/webhook/{$webhook->workflow_id}/{$webhook->path}"),
            'method' => strtoupper($webhook->method ?? 'POST'),
        ];
    }

    public function getLogs(string $id)
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            return [];
        }

        return $webhook->workflow->executions()
            ->where('mode', 'webhook')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($execution) {
                return [
                    'id' => $execution->id,
                    'status' => $execution->status,
                    'triggered_at' => $execution->created_at,
                    'execution_time_ms' => $execution->execution_time_ms,
                    'error' => $execution->error_message,
                ];
            });
    }

    public function getStats(string $id)
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            return ['error' => 'Webhook not found'];
        }

        $executions = $webhook->workflow->executions()->where('mode', 'webhook');

        return [
            'total_triggers' => $webhook->trigger_count ?? 0,
            'last_triggered_at' => $webhook->last_triggered_at,
            'total_executions' => $executions->count(),
            'success_count' => $executions->where('status', 'success')->count(),
            'error_count' => $executions->where('status', 'error')->count(),
            'avg_execution_time_ms' => $executions->avg('execution_time_ms'),
            'active' => $webhook->active,
        ];
    }

    public function regenerateToken(string $id)
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            return ['status' => 'error', 'message' => 'Webhook not found'];
        }

        $newToken = Str::random(64);

        if (!$webhook->auth_config) {
            $webhook->auth_config = [];
        }

        $authConfig = $webhook->auth_config;

        if ($webhook->auth_type === 'bearer') {
            $authConfig['token'] = $newToken;
        } elseif ($webhook->auth_type === 'api_key') {
            $authConfig['api_key'] = $newToken;
        } elseif ($webhook->auth_type === 'hmac') {
            $authConfig['secret'] = $newToken;
        }

        $webhook->auth_config = $authConfig;
        $webhook->save();

        return [
            'status' => 'success',
            'message' => 'Token regenerated',
            'token' => $newToken,
        ];
    }

    public function updateIpWhitelist(string $id, array $ips)
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            return ['status' => 'error', 'message' => 'Webhook not found'];
        }

        $webhook->ip_whitelist = $ips;
        $webhook->save();

        return [
            'status' => 'success',
            'message' => 'IP whitelist updated',
            'whitelist' => $ips,
        ];
    }
}
