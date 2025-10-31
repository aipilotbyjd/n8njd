<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreNodeRequest;
use App\Http\Requests\V1\UpdateNodeRequest;
use App\Services\Node\NodeService;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    protected $nodeService;

    public function __construct(NodeService $nodeService)
    {
        $this->nodeService = $nodeService;
    }

    public function index(Request $request)
    {
        return $this->nodeService->getNodes();
    }

    public function getCategories(Request $request)
    {
        return $this->nodeService->getCategories();
    }

    public function getTags(Request $request)
    {
        return $this->nodeService->getTags();
    }

    public function getCustomNodes(Request $request)
    {
        return $this->nodeService->getCustomNodes($request->user()->org_id);
    }

    public function createCustomNode(StoreNodeRequest $request)
    {
        $data = $request->validated();
        $orgId = $request->user()->org_id;
        $userId = $request->user()->id;

        return $this->nodeService->createCustomNode($data, $orgId, $userId);
    }

    public function updateCustomNode(UpdateNodeRequest $request, $id)
    {
        return $this->nodeService->updateCustomNode($id, $request->validated());
    }

    public function deleteCustomNode(Request $request, $id)
    {
        return $this->nodeService->deleteCustomNode($id);
    }

    public function publishCustomNode(Request $request, $id)
    {
        return $this->nodeService->publishCustomNode($id);
    }

    public function getUsageStats(Request $request)
    {
        return $this->nodeService->getUsageStats();
    }

    public function show(Request $request, $type)
    {
        return $this->nodeService->getNodeByType($type);
    }

    public function getSchema(Request $request, $type)
    {
        return $this->nodeService->getSchema($type);
    }

    public function testNode(Request $request, $type)
    {
        return $this->nodeService->testNode($type, $request->all());
    }

    public function validateConfig(Request $request, $type)
    {
        return $this->nodeService->validateConfig($type, $request->all());
    }

    public function getDynamicParameters(Request $request, $type)
    {
        return $this->nodeService->getDynamicParameters($type);
    }

    public function resolveParameters(Request $request, $type)
    {
        return $this->nodeService->resolveParameters($type, $request->all());
    }

    public function getNodeUsage(Request $request, $type)
    {
        return $this->nodeService->getNodeUsage($type);
    }
}
