<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ExecuteWorkflowRequest;
use App\Services\Execution\ExecutionService;
use Illuminate\Http\Request;

class ExecutionController extends Controller
{
    protected $executionService;

    public function __construct(ExecutionService $executionService)
    {
        $this->executionService = $executionService;
    }

    public function index(Request $request)
    {
        $orgId = $request->user()->organizations()->first()->id ?? null;
        return $this->executionService->getExecutionsByOrg($orgId);
    }

    public function show(Request $request, $id)
    {
        return $this->executionService->getExecution($id);
    }

    public function destroy(Request $request, $id)
    {
        return $this->executionService->deleteExecution($id);
    }

    public function bulkDelete(Request $request)
    {
        $this->executionService->bulkDeleteExecutions($request->input('ids'));

        return response()->noContent();
    }

    public function stop(Request $request, $id)
    {
        return $this->executionService->stop($id);
    }

    public function retry(Request $request, $id)
    {
        return $this->executionService->retry($id);
    }

    public function resume(Request $request, $id)
    {
        return $this->executionService->resume($id);
    }

    public function bulkRetry(Request $request)
    {
        return $this->executionService->bulkRetry($request->input('ids'));
    }

    public function getNodes(Request $request, $id)
    {
        return $this->executionService->getNodes($id);
    }

    public function getNode(Request $request, $id, $nodeId)
    {
        return $this->executionService->getNode($id, $nodeId);
    }

    public function getLogs(Request $request, $id)
    {
        return $this->executionService->getLogs($id);
    }

    public function getTimeline(Request $request, $id)
    {
        return $this->executionService->getTimeline($id);
    }

    public function getData(Request $request, $id)
    {
        return $this->executionService->getData($id);
    }

    public function getErrors(Request $request, $id)
    {
        return $this->executionService->getErrors($id);
    }

    public function getWaiting(Request $request)
    {
        return $this->executionService->getWaiting();
    }

    public function continueWaiting(Request $request, $id)
    {
        return $this->executionService->continueWaiting($id);
    }

    public function cancelWaiting(Request $request, $id)
    {
        return $this->executionService->cancelWaiting($id);
    }

    public function getStats(Request $request)
    {
        return $this->executionService->getStats();
    }

    public function getDailyStats(Request $request)
    {
        return $this->executionService->getDailyStats();
    }

    public function getStatsByWorkflow(Request $request)
    {
        return $this->executionService->getStatsByWorkflow();
    }

    public function getStatsByStatus(Request $request)
    {
        return $this->executionService->getStatsByStatus();
    }

    public function getPerformanceStats(Request $request)
    {
        return $this->executionService->getPerformanceStats();
    }

    public function getQueueStatus(Request $request)
    {
        return $this->executionService->getQueueStatus();
    }

    public function getQueueMetrics(Request $request)
    {
        return $this->executionService->getQueueMetrics();
    }

    public function clearQueue(Request $request)
    {
        return $this->executionService->clearQueue();
    }

    public function setQueuePriority(Request $request, $id)
    {
        return $this->executionService->setQueuePriority($id, $request->input('priority'));
    }

    public function executeWorkflow(ExecuteWorkflowRequest $request, $id)
    {
        $orgId = $request->user()->organizations()->first()->id ?? null;
        return $this->executionService->executeWorkflow($id, $orgId, $request->user()->id, $request->validated(), 'manual');
    }

    public function testExecuteWorkflow(ExecuteWorkflowRequest $request, $id)
    {
        $orgId = $request->user()->organizations()->first()->id ?? null;
        return $this->executionService->executeWorkflow($id, $orgId, $request->user()->id, $request->validated(), 'test');
    }
}
