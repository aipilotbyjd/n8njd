<?php

namespace App\Models;

class WorkflowExecution extends Execution
{
    protected $table = 'executions';

    protected $fillable = [
        // From Execution
        'workflow_id',
        'triggered_by',
        'status',
        'mode',
        'input_data',
        'output_data',
        'error_message',
        'metadata',
        'started_at',
        'finished_at',
        // Additional fields for WorkflowExecution
        'org_id',
        'user_id',
        'trigger_data',
        'execution_time_ms',
        'error_stack',
        'node_executions_count',
        'waiting_node_id',
    ];

    protected function casts(): array
    {
        return [
            'trigger_data' => 'array',
            'input_data' => 'array',
            'output_data' => 'array',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * Get the user who triggered the execution (alias for triggeredBy for compatibility).
     */
    public function getUserIdAttribute($value)
    {
        return $value ?? $this->triggered_by;
    }

    /**
     * Get the trigger data (alias for inputData for compatibility).
     */
    public function getTriggerDataAttribute($value)
    {
        return $value ?? $this->input_data;
    }
}
