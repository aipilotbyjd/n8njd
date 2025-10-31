<?php

namespace App\Services\Node\Execution;

class SetNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $values = $properties['values'] ?? [];

        $outputData = $inputData;

        foreach ($values as $value) {
            $key = $value['key'] ?? null;
            $val = $value['value'] ?? null;

            if ($key) {
                $val = $this->replacePlaceholders($val, $inputData);
                data_set($outputData, $key, $val);
            }
        }

        return $outputData;
    }

    private function replacePlaceholders($data, array $inputData)
    {
        if (is_string($data)) {
            return preg_replace_callback('/{{\s*\$json\.([^\s}]+)\s*}}/', function ($matches) use ($inputData) {
                return data_get($inputData, $matches[1]);
            }, $data);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replacePlaceholders($value, $inputData);
            }
        }

        return $data;
    }
}
