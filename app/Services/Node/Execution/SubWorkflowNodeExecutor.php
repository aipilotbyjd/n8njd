<?php

namespace App\Services\Node\Execution;

use App\Models\Workflow;
use App\Services\Execution\ExecutionService;
use Illuminate\Support\Facades\Log;

class SubWorkflowNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $subWorkflowId = $properties['sub_workflow_id'] ?? null;

        if (! $subWorkflowId) {
            throw new \Exception('Sub-workflow ID not specified');
        }

        $subWorkflow = Workflow::find($subWorkflowId);

        if (! $subWorkflow) {
            throw new \Exception("Sub-workflow not found: {$subWorkflowId}");
        }

        Log::info('Executing sub-workflow', [
            'parent_execution_id' => $this->workflowExecution->id,
            'sub_workflow_id' => $subWorkflowId,
            'sub_workflow_name' => $subWorkflow->name,
        ]);

        $executionService = app(ExecutionService::class);

        $subExecution = $executionService->runWorkflow(
            $subWorkflowId,
            $this->workflowExecution->org_id,
            $this->workflowExecution->user_id,
            $inputData,
            'sub-workflow'
        );

        if ($subExecution->status === 'error') {
            throw new \Exception("Sub-workflow execution failed: {$subExecution->error_message}");
        }

        if ($subExecution->status === 'waiting') {
            $this->workflowExecution->status = 'waiting';
            $this->workflowExecution->save();

            return [
                'status' => 'waiting',
                'sub_execution_id' => $subExecution->id,
            ];
        }

        return [
            'status' => 'completed',
            'sub_execution_id' => $subExecution->id,
            'execution_time_ms' => $subExecution->execution_time_ms,
            'result' => $subExecution->trigger_data,
        ];
    }
}
