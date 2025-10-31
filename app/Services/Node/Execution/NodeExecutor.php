<?php

namespace App\Services\Node\Execution;

use App\Models\Node;
use App\Models\WorkflowExecution;

abstract class NodeExecutor
{
    protected $node;

    protected $workflowExecution;

    public function __construct(Node $node, WorkflowExecution $workflowExecution)
    {
        $this->node = $node;
        $this->workflowExecution = $workflowExecution;
    }

    abstract public function execute(array $inputData = []);
}
