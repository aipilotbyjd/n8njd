<?php

namespace App\Services\Node\Execution;

use App\Services\Expression\ExpressionEvaluator;

class SwitchNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $cases = $properties['cases'] ?? [];
        $defaultCase = $properties['default_case'] ?? 'default';

        foreach ($cases as $case) {
            $condition = $case['condition'] ?? null;
            $outputBranch = $case['output'] ?? null;

            if (! $condition || ! $outputBranch) {
                continue;
            }

            try {
                if (ExpressionEvaluator::evaluate($condition, $inputData)) {
                    return [
                        '__branch' => $outputBranch,
                        'data' => $inputData,
                        'matched_case' => $outputBranch,
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return [
            '__branch' => $defaultCase,
            'data' => $inputData,
            'matched_case' => 'default',
        ];
    }
}
