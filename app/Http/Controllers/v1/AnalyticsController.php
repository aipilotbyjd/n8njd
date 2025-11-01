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
        return $this->analyticsService->getDashboard();
    }

    public function getOverview(Request $request)
    {
        return $this->analyticsService->getOverview();
    }

    public function getWorkflowPerformance(Request $request)
    {
        return $this->analyticsService->getWorkflowPerformance();
    }

    public function getWorkflowSuccessRate(Request $request)
    {
        return $this->analyticsService->getWorkflowSuccessRate();
    }

    public function getWorkflowExecutionTime(Request $request)
    {
        return $this->analyticsService->getWorkflowExecutionTime();
    }

    public function getMostUsedWorkflows(Request $request)
    {
        return $this->analyticsService->getMostUsedWorkflows();
    }

    public function getWorkflowMetrics(Request $request, $id)
    {
        return $this->analyticsService->getWorkflowMetrics($id);
    }

    public function getExecutionTimeline(Request $request)
    {
        return $this->analyticsService->getExecutionTimeline();
    }

    public function getExecutionStatusBreakdown(Request $request)
    {
        return $this->analyticsService->getExecutionStatusBreakdown();
    }

    public function getExecutionErrorRate(Request $request)
    {
        return $this->analyticsService->getExecutionErrorRate();
    }

    public function getExecutionResourceUsage(Request $request)
    {
        return $this->analyticsService->getExecutionResourceUsage();
    }

    public function getNodeUsage(Request $request)
    {
        return $this->analyticsService->getNodeUsage();
    }

    public function getNodePerformance(Request $request)
    {
        return $this->analyticsService->getNodePerformance();
    }

    public function getNodeErrorRate(Request $request)
    {
        return $this->analyticsService->getNodeErrorRate();
    }

    public function getCostBreakdown(Request $request)
    {
        return $this->analyticsService->getCostBreakdown();
    }

    public function getCostTrends(Request $request)
    {
        return $this->analyticsService->getCostTrends();
    }

    public function getCostByWorkflow(Request $request)
    {
        return $this->analyticsService->getCostByWorkflow();
    }

    public function getReports(Request $request)
    {
        return $this->analyticsService->getReports();
    }

    public function createReport(Request $request)
    {
        return $this->analyticsService->createReport($request->all());
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
