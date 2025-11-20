<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\{StoreRequest, UpdateRequest};
use App\Models\{Organization, Project};
use App\Services\Project\ProjectService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(private ProjectService $service) {}

    /**
     * Get all projects for a specific organization.
     */
    public function index(Request $request, Organization $organization)
    {
        if (!$organization->hasMember($request->user()->id)) {
            return $this->forbidden('You are not a member of this organization');
        }

        $filters = $request->only(['status', 'is_active', 'search']);
        $projects = $this->service->getOrganizationProjects($organization, $filters);

        return $this->success($projects);
    }

    /**
     * Create a new project.
     */
    public function store(StoreRequest $request, Organization $organization)
    {
        if (!$organization->isAdmin($request->user()->id)) {
            return $this->forbidden('You do not have permission to create projects');
        }

        $project = $this->service->create($organization, $request->validated(), $request->user());

        return $this->created($project, 'Project created successfully');
    }

    /**
     * Get a specific project.
     */
    public function show(Request $request, Organization $organization, Project $project)
    {
        if (!$organization->hasMember($request->user()->id) || $project->organization_id !== $organization->id) {
            return $this->forbidden('You do not have access to this project');
        }

        $project->load('creator:id,name,email', 'workflows:id,project_id,name,status,is_active')
            ->loadCount('workflows');

        return $this->success($project);
    }

    /**
     * Update a project.
     */
    public function update(UpdateRequest $request, Organization $organization, Project $project)
    {
        if (!$organization->isAdmin($request->user()->id) || $project->organization_id !== $organization->id) {
            return $this->forbidden('You do not have permission to update this project');
        }

        $updated = $this->service->update($project, $request->validated());

        return $this->success($updated, 'Project updated successfully');
    }

    /**
     * Delete a project.
     */
    public function destroy(Request $request, Organization $organization, Project $project)
    {
        if (!$organization->isAdmin($request->user()->id) || $project->organization_id !== $organization->id) {
            return $this->forbidden('You do not have permission to delete this project');
        }

        $this->service->delete($project);

        return $this->success(null, 'Project deleted successfully');
    }

    /**
     * Get project statistics.
     */
    public function getStats(Request $request, Organization $organization, Project $project)
    {
        if (!$organization->hasMember($request->user()->id) || $project->organization_id !== $organization->id) {
            return $this->forbidden('You do not have access to this project');
        }

        $stats = $this->service->getStats($project);

        return $this->success($stats);
    }

    /**
     * Archive a project.
     */
    public function archive(Request $request, Organization $organization, Project $project)
    {
        if (!$organization->isAdmin($request->user()->id) || $project->organization_id !== $organization->id) {
            return $this->forbidden('You do not have permission to archive this project');
        }

        $archived = $this->service->archive($project);

        return $this->success($archived, 'Project archived successfully');
    }

    /**
     * Complete a project.
     */
    public function complete(Request $request, Organization $organization, Project $project)
    {
        if (!$organization->isAdmin($request->user()->id) || $project->organization_id !== $organization->id) {
            return $this->forbidden('You do not have permission to complete this project');
        }

        $completed = $this->service->complete($project);

        return $this->success($completed, 'Project completed successfully');
    }

    /**
     * Bulk delete projects.
     */
    public function bulkDelete(Request $request, Organization $organization)
    {
        if (!$organization->isAdmin($request->user()->id)) {
            return $this->forbidden('You do not have permission to delete projects');
        }

        $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'required|integer|exists:projects,id',
        ]);

        $deleted = $this->service->bulkDelete($organization, $request->project_ids);

        return $this->success(['deleted_count' => $deleted], "Successfully deleted {$deleted} project(s)");
    }
}
