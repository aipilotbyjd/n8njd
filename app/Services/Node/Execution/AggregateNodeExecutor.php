<?php

namespace App\Services\Node\Execution;

class AggregateNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $items = $inputData['items'] ?? [];

        if (!is_array($items) || empty($items)) {
            return ['aggregated' => []];
        }

        $operations = $properties['operations'] ?? [];
        $results = [];

        foreach ($operations as $operation) {
            $field = $operation['field'] ?? null;
            $type = $operation['type'] ?? 'sum';
            $outputKey = $operation['output_key'] ?? $field . '_' . $type;

            if (!$field) {
                continue;
            }

            $values = array_map(function ($item) use ($field) {
                return data_get($item, $field);
            }, $items);

            $values = array_filter($values, function ($val) {
                return is_numeric($val);
            });

            switch ($type) {
                case 'sum':
                    $results[$outputKey] = array_sum($values);
                    break;
                case 'avg':
                    $results[$outputKey] = count($values) > 0 ? array_sum($values) / count($values) : 0;
                    break;
                case 'min':
                    $results[$outputKey] = count($values) > 0 ? min($values) : null;
                    break;
                case 'max':
                    $results[$outputKey] = count($values) > 0 ? max($values) : null;
                    break;
                case 'count':
                    $results[$outputKey] = count($items);
                    break;
            }
        }

        return [
            'aggregated' => $results,
            'item_count' => count($items),
        ];
    }
}
