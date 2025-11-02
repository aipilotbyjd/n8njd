<?php

namespace App\Services\Workflow;

use App\Enums\WorkflowStatus;
use App\Models\Comment;
use App\Models\Workflow;
use App\Models\WorkflowShare;
use App\Models\WorkflowSubWorkflow;
use App\Models\WorkflowVersion;
use Illuminate\Support\Str;

class WorkflowService
{
    public function createWorkflow(array $data): Workflow
    {
        $data['id'] = Str::uuid();

        return Workflow::create($data);
    }

    public function getWorkflow(string $id): ?Workflow
    {
        return Workflow::find($id);
    }

    public function updateWorkflow(string $id, array $data): ?Workflow
    {
        $workflow = Workflow::find($id);

        if (!$workflow) {
            return null;
        }

        $workflow->update($data);

        return $workflow;
    }

    public function deleteWorkflow(string $id): bool
    {
        $workflow = Workflow::find($id);

        if (!$workflow) {
            return false;
        }

        return $workflow->delete();
    }

    public function getWorkflowsByOrg(?string $orgId, int $perPage = 15)
    {
        if (!$orgId) {
            return Workflow::where('id', null)->paginate($perPage);
        }
        
        return Workflow::where('organization_id', $orgId)->paginate($perPage);
    }

    public function duplicateWorkflow(string $id): ?Workflow
    {
        $workflow = $this->getWorkflow($id);

        if (!$workflow) {
            return null;
        }

        $newWorkflow = $workflow->replicate();
        $newWorkflow->name = $workflow->name . ' - copy';
        $newWorkflow->created_at = now();
        $newWorkflow->updated_at = now();
        $newWorkflow->save();

        return $newWorkflow;
    }

    public function activateWorkflow(string $id): ?Workflow
    {
        $workflow = $this->getWorkflow($id);

        if (!$workflow) {
            return null;
        }

        $workflow->status = WorkflowStatus::ACTIVE;
        $workflow->save();

        return $workflow;
    }

    public function deactivateWorkflow(string $id): ?Workflow
    {
        $workflow = $this->getWorkflow($id);

        if (!$workflow) {
            return null;
        }

        $workflow->status = WorkflowStatus::INACTIVE;
        $workflow->save();

        return $workflow;
    }

    public function createWorkflowVersion(string $id, array $data): ?WorkflowVersion
    {
        $workflow = $this->getWorkflow($id);

        if (!$workflow) {
            return null;
        }

        $latestVersion = $workflow->versions()->latest()->first();

        $newVersion = new WorkflowVersion([
            'workflow_id' => $workflow->id,
            'version' => $latestVersion ? $latestVersion->version + 1 : 1,
            'description' => $data['description'],
            'definition' => $latestVersion ? $latestVersion->definition : [],
        ]);

        $newVersion->save();

        return $newVersion;
    }

    public function importWorkflow(array $data, string $orgId, string $userId): Workflow
    {
        $data['organization_id'] = $orgId;
        $data['created_by'] = $userId;

        return $this->createWorkflow($data);
    }

    public function exportWorkflow(string $id): ?array
    {
        $workflow = $this->getWorkflow($id);

        if (!$workflow) {
            return null;
        }

        return $workflow->toArray();
    }

    public function bulkImportWorkflows(array $workflows, string $orgId, string $userId): array
    {
        $createdWorkflows = [];
        foreach ($workflows as $workflowData) {
            $createdWorkflows[] = $this->importWorkflow($workflowData, $orgId, $userId);
        }

        return $createdWorkflows;
    }

    public function bulkDeleteWorkflows(array $workflowIds): void
    {
        Workflow::whereIn('id', $workflowIds)->delete();
    }

    public function bulkActivateWorkflows(array $workflowIds): void
    {
        Workflow::whereIn('id', $workflowIds)->update(['status' => WorkflowStatus::ACTIVE]);
    }

    public function restoreVersion(string $workflowId, string $versionId): ?Workflow
    {
        $workflow = $this->getWorkflow($workflowId);
        if (!$workflow) {
            return null;
        }

        $version = $workflow->versions()->find($versionId);
        if (!$version) {
            return null;
        }

        $workflow->nodes = $version->definition['nodes'];
        $workflow->connections = $version->definition['connections'];
        $workflow->settings = $version->definition['settings'];
        $workflow->version = $version->version;
        $workflow->save();

        return $workflow;
    }

    public function compareVersions(string $workflowId, string $versionId1, string $versionId2): ?array
    {
        $workflow = $this->getWorkflow($workflowId);
        if (!$workflow) {
            return null;
        }

        $version1 = $workflow->versions()->find($versionId1);
        $version2 = $workflow->versions()->find($versionId2);

        if (!$version1 || !$version2) {
            return null;
        }

        // This is a simplified comparison. A more detailed diff would be needed for a real application.
        return [
            'version1' => $version1->definition,
            'version2' => $version2->definition,
        ];
    }

