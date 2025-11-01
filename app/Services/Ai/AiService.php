<?php

namespace App\Services\Ai;

class AiService
{
    // Mocked methods for now

    public function suggestNodes(array $workflow)
    {
        return [];
    }

    public function suggestConnections(array $workflow)
    {
        return [];
    }

    public function optimizeWorkflow(array $workflow)
    {
        return $workflow;
    }

    public function generateWorkflow(string $description)
    {
        return [];
    }

    public function explainError(string $errorMessage)
    {
        return ['explanation' => 'This is a mocked explanation for the error: ' . $errorMessage];
    }

    public function chat(string $message)
    {
        return ['reply' => 'This is a mocked reply to your message: ' . $message];
    }

    public function generateExpression(string $prompt)
    {
        return ['expression' => '{{ 1 + 1 }}'];
    }

    public function generateCode(string $prompt)
    {
        return ['code' => 'console.log("Hello, world!");'];
    }
}
