<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreWorkflowRequest;
use App\Http\Requests\V1\StoreWorkflowVersionRequest;
use App\Http\Requests\V1\UpdateWorkflowRequest;
use App\Services\Workflow\WorkflowService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    use ApiResponse;

    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $workflows = $this->workflowService->getWorkflowsByOrg($request->user()->org_id, $perPage);

        return $this->success($workflows, 'Workflows retrieved successfully');
    }

    public function store(StoreWorkflowRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->org_id;
        $data['created_by'] = $request->user()->id;

        $workflow = $this->workflowService->createWorkflow($data);
        $workflow->load(['nodes', 'edges']);

        return $this->created($workflow, 'Workflow created successfully');
    }

    public function show(string $id)
    {
        $workflow = $this->workflowService->getWorkflow($id);

        if (!$workflow) {
            return $this->notFound('Workflow not found.');
        }

        $workflow->load(['nodes', 'edges']);

        return $this->success($workflow, 'Workflow retrieved successfully');
    }

    public function update(UpdateWorkflowRequest $request, string $id)
    {
        $workflow = $this->workflowService->updateWorkflow($id, $request->validated());

        if (!$workflow) {
            return $this->notFound('Workflow not found.');
        }

        $workflow->load(['nodes', 'edges']);

        return $this->success($workflow, 'Workflow updated successfully');
    }

    public function destroy(string $id)
    {
        $deleted = $this->workflowService->deleteWorkflow($id);

        if (!$deleted) {
            return $this->notFound('Workflow not found.');
        }

        return $this->success(null, 'Workflow deleted successfully');
    }

    public function versions(Request $request, string $id)
    {
        $workflow = $this->workflowService->getWorkflow($id);

        if (!$workflow) {
            return $this->notFound('Workflow not found.');
        }

        $versions = $workflow->versions()->paginate($request->get('per_page', 15));

        return $this->success($versions);
    }

    public function getVersion(string $id, string $versionId)
    {
        $workflow = $this->workflowService->getWorkflow($id);

        if (!$workflow) {
            return $this->notFound('Workflow not found.');
        }

        $version = $workflow->versions()->find($versionId);

        if (!$version) {
            return $this->notFound('Version not found.');
        }

        return $this->success($version);
    }

    public function duplicate(string $id)
    {
        $newWorkflow = $this->workflowService->duplicateWorkflow($id);

        if (!$newWorkflow) {
            return $this->notFound('Workflow not found.');
        }

        return $this->created($newWorkflow);
    }

    public function activate(string $id)
    {
        $workflow = $this->workflowService->activateWorkflow($id);

        if (!$workflow) {
            return $this->notFound('Workflow not found.');
        }

        return $this->success($workflow);
    }

    public function deactivate(string $id)
    {
        $workflow = $this->workflowService->deactivateWorkflow($id);

        if (!$workflow) {
            return $this->notFound('Workflow not found.');
        }

        return $this->success($workflow);
    }

    public function createVersion(StoreWorkflowVersionRequest $request, string $id)
    {
        $newVersion = $this->workflowService->createWorkflowVersion($id, $request->validated());

        if (!$newVersion) {
            return $this->notFound('Workflow not found.');
        }

        return $this->created($newVersion);
    }

    public function import(Request $request)
    {
        $data = $request->all();
        $orgId = $request->user()->org_id;
        $userId = $request->user()->id;

        $workflow = $this->workflowService->importWorkflow($data, $orgId, $userId);

        return $this->created($workflow);
    }

    public function export(string $id)
    {
        $workflowData = $this->workflowService->exportWorkflow($id);

        if (!$workflowData) {
            return $this->notFound('Workflow not found.');
        }

        return $this->success($workflowData, 'Workflow exported successfully');
    }

    public function bulkImport(Request $request)
    {
        $workflows = $request->input('workflows');
        $orgId = $request->user()->org_id;
        $userId = $request->user()->id;

        $createdWorkflows = $this->workflowService->bulkImportWorkflows($workflows, $orgId, $userId);

        return $this->created($createdWorkflows);
    }

    public function bulkDelete(Request $request)
    {
        $workflowIds = $request->input('ids');
        $this->workflowService->bulkDeleteWorkflows($workflowIds);

        return $this->noContent();
    }

    public function bulkActivate(Request $request)
    {
        $workflowIds = $request->input('ids');
        $this->workflowService->bulkActivateWorkflows($workflowIds);

        return $this->noContent();
    }

    public function restoreVersion(string $id, string $versionId)
    {
        $workflow = $this->workflowService->restoreVersion($id, $versionId);

        if (!$workflow) {
            return $this->notFound('Workflow or Version not found.');
        }

        return $this->success($workflow);
    }

    public function compareVersions(string $id, string $v1, string $v2)
    {
        $comparison = $this->workflowService->compareVersions($id, $v1, $v2);

        if (!$comparison) {
            return $this->notFound('Workflow or one of the versions not found.');
        }

        return $this->success($comparison);
    }

    public function getShares(string $id)
    {
        return $this->success($this->workflowService->getWorkflowShares($id));
    }

    public function createShare(Request $request, string $id)
    {
        $userId = $request->input('user_id');
        $permissions = $request->input('permissions');

        return $this->success($this->workflowService->shareWorkflow($id, $userId, $permissions));
    }

    public function deleteShare(string $id, string $userId)
    {
        return $this->success($this->workflowService->unshareWorkflow($id, $userId));
    }

    public function updateSharePermissions(Request $request, string $id, string $userId)
    {
        $permissions = $request->input('permissions');

        return $this->success($this->workflowService->updateWorkflowSharePermissions($id, $userId, $permissions));
    }

    public function getComments(string $id)
    {
        $comments = $this->workflowService->getWorkflowComments($id);

        if (is_null($comments)) {
            return $this->notFound('Workflow not found.');
        }

        return $this->success($comments);
    }

    public function createComment(Request $request, string $id)
    {
        $comment = $this->workflowService->createWorkflowComment($id, $request->user()->id, $request->input('content'));

        return $this->created($comment);
    }

    public function updateComment(Request $request, string $id, string $commentId)
    {
        $comment = $this->workflowService->updateWorkflowComment($commentId, $request->input('content'));

        if (!$comment) {
            return $this->notFound('Comment not found.');
        }

        return $this->success($comment);
    }

    public function deleteComment(string $id, string $commentId)
    {
        if (!$this->workflowService->deleteWorkflowComment($commentId)) {
            return $this->notFound('Comment not found.');
        }

        return $this->noContent();
    }

    public function getSubWorkflows(string $id)
    {
        return $this->success($this->workflowService->getSubWorkflows($id));
    }

    public function linkSubWorkflow(Request $request, string $id)
    {
        $subWorkflowId = $request->input('sub_workflow_id');

        return $this->success($this->workflowService->linkSubWorkflow($id, $subWorkflowId));
    }

    public function unlinkSubWorkflow(string $id, string $subId)
    {
        return $this->success($this->workflowService->unlinkSubWorkflow($id, $subId));
    }

    public function getDependencies(string $id)
    {
        return $this->success($this->workflowService->getDependencies($id));
    }

    public function getDependents(string $id)
    {
        return $this->success($this->workflowService->getDependents($id));
    }

    public function getImpactAnalysis(string $id)
    {
        return $this->success($this->workflowService->getImpactAnalysis($id));
    }

    public function validateWorkflow(string $id)
    {
        return $this->success($this->workflowService->validateWorkflow($id));
    }

    public function testRun(Request $request, string $id)
    {
        return $this->success($this->workflowService->testRun($id, $request->user()));
    }

    public function healthCheck(string $id)
    {
        return $this->success($this->workflowService->healthCheck($id));
    }
}
