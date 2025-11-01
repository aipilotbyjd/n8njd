<?php

namespace App\Services\Node\Execution;

use App\Models\Node;
use App\Models\WorkflowExecution;
use Exception;

class NodeExecutorFactory
{
    public static function make(Node $node, WorkflowExecution $workflowExecution): NodeExecutor
    {
        $type = str_replace('-', '', ucwords($node->type, '-'));
        $className = 'App\\Services\\Node\\Execution\\' . $type . 'NodeExecutor';

        if (!class_exists($className)) {
            throw new Exception("Node executor for type '{$node->type}' not found.");
        }

        return new $className($node, $workflowExecution);
    }
}
