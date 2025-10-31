<?php

namespace App\Services\Node\Execution;

class SplitNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $mode = $properties['mode'] ?? 'array';

        if ($mode === 'array') {
            $key = $properties['key'] ?? null;

            if (! $key) {
                return $inputData;
            }

            $array = data_get($inputData, $key);

            if (! is_array($array)) {
                return $inputData;
            }

            return [
                'items' => array_map(function ($item) {
                    return ['item' => $item];
                }, $array),
            ];
        }

        return $inputData;
    }
}
