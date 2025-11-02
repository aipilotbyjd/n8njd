<?php

namespace App\Services\Execution;

use App\Jobs\ExecuteWorkflowJob;
use App\Models\ExecutionData;
use App\Models\NodeExecution;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Services\Node\Execution\NodeExecutorFactory;
use App\Services\Workflow\Graph;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExecutionService
{
    public function getExecutionsByOrg(string $orgId)
    {
        return WorkflowExecution::where('organization_id', $orgId)->get();
    }

    public function getExecution(string $id): ?WorkflowExecution
    {
        return WorkflowExecution::find($id);
    }

    public function deleteExecution(string $id): bool
    {
        $execution = WorkflowExecution::find($id);

        if (!$execution) {
            return false;
        }

        return $execution->delete();
    }

    public function bulkDeleteExecutions(array $executionIds): void
    {
        WorkflowExecution::whereIn('id', $executionIds)->delete();
    }

    public function executeWorkflow(string $workflowId, string $orgId, string $userId, array $triggerData, string $mode): void
    {
        ExecuteWorkflowJob::dispatch($workflowId, $orgId, $userId, $triggerData, $mode);
    }

    public function runWorkflow(string $workflowId, string $orgId, string $userId, array $triggerData, string $mode): WorkflowExecution
    {
        $workflow = Workflow::find($workflowId);

        if (!$workflow) {
            Log::error('Workflow not found', ['workflow_id' => $workflowId]);
            throw new \Exception("Workflow not found: {$workflowId}");
        }

        Log::info('Starting workflow execution', [
            'workflow_id' => $workflowId,
            'workflow_name' => $workflow->name,
            'organization_id' => $orgId,
            'user_id' => $userId,
            'mode' => $mode,
        ]);

        $startTime = microtime(true);
        $startedAt = now();

        $workflowExecution = WorkflowExecution::create([
            'id' => Str::uuid(),
            'workflow_id' => $workflowId,
            'organization_id' => $orgId,
            'user_id' => $userId,
            'trigger_data' => $triggerData,
            'mode' => $mode,
            'status' => 'running',
            'started_at' => $startedAt,
        ]);

        $nodes = collect($workflow->nodes);
        $connections = collect($workflow->connections);

        $graph = new Graph;
        foreach ($nodes as $node) {
            $graph->addNode($node['id']);
        }
        foreach ($connections as $connection) {
            $graph->addEdge($connection['source'], $connection['target']);
        }

        $startNode = $nodes->firstWhere('type', 'start');

        if (!$startNode) {
            Log::error('Start node not found in workflow', ['workflow_id' => $workflowId]);
            $workflowExecution->status = 'error';
            $workflowExecution->error_message = 'Start node not found in workflow';
            $workflowExecution->save();

            return $workflowExecution;
        }

        try {
            $this->executeNode($startNode['id'], $triggerData, $workflowExecution, $nodes, $connections, $graph);
            $workflowExecution->status = 'success';

            Log::info('Workflow execution completed successfully', [
                'execution_id' => $workflowExecution->id,
                'workflow_id' => $workflowId,
            ]);
        } catch (\Exception $e) {
            $workflowExecution->status = 'error';
            $workflowExecution->error_message = $e->getMessage();
            $workflowExecution->error_stack = $e->getTraceAsString();

            Log::error('Workflow execution failed', [
                'execution_id' => $workflowExecution->id,
                'workflow_id' => $workflowId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        $finishedAt = now();
        $executionTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        $workflowExecution->finished_at = $finishedAt;
        $workflowExecution->execution_time_ms = $executionTimeMs;
        $workflowExecution->node_executions_count = NodeExecution::where('execution_id', $workflowExecution->id)->count();
        $workflowExecution->save();

        return $workflowExecution;
    }

    private function executeNode(string $nodeId, array $inputData, WorkflowExecution $workflowExecution, $nodes, $connections, Graph $graph)
    {
        $nodeModel = $nodes->firstWhere('id', $nodeId);

        if ($nodeModel->type === 'wait') {
            $workflowExecution->status = 'waiting';
            $workflowExecution->waiting_node_id = $nodeId;
            $workflowExecution->save();

            return;
        }

        $nodeExecutionId = Str::uuid();
        $startTime = microtime(true);
        $startedAt = now();
        $inputDataId = null;
        $outputDataId = null;
        $error = null;
        $status = 'running';

        try {
            Log::debug('Executing node', [
                'execution_id' => $workflowExecution->id,
                'node_id' => $nodeId,
                'node_type' => $nodeModel->type,
            ]);

            if (!empty($inputData)) {
                $inputDataRecord = ExecutionData::create([
                    'id' => Str::uuid(),
                    'data' => $inputData,
                ]);
                $inputDataId = $inputDataRecord->id;
            }

            $executor = NodeExecutorFactory::make($nodeModel, $workflowExecution);
            $outputData = $executor->execute($inputData);

            if (!empty($outputData)) {
                $outputDataRecord = ExecutionData::create([
                    'id' => Str::uuid(),
                    'data' => $outputData,
                ]);
                $outputDataId = $outputDataRecord->id;
            }

            $status = 'success';

            Log::debug('Node execution completed', [
                'execution_id' => $workflowExecution->id,
                'node_id' => $nodeId,
                'execution_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
            ]);
        } catch (\Exception $e) {
            $status = 'error';
            $error = $e->getMessage();
            $outputData = [];

            Log::error('Node execution failed', [
                'execution_id' => $workflowExecution->id,
                'node_id' => $nodeId,
                'node_type' => $nodeModel->type,
                'error' => $e->getMessage(),
            ]);
        }

        $finishedAt = now();
        $executionTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        NodeExecution::create([
            'id' => $nodeExecutionId,
            'execution_id' => $workflowExecution->id,
            'workflow_id' => $workflowExecution->workflow_id,
            'node_id' => $nodeId,
            'node_type' => $nodeModel->type,
            'status' => $status,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'execution_time_ms' => $executionTimeMs,
            'input_data_id' => $inputDataId,
            'output_data_id' => $outputDataId,
            'error' => $error,
        ]);

        if ($status === 'error') {
            throw new \Exception($error);
        }

        $successors = $graph->getSuccessors($nodeId);

        if ($nodeModel->type === 'if') {
            $branch = $outputData['__branch'] ?? 'false';
            $data = $outputData['data'];

            $nextConnection = $connections->firstWhere(function ($connection) use ($nodeId, $branch) {
                return $connection['source'] === $nodeId && $connection['sourceHandle'] === $branch;
            });

            if ($nextConnection) {
                $this->executeNode($nextConnection['target'], $data, $workflowExecution, $nodes, $connections, $graph);
            }
        } else {
            foreach ($successors as $successorId) {
                $this->executeNode($successorId, $outputData, $workflowExecution, $nodes, $connections, $graph);
            }
        }
    }

    // Mocked methods for now

    public function stop(string $id)
    {
        $execution = $this->getExecution($id);

        if (!$execution) {
            return ['status' => 'error', 'message' => 'Execution not found.'];
        }

        if ($execution->status !== 'running') {
            return ['status' => 'error', 'message' => 'Execution is not running.'];
        }

        $execution->status = 'stopped';
        $execution->save();

        return ['status' => 'success', 'message' => 'Execution stopped.'];
    }

    public function retry(string $id)
    {
        $execution = $this->getExecution($id);

        if (!$execution) {
            return ['status' => 'error', 'message' => 'Execution not found.'];
        }

        if ($execution->status !== 'error') {
            return ['status' => 'error', 'message' => 'Execution did not fail.'];
        }

        $this->executeWorkflow(
            $execution->workflow_id,
            $execution->organization_id,
            $execution->user_id,
            $execution->trigger_data,
            $execution->mode
        );

        return ['status' => 'success', 'message' => 'Execution retried.'];
    }

    public function resume(string $id)
    {
        $execution = $this->getExecution($id);

        if (!$execution) {
            return ['status' => 'error', 'message' => 'Execution not found.'];
        }

        if ($execution->status !== 'waiting') {
            return ['status' => 'error', 'message' => 'Execution is not waiting.'];
        }

        $execution->status = 'running';
        $execution->save();

        $workflow = Workflow::find($execution->workflow_id);
        $nodes = collect($workflow->nodes);
        $connections = collect($workflow->connections);

        $graph = new Graph;
        foreach ($nodes as $node) {
            $graph->addNode($node['id']);
        }
        foreach ($connections as $connection) {
            $graph->addEdge($connection['source'], $connection['target']);
        }

        $successors = $graph->getSuccessors($execution->waiting_node_id);

        foreach ($successors as $successorId) {
            $this->executeNode($successorId, $execution->trigger_data, $execution, $nodes, $connections, $graph);
        }

        return ['status' => 'success', 'message' => 'Execution resumed.'];
    }

    public function bulkRetry(array $ids)
    {
        foreach ($ids as $id) {
            $this->retry($id);
        }

        return ['status' => 'success', 'message' => 'Executions retried.'];
    }

    public function getNodes(string $id)
    {
        $execution = $this->getExecution($id);

        if (!$execution) {
            return null;
        }

        return $execution->workflow->nodes;
    }

    public function getNode(string $id, string $nodeId)
    {
        $nodes = $this->getNodes($id);

        if (!$nodes) {
            return null;
        }

        return collect($nodes)->firstWhere('id', $nodeId);
    }

    public function getLogs(string $id)
    {
        return NodeExecution::where('execution_id', $id)->get();
    }

    public function getTimeline(string $id)
    {
        $nodeExecutions = NodeExecution::where('execution_id', $id)->orderBy('started_at')->get();

        $timeline = [];
        foreach ($nodeExecutions as $nodeExecution) {
            $timeline[] = [
                'node_id' => $nodeExecution->node_id,
                'node_type' => $nodeExecution->node_type,
                'status' => $nodeExecution->status,
                'started_at' => $nodeExecution->started_at,
                'finished_at' => $nodeExecution->finished_at,
                'duration' => $nodeExecution->execution_time_ms,
            ];
        }

        return $timeline;
    }

    public function getData(string $id)
    {
        $nodeExecutions = NodeExecution::where('execution_id', $id)->get();

        $data = [];
        foreach ($nodeExecutions as $nodeExecution) {
            $inputData = ExecutionData::find($nodeExecution->input_data_id);
            $outputData = ExecutionData::find($nodeExecution->output_data_id);

            $data[] = [
                'node_id' => $nodeExecution->node_id,
                'input_data' => $inputData ? $inputData->data : null,
                'output_data' => $outputData ? $outputData->data : null,
            ];
        }

        return $data;
    }

    public function getErrors(string $id)
    {
        return NodeExecution::where('execution_id', $id)->whereNotNull('error')->get();
    }

    public function getWaiting()
    {
        return WorkflowExecution::where('status', 'waiting')->get();
    }

    public function continueWaiting(string $id)
    {
        return $this->resume($id);
    }

    public function cancelWaiting(string $id)
    {
        $execution = $this->getExecution($id);

        if (!$execution) {
            return ['status' => 'error', 'message' => 'Execution not found.'];
        }

        if ($execution->status !== 'waiting') {
            return ['status' => 'error', 'message' => 'Execution is not waiting.'];
        }

        $execution->status = 'cancelled';
        $execution->save();

        return ['status' => 'success', 'message' => 'Waiting execution cancelled.'];
    }

    public function getStats()
    {
        $total = WorkflowExecution::count();
        $success = WorkflowExecution::where('status', 'success')->count();
        $error = WorkflowExecution::where('status', 'error')->count();
        $running = WorkflowExecution::where('status', 'running')->count();
        $waiting = WorkflowExecution::where('status', 'waiting')->count();
        $stopped = WorkflowExecution::where('status', 'stopped')->count();
        $cancelled = WorkflowExecution::where('status', 'cancelled')->count();

        return [
            'total' => $total,
            'success' => $success,
            'error' => $error,
            'running' => $running,
            'waiting' => $waiting,
            'stopped' => $stopped,
            'cancelled' => $cancelled,
        ];
    }

    public function getDailyStats()
    {
        return WorkflowExecution::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
    }

    public function getStatsByWorkflow()
    {
        return WorkflowExecution::selectRaw('workflow_id, COUNT(*) as count')
            ->groupBy('workflow_id')
            ->get();
    }

    public function getStatsByStatus()
    {
        return WorkflowExecution::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
    }

    public function getPerformanceStats()
    {
        $avg = NodeExecution::avg('execution_time_ms');
        $median = NodeExecution::median('execution_time_ms');
        $min = NodeExecution::min('execution_time_ms');
        $max = NodeExecution::max('execution_time_ms');

        return [
            'avg' => $avg,
            'median' => $median,
            'min' => $min,
            'max' => $max,
        ];
    }

    public function getQueueStatus()
    {
        $connection = config('queue.default');
        $queue = config('queue.connections.' . $connection . '.queue');

        return [
            'connection' => $connection,
            'queue' => $queue,
            'size' => \Illuminate\Support\Facades\Queue::size(),
        ];
    }

    public function getQueueMetrics()
    {
        return $this->getQueueStatus();
    }

    public function clearQueue()
    {
        \Illuminate\Support\Facades\Queue::clear(config('queue.default'));

        return ['status' => 'success', 'message' => 'Queue cleared.'];
    }

    public function setQueuePriority(string $id, int $priority)
    {
        return ['status' => 'error', 'message' => 'Setting queue priority is not supported by the current queue driver.'];
    }
}
