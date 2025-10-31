<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Collaboration\CollaborationService;
use Illuminate\Http\Request;

class CollaborationController extends Controller
{
    protected $collaborationService;

    public function __construct(CollaborationService $collaborationService)
    {
        $this->collaborationService = $collaborationService;
    }

    public function getPresence(Request $request, $id)
    {
        return $this->collaborationService->getPresence($id);
    }

    public function joinPresence(Request $request, $id)
    {
        return $this->collaborationService->joinPresence($id, $request->user()->id);
    }

    public function leavePresence(Request $request, $id)
    {
        return $this->collaborationService->leavePresence($id, $request->user()->id);
    }

    public function submitOperation(Request $request, $id)
    {
        return $this->collaborationService->submitOperation($id, $request->all());
    }

    public function getOperations(Request $request, $id, $cursor)
    {
        return $this->collaborationService->getOperations($id, $cursor);
    }

    public function lock(Request $request, $id)
    {
        return $this->collaborationService->lock($id, $request->input('resource_id'));
    }

    public function unlock(Request $request, $id)
    {
        return $this->collaborationService->unlock($id, $request->input('resource_id'));
    }
}
