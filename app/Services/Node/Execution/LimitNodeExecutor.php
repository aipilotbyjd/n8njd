<?php

namespace App\Services\Node\Execution;

class LimitNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $items = $inputData['items'] ?? [];

        if (! is_array($items)) {
            return ['items' => []];
        }

        $limit = (int) ($properties['limit'] ?? 10);
        $offset = (int) ($properties['offset'] ?? 0);

        $sliced = array_slice($items, $offset, $limit);

        return [
            'items' => $sliced,
            'count' => count($sliced),
            'total' => count($items),
        ];
    }
}
