<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function suggestNodes(Request $request)
    {
        return $this->aiService->suggestNodes($request->all());
    }

    public function suggestConnections(Request $request)
    {
        return $this->aiService->suggestConnections($request->all());
    }

    public function optimizeWorkflow(Request $request)
    {
        return $this->aiService->optimizeWorkflow($request->all());
    }

    public function generateWorkflow(Request $request)
    {
        $request->validate(['description' => 'required|string']);
        return $this->aiService->generateWorkflow($request->input('description'));
    }

    public function explainError(Request $request)
    {
        $request->validate(['error' => 'required|string']);
        return $this->aiService->explainError($request->input('error'));
    }

    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        return $this->aiService->chat($request->input('message'));
    }

    public function generateExpression(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);
        return $this->aiService->generateExpression($request->input('prompt'));
    }

    public function generateCode(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);
        return $this->aiService->generateCode($request->input('prompt'));
    }
}
