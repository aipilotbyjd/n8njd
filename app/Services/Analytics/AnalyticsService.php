<?php

namespace App\Services\Analytics;

use App\Models\NodeExecution;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    public function getDashboard(string $orgId)
    {
        return Cache::remember("dashboard_analytics_{$orgId}", 300, function () use ($orgId) {
            return [
                'overview' => $this->getOverview($orgId),
                'recent_executions' => $this->getRecentExecutions($orgId, 10),
                'top_workflows' => $this->getTopWorkflows($orgId, 5),
                'error_rate' => $this->getErrorRate($orgId),
            ];
        });
    }

    public function getOverview(string $orgId)
    {
        $executions = WorkflowExecution::where('org_id', $orgId);

        return [
            'total_workflows' => Workflow::where('org_id', $orgId)->count(),
            'active_workflows' => Workflow::where('org_id', $orgId)->where('active', true)->count(),
            'total_executions' => $executions->count(),
            'executions_today' => $executions->whereDate('created_at', today())->count(),
            'success_rate' => $this->calculateSuccessRate($orgId),
            'avg_execution_time_ms' => $executions->avg('execution_time_ms'),
        ];
    }

    public function getWorkflowPerformance(string $orgId, ?int $limit = 20)
    {
        return Workflow::where('org_id', $orgId)
            ->withCount(['executions as total_executions'])
            ->withAvg('executions as avg_execution_time', 'execution_time_ms')
            ->orderBy('total_executions', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($workflow) {
                $successCount = WorkflowExecution::where('workflow_id', $workflow->id)
                    ->where('status', 'success')
                    ->count();

                $totalCount = $workflow->total_executions ?? 0;

                return [
                    'workflow_id' => $workflow->id,
                    'name' => $workflow->name,
                    'total_executions' => $totalCount,
                    'avg_execution_time_ms' => round($workflow->avg_execution_time ?? 0, 2),
                    'success_rate' => $totalCount > 0 ? round(($successCount / $totalCount) * 100, 2) : 0,
                ];
            });
    }

    public function getWorkflowSuccessRate(string $orgId, ?string $workflowId = null)
    {
        $query = WorkflowExecution::where('org_id', $orgId);

        if ($workflowId) {
            $query->where('workflow_id', $workflowId);
        }

        $total = $query->count();
        $success = $query->where('status', 'success')->count();
        $error = $query->where('status', 'error')->count();

        return [
            'total' => $total,
            'success' => $success,
            'error' => $error,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 2) : 0,
            'error_rate' => $total > 0 ? round(($error / $total) * 100, 2) : 0,
        ];
    }

    public function getWorkflowExecutionTime(string $orgId, ?string $workflowId = null)
    {
        $query = WorkflowExecution::where('org_id', $orgId)->whereNotNull('execution_time_ms');

        if ($workflowId) {
            $query->where('workflow_id', $workflowId);
        }

        return [
            'avg' => round($query->avg('execution_time_ms') ?? 0, 2),
            'min' => $query->min('execution_time_ms') ?? 0,
            'max' => $query->max('execution_time_ms') ?? 0,
            'median' => $this->calculateMedian($query->pluck('execution_time_ms')->toArray()),
        ];
    }

    public function getMostUsedWorkflows(string $orgId, int $limit = 10)
    {
        return Workflow::where('org_id', $orgId)
            ->withCount('executions')
            ->orderBy('executions_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'name' => $workflow->name,
                    'execution_count' => $workflow->executions_count,
                    'last_executed_at' => $workflow->last_execution_at,
                ];
            });
    }

    public function getWorkflowMetrics(string $workflowId)
    {
        $workflow = Workflow::find($workflowId);

        if (!$workflow) {
            return null;
        }

        $executions = WorkflowExecution::where('workflow_id', $workflowId);

        return [
            'workflow_id' => $workflowId,
            'name' => $workflow->name,
            'total_executions' => $executions->count(),
            'success_count' => $executions->where('status', 'success')->count(),
            'error_count' => $executions->where('status', 'error')->count(),
            'avg_execution_time_ms' => round($executions->avg('execution_time_ms') ?? 0, 2),
            'last_execution_at' => $workflow->last_execution_at,
            'executions_last_24h' => $executions->where('created_at', '>', now()->subDay())->count(),
            'executions_last_7d' => $executions->where('created_at', '>', now()->subWeek())->count(),
            'executions_last_30d' => $executions->where('created_at', '>', now()->subMonth())->count(),
        ];
    }

    public function getExecutionTimeline(string $orgId, int $days = 30)
    {
        $executions = WorkflowExecution::where('org_id', $orgId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->orderBy('date', 'ASC')
            ->get();

        $timeline = [];
        foreach ($executions as $execution) {
            if (!isset($timeline[$execution->date])) {
                $timeline[$execution->date] = [
                    'date' => $execution->date,
                    'success' => 0,
                    'error' => 0,
                    'running' => 0,
                    'waiting' => 0,
                ];
            }
            $timeline[$execution->date][$execution->status] = $execution->count;
        }

        return array_values($timeline);
    }

    public function getExecutionStatusBreakdown(string $orgId)
    {
        return WorkflowExecution::where('org_id', $orgId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });
    }

    public function getExecutionErrorRate(string $orgId, int $days = 30)
    {
        $executions = WorkflowExecution::where('org_id', $orgId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, 
                COUNT(*) as total, 
                SUM(CASE WHEN status = "error" THEN 1 ELSE 0 END) as errors')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total' => $item->total,
                    'errors' => $item->errors,
                    'error_rate' => $item->total > 0 ? round(($item->errors / $item->total) * 100, 2) : 0,
                ];
            });

        return $executions;
    }

    public function getExecutionResourceUsage(string $orgId)
    {
        $executions = WorkflowExecution::where('org_id', $orgId)->whereNotNull('execution_time_ms');

        return [
            'total_execution_time_ms' => $executions->sum('execution_time_ms'),
            'avg_execution_time_ms' => round($executions->avg('execution_time_ms') ?? 0, 2),
            'total_node_executions' => $executions->sum('node_executions_count'),
            'avg_nodes_per_execution' => round($executions->avg('node_executions_count') ?? 0, 2),
        ];
    }

    public function getNodeUsage(string $orgId)
    {
        return NodeExecution::whereHas('workflowExecution', function ($query) use ($orgId) {
            $query->where('org_id', $orgId);
        })
            ->selectRaw('node_type, COUNT(*) as count')
            ->groupBy('node_type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'node_type' => $item->node_type,
                    'usage_count' => $item->count,
                ];
            });
    }

    public function getNodePerformance(string $orgId)
    {
        return NodeExecution::whereHas('workflowExecution', function ($query) use ($orgId) {
            $query->where('org_id', $orgId);
        })
            ->selectRaw('node_type, 
                AVG(execution_time_ms) as avg_time, 
                MIN(execution_time_ms) as min_time,
                MAX(execution_time_ms) as max_time,
                COUNT(*) as count')
            ->groupBy('node_type')
            ->get()
            ->map(function ($item) {
                return [
                    'node_type' => $item->node_type,
                    'avg_execution_time_ms' => round($item->avg_time ?? 0, 2),
                    'min_execution_time_ms' => $item->min_time ?? 0,
                    'max_execution_time_ms' => $item->max_time ?? 0,
                    'execution_count' => $item->count,
                ];
            });
    }

    public function getNodeErrorRate(string $orgId)
    {
        return NodeExecution::whereHas('workflowExecution', function ($query) use ($orgId) {
            $query->where('org_id', $orgId);
        })
            ->selectRaw('node_type, 
                COUNT(*) as total,
                SUM(CASE WHEN status = "error" THEN 1 ELSE 0 END) as errors')
            ->groupBy('node_type')
            ->get()
            ->map(function ($item) {
                return [
                    'node_type' => $item->node_type,
                    'total' => $item->total,
                    'errors' => $item->errors,
                    'error_rate' => $item->total > 0 ? round(($item->errors / $item->total) * 100, 2) : 0,
                ];
            });
    }

    public function getCostBreakdown(string $orgId)
    {
        return [
            'message' => 'Cost tracking not yet implemented',
        ];
    }

    public function getCostTrends(string $orgId)
    {
        return [
            'message' => 'Cost tracking not yet implemented',
        ];
    }

    public function getCostByWorkflow(string $orgId)
    {
        return [
            'message' => 'Cost tracking not yet implemented',
        ];
    }

    public function getReports(string $orgId)
    {
        return [];
    }

    public function createReport(string $orgId, array $data)
    {
        return [
            'status' => 'success',
            'message' => 'Report generation queued',
        ];
    }

    public function getReport(string $id)
    {
        return null;
    }

    public function exportReport(string $id, string $format = 'pdf')
    {
        return [
            'status' => 'error',
            'message' => 'Report export not yet implemented',
        ];
    }

    private function calculateSuccessRate(string $orgId): float
    {
        $executions = WorkflowExecution::where('org_id', $orgId);
        $total = $executions->count();

        if ($total === 0) {
            return 0;
        }

        $success = $executions->where('status', 'success')->count();

        return round(($success / $total) * 100, 2);
    }

    private function getRecentExecutions(string $orgId, int $limit)
    {
        return WorkflowExecution::where('org_id', $orgId)
            ->with('workflow:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($execution) {
                return [
                    'id' => $execution->id,
                    'workflow_name' => $execution->workflow->name ?? 'Unknown',
                    'status' => $execution->status,
                    'execution_time_ms' => $execution->execution_time_ms,
                    'created_at' => $execution->created_at,
                ];
            });
    }

    private function getTopWorkflows(string $orgId, int $limit)
    {
        return Workflow::where('org_id', $orgId)
            ->withCount('executions')
            ->orderBy('executions_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'name' => $workflow->name,
                    'execution_count' => $workflow->executions_count,
                ];
            });
    }

    private function getErrorRate(string $orgId): float
    {
        $executions = WorkflowExecution::where('org_id', $orgId);
        $total = $executions->count();

        if ($total === 0) {
            return 0;
        }

        $errors = $executions->where('status', 'error')->count();

        return round(($errors / $total) * 100, 2);
    }

    private function calculateMedian(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        sort($values);
        $count = count($values);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }
}
