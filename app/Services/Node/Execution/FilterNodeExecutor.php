<?php

namespace App\Services\Node\Execution;

use App\Services\Expression\ExpressionEvaluator;

class FilterNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $condition = $properties['condition'] ?? null;

        if (! $condition) {
            return $inputData;
        }

        if (! isset($inputData['items']) || ! is_array($inputData['items'])) {
            return $inputData;
        }

        $filtered = array_filter($inputData['items'], function ($item) use ($condition) {
            try {
                return ExpressionEvaluator::evaluate($condition, $item);
            } catch (\Exception $e) {
                return false;
            }
        });

        return [
            'items' => array_values($filtered),
            'count' => count($filtered),
        ];
    }
}
