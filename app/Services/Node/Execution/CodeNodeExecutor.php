<?php

namespace App\Services\Node\Execution;

class CodeNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $code = $properties['code'] ?? '';
        $language = $properties['language'] ?? 'javascript';

        if ($language !== 'php') {
            throw new \Exception('Only PHP code execution is currently supported');
        }

        try {
            $items = $inputData;

            $result = eval($code);

            if ($result === false) {
                throw new \Exception('Code execution failed');
            }

            return $result ?? $items;
        } catch (\ParseError $e) {
            throw new \Exception('Code parse error: '.$e->getMessage());
        } catch (\Throwable $e) {
            throw new \Exception('Code execution error: '.$e->getMessage());
        }
    }
}
