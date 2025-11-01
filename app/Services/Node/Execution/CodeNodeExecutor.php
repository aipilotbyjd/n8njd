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

        // Security: Wrap code in function scope to limit access
        $wrappedCode = "return (function(\$items) { {$code} })(\$items);";

        try {
            $items = $inputData;

            // Disable dangerous functions
            $disabledFunctions = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'eval'];
            foreach ($disabledFunctions as $func) {
                if (stripos($code, $func) !== false) {
                    throw new \Exception("Forbidden function: {$func}");
                }
            }

            $result = eval($wrappedCode);

            return $result ?? $items;
        } catch (\ParseError $e) {
            throw new \Exception('Code parse error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            throw new \Exception('Code execution error: ' . $e->getMessage());
        }
    }
}
