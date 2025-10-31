<?php

namespace App\Services\Collaboration;

class CollaborationService
{
    // Mocked methods for now

    public function getPresence(string $workflowId)
    {
        return [];
    }

    public function joinPresence(string $workflowId, string $userId)
    {
        return ['message' => 'Joined presence channel.'];
    }

    public function leavePresence(string $workflowId, string $userId)
    {
        return ['message' => 'Left presence channel.'];
    }

    public function submitOperation(string $workflowId, array $operation)
    {
        return ['message' => 'Operation submitted.'];
    }

    public function getOperations(string $workflowId, string $cursor)
    {
        return [];
    }

    public function lock(string $workflowId, string $resourceId)
    {
        return ['message' => 'Resource locked.'];
    }

    public function unlock(string $workflowId, string $resourceId)
    {
        return ['message' => 'Resource unlocked.'];
    }
}
