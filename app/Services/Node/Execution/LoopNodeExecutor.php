<?php

namespace App\Services\Node\Execution;

class LoopNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $mode = $properties['mode'] ?? 'forEach';

        if ($mode === 'forEach') {
            return $this->executeForEach($inputData, $properties);
        }

        if ($mode === 'times') {
            return $this->executeTimes($inputData, $properties);
        }

        return $inputData;
    }

    private function executeForEach(array $inputData, array $properties): array
    {
        $itemsKey = $properties['items_key'] ?? 'items';
        $items = data_get($inputData, $itemsKey);

        if (!is_array($items)) {
            return [
                'items' => [],
                'count' => 0,
            ];
        }

        $results = [];
        $index = 0;

        foreach ($items as $key => $item) {
            $results[] = [
                'item' => $item,
                'index' => $index,
                'key' => $key,
                'first' => $index === 0,
                'last' => $index === count($items) - 1,
            ];
            $index++;
        }

        return [
            'items' => $results,
            'count' => count($results),
        ];
    }

    private function executeTimes(array $inputData, array $properties): array
    {
        $times = (int) ($properties['times'] ?? 1);
        $results = [];

        for ($i = 0; $i < $times; $i++) {
            $results[] = [
                'iteration' => $i,
                'first' => $i === 0,
                'last' => $i === $times - 1,
                'data' => $inputData,
            ];
        }

        return [
            'items' => $results,
            'count' => count($results),
        ];
    }
}
