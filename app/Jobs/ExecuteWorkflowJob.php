<?php

namespace App\Jobs;

use App\Services\Execution\ExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $workflowId;

    protected $orgId;

    protected $userId;

    protected $triggerData;

    protected $mode;

    /**
     * Create a new job instance.
     */
    public function __construct(string $workflowId, string $orgId, string $userId, array $triggerData, string $mode)
    {
        $this->workflowId = $workflowId;
        $this->orgId = $orgId;
        $this->userId = $userId;
        $this->triggerData = $triggerData;
        $this->mode = $mode;
    }

    /**
     * Execute the job.
     */
    public function handle(ExecutionService $executionService): void
    {
        $executionService->runWorkflow(
            $this->workflowId,
            $this->orgId,
            $this->userId,
            $this->triggerData,
            $this->mode
        );
    }
}
