<?php

namespace App\Services\Node\Execution;

class SortNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $items = $inputData['items'] ?? [];

        if (! is_array($items) || empty($items)) {
            return ['items' => []];
        }

        $sortField = $properties['field'] ?? null;
        $sortOrder = $properties['order'] ?? 'asc';

        if (! $sortField) {
            return ['items' => $items];
        }

        usort($items, function ($a, $b) use ($sortField, $sortOrder) {
            $aVal = data_get($a, $sortField);
            $bVal = data_get($b, $sortField);

            if ($aVal === $bVal) {
                return 0;
            }

            $comparison = $aVal < $bVal ? -1 : 1;

            return $sortOrder === 'desc' ? -$comparison : $comparison;
        });

        return ['items' => $items];
    }
}
