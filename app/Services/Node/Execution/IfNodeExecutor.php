<?php

namespace App\Services\Node\Execution;

use App\Services\Expression\ExpressionEvaluator;

class IfNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;

        $condition = $properties['condition']; // e.g., "{{ $json.status }} == 'success'"

        $result = false;
        try {
            $result = ExpressionEvaluator::evaluate($condition, $inputData);
        } catch (\Throwable $th) {
            $result = false;
        }

        return [
            '__branch' => $result ? 'true' : 'false',
            'data' => $inputData,
        ];
    }
}