    public function getWorkflowShares(string $workflowId)
    {
        return WorkflowShare::where('workflow_id', $workflowId)->get();
    }

    public function shareWorkflow(string $workflowId, string $userId, string $permissions): WorkflowShare
    {
        return WorkflowShare::create([
            'id' => Str::uuid(),
            'workflow_id' => $workflowId,
            'user_id' => $userId,
            'permissions' => $permissions,
        ]);
    }

    public function unshareWorkflow(string $workflowId, string $userId): bool
    {
        return WorkflowShare::where('workflow_id', $workflowId)->where('user_id', $userId)->delete();
    }

    public function updateWorkflowSharePermissions(string $workflowId, string $userId, string $permissions): bool
    {
        return WorkflowShare::where('workflow_id', $workflowId)->where('user_id', $userId)->update(['permissions' => $permissions]);
    }

    public function getWorkflowComments(string $workflowId)
    {
        $workflow = $this->getWorkflow($workflowId);
        if (!$workflow) {
            return null;
        }

        return $workflow->comments;
    }

    public function createWorkflowComment(string $workflowId, string $userId, string $comment): Comment
    {
        return Comment::create([
            'id' => Str::uuid(),
            'workflow_id' => $workflowId,
            'user_id' => $userId,
            'content' => $comment,
        ]);
    }

    public function updateWorkflowComment(string $commentId, string $comment): ?Comment
    {
        $commentModel = Comment::find($commentId);
        if (!$commentModel) {
            return null;
        }
        $commentModel->content = $comment;
        $commentModel->save();

        return $commentModel;
    }

    public function deleteWorkflowComment(string $commentId): bool
    {
        $comment = Comment::find($commentId);
        if (!$comment) {
            return false;
        }

        return $comment->delete();
    }

    public function getSubWorkflows(string $workflowId)
    {
        return WorkflowSubWorkflow::where('workflow_id', $workflowId)->get();
    }

    public function linkSubWorkflow(string $workflowId, string $subWorkflowId): WorkflowSubWorkflow
    {
        return WorkflowSubWorkflow::create([
            'id' => Str::uuid(),
            'workflow_id' => $workflowId,
            'sub_workflow_id' => $subWorkflowId,
        ]);
    }

    public function unlinkSubWorkflow(string $workflowId, string $subWorkflowId): bool
    {
        return WorkflowSubWorkflow::where('workflow_id', $workflowId)->where('sub_workflow_id', $subWorkflowId)->delete();
    }

    public function getDependencies(string $workflowId)
    {
        $subWorkflows = WorkflowSubWorkflow::where('workflow_id', $workflowId)->get();

        $dependencies = [];
        foreach ($subWorkflows as $subWorkflow) {
            $dependencies[] = $this->getWorkflow($subWorkflow->sub_workflow_id);
        }

        return $dependencies;
    }

    public function getDependents(string $workflowId)
    {
        $superWorkflows = WorkflowSubWorkflow::where('sub_workflow_id', $workflowId)->get();

        $dependents = [];
        foreach ($superWorkflows as $superWorkflow) {
            $dependents[] = $this->getWorkflow($superWorkflow->workflow_id);
        }

        return $dependents;
    }

    public function getImpactAnalysis(string $workflowId)
    {
        $dependents = $this->getDependents($workflowId);
        $impact = $dependents;

        foreach ($dependents as $dependent) {
            $impact = array_merge($impact, $this->getImpactAnalysis($dependent->id));
        }

        return $impact;
    }

    public function validateWorkflow(string $workflowId)
    {
        $workflow = $this->getWorkflow($workflowId);

        if (!$workflow) {
            return ['status' => 'error', 'message' => 'Workflow not found.'];
        }

        $definition = $workflow->definition;

        if (!isset($definition['nodes']) || !is_array($definition['nodes'])) {
            return ['status' => 'error', 'message' => 'Workflow must have nodes.'];
        }

        if (!isset($definition['connections']) || !is_array($definition['connections'])) {
            return ['status' => 'error', 'message' => 'Workflow must have connections.'];
        }

        return ['status' => 'success', 'message' => 'Workflow is valid.'];
    }

    public function testRun(string $workflowId, $user)
    {
        $executionService = app(ExecutionService::class);

        return $executionService->runWorkflow($workflowId, $user->organization_id, $user->id, [], 'test');
    }

    public function healthCheck(string $workflowId)
    {
        $validation = $this->validateWorkflow($workflowId);

        if ($validation['status'] === 'error') {
            return ['status' => 'error', 'message' => $validation['message']];
        }

        return ['status' => 'success', 'message' => 'Workflow health is good.'];
    }
}
