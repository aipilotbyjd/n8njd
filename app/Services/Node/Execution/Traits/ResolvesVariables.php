<?php

namespace App\Services\Node\Execution\Traits;

use App\Services\Variable\VariableResolver;

trait ResolvesVariables
{
    protected function resolveVariables($data, string $orgId)
    {
        if (is_string($data)) {
            return VariableResolver::resolveInString($data, $orgId);
        }

        if (is_array($data)) {
            return VariableResolver::resolveInArray($data, $orgId);
        }

        return $data;
    }

    protected function replacePlaceholders($data, array $inputData)
    {
        if (is_string($data)) {
            return $this->replaceStringPlaceholders($data, $inputData);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replacePlaceholders($value, $inputData);
            }
        }

        return $data;
    }

    protected function replaceStringPlaceholders(string $string, array $inputData): string
    {
        return preg_replace_callback('/{{\s*\$json\.([^\s}]+)\s*}}/', function ($matches) use ($inputData) {
            return data_get($inputData, $matches[1]) ?? '';
        }, $string);
    }
}
