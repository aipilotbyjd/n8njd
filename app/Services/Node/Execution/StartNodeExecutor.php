<?php

namespace App\Services\Node\Execution;

use App\Models\Node;

class StartNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        // The Start node simply passes the trigger data to the next node
        return $this->workflowExecution->trigger_data;
    }
}
