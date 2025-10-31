<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreWebhookRequest;
use App\Http\Requests\V1\UpdateWebhookRequest;
use App\Services\Webhook\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function handleIncomingWebhook(Request $request, $workflowId, $path)
    {
        return $this->webhookService->handleIncomingWebhook($workflowId, $path, $request);
    }

    public function index(Request $request)
    {
        return $this->webhookService->getWebhooksByOrg($request->user()->org_id);
    }

    public function store(StoreWebhookRequest $request)
    {
        return $this->webhookService->createWebhook($request->validated(), $request->user()->org_id);
    }

    public function show(Request $request, $id)
    {
        return $this->webhookService->getWebhook($id);
    }

    public function update(UpdateWebhookRequest $request, $id)
    {
        return $this->webhookService->updateWebhook($id, $request->validated());
    }

    public function destroy(Request $request, $id)
    {
        return $this->webhookService->deleteWebhook($id);
    }

    public function test(Request $request, $id)
    {
        return $this->webhookService->test($id);
    }

    public function getTestUrl(Request $request, $id)
    {
        return $this->webhookService->getTestUrl($id);
    }

    public function getLogs(Request $request, $id)
    {
        return $this->webhookService->getLogs($id);
    }

    public function getStats(Request $request, $id)
    {
        return $this->webhookService->getStats($id);
    }

    public function regenerateToken(Request $request, $id)
    {
        return $this->webhookService->regenerateToken($id);
    }

    public function updateIpWhitelist(Request $request, $id)
    {
        return $this->webhookService->updateIpWhitelist($id, $request->input('ips'));
    }
}
