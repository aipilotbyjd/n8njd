<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function getDashboard(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getDashboard($orgId);
    }

    public function getOverview(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getOverview($orgId);
    }

    public function getWorkflowPerformance(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getWorkflowPerformance($orgId);
    }

    public function getWorkflowSuccessRate(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getWorkflowSuccessRate($orgId);
    }

    public function getWorkflowExecutionTime(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getWorkflowExecutionTime($orgId);
    }

    public function getMostUsedWorkflows(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getMostUsedWorkflows($orgId);
    }

    public function getWorkflowMetrics(Request $request, $id)
    {
        return $this->analyticsService->getWorkflowMetrics($id);
    }

    public function getExecutionTimeline(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getExecutionTimeline($orgId);
    }

    public function getExecutionStatusBreakdown(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getExecutionStatusBreakdown($orgId);
    }

    public function getExecutionErrorRate(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getExecutionErrorRate($orgId);
    }

    public function getExecutionResourceUsage(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getExecutionResourceUsage($orgId);
    }

    public function getNodeUsage(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getNodeUsage($orgId);
    }

    public function getNodePerformance(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getNodePerformance($orgId);
    }

    public function getNodeErrorRate(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getNodeErrorRate($orgId);
    }

    public function getCostBreakdown(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getCostBreakdown($orgId);
    }

    public function getCostTrends(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getCostTrends($orgId);
    }

    public function getCostByWorkflow(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getCostByWorkflow($orgId);
    }

    public function getReports(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->getReports($orgId);
    }

    public function createReport(Request $request)
    {
        $orgId = $request->user()->org_id;
        return $this->analyticsService->createReport($orgId, $request->all());
    }

    public function getReport(Request $request, $id)
    {
        return $this->analyticsService->getReport($id);
    }

    public function exportReport(Request $request, $id)
    {
        return $this->analyticsService->exportReport($id);
    }
}
