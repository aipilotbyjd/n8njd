<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreNodeRequest;
use App\Http\Requests\V1\UpdateNodeRequest;
use App\Services\Node\NodeService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    use ApiResponse;

    protected $nodeService;

    public function __construct(NodeService $nodeService)
    {
        $this->nodeService = $nodeService;
    }

    public function index(Request $request)
    {
        return $this->success($this->nodeService->getNodes(), 'Node types retrieved successfully');
    }

    public function getCategories(Request $request)
    {
        return $this->success($this->nodeService->getCategories(), 'Categories retrieved successfully');
    }

    public function getTags(Request $request)
    {
        return $this->success($this->nodeService->getTags(), 'Tags retrieved successfully');
    }

    public function getCustomNodes(Request $request)
    {
        return $this->success($this->nodeService->getCustomNodes($request->user()->org_id), 'Custom nodes retrieved successfully');
    }

    public function createCustomNode(StoreNodeRequest $request)
    {
        $data = $request->validated();
        $orgId = $request->user()->org_id;
        $userId = $request->user()->id;

        return $this->created($this->nodeService->createCustomNode($data, $orgId, $userId), 'Custom node created successfully');
    }

    public function updateCustomNode(UpdateNodeRequest $request, $id)
    {
        $result = $this->nodeService->updateCustomNode($id, $request->validated());
        
        if (!$result) {
            return $this->notFound('Custom node not found');
        }
        
        return $this->success($result, 'Custom node updated successfully');
    }

    public function deleteCustomNode(Request $request, $id)
    {
        $result = $this->nodeService->deleteCustomNode($id);
        
        if (!$result) {
            return $this->notFound('Custom node not found');
        }
        
        return $this->success(null, 'Custom node deleted successfully');
    }

    public function publishCustomNode(Request $request, $id)
    {
        return $this->success($this->nodeService->publishCustomNode($id), 'Custom node published');
    }

    public function getUsageStats(Request $request)
    {
        return $this->success($this->nodeService->getUsageStats(), 'Usage stats retrieved successfully');
    }

    public function show(Request $request, $type)
    {
        $node = $this->nodeService->getNodeByType($type);
        
        if (!$node) {
            return $this->notFound('Node type not found');
        }
        
        return $this->success($node, 'Node type retrieved successfully');
    }

    public function getSchema(Request $request, $type)
    {
        return $this->success($this->nodeService->getSchema($type), 'Schema retrieved successfully');
    }

    public function testNode(Request $request, $type)
    {
        return $this->success($this->nodeService->testNode($type, $request->all()), 'Node test completed');
    }

    public function validateConfig(Request $request, $type)
    {
        return $this->success($this->nodeService->validateConfig($type, $request->all()), 'Validation completed');
    }

    public function getDynamicParameters(Request $request, $type)
    {
        return $this->success($this->nodeService->getDynamicParameters($type), 'Dynamic parameters retrieved');
    }

    public function resolveParameters(Request $request, $type)
    {
        return $this->success($this->nodeService->resolveParameters($type, $request->all()), 'Parameters resolved');
    }

    public function getNodeUsage(Request $request, $type)
    {
        return $this->success($this->nodeService->getNodeUsage($type), 'Node usage retrieved');
    }
}
