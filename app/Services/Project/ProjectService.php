<?php

namespace App\Services\Project;

use App\Models\{Organization, Project, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    /**
     * Get all projects for a specific organization.
     */
    public function getOrganizationProjects(Organization $organization, array $filters = [])
    {
        $query = $organization->projects()
            ->with('creator:id,name,email')
            ->withCount('workflows');

        // Apply filters
        if (isset($filters['status'])) {
            $query->ofStatus($filters['status']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->get();
    }

    /**
     * Create a new project.
     */
    public function create(Organization $organization, array $data, User $user): Project
    {
        return DB::transaction(function () use ($organization, $data, $user) {
            $data['organization_id'] = $organization->id;
            $data['created_by'] = $user->id;
            
            $project = Project::create($data);
            
            return $project->load('creator:id,name,email')->loadCount('workflows');
        });
    }

    /**
     * Update an existing project.
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        
        return $project->fresh()
            ->load('creator:id,name,email')
            ->loadCount('workflows');
    }

    /**
     * Delete a project.
     */
    public function delete(Project $project): void
    {
        DB::transaction(function () use ($project) {
            // Optional: You might want to handle workflows differently
            // For now, project_id is nullable, so workflows won't be deleted
            $project->delete();
        });
    }

    /**
     * Get project statistics.
     */
    public function getStats(Project $project): array
    {
        return [
            'workflows' => [
                'total' => $project->workflows()->count(),
                'active' => $project->workflows()->where('is_active', true)->count(),
                'draft' => $project->workflows()->where('status', 'draft')->count(),
            ],
            'executions' => DB::table('executions')
                ->whereIn('workflow_id', $project->workflows()->pluck('id'))
                ->count(),
            'executions_success' => DB::table('executions')
                ->whereIn('workflow_id', $project->workflows()->pluck('id'))
                ->where('status', 'success')
                ->count(),
            'executions_failed' => DB::table('executions')
                ->whereIn('workflow_id', $project->workflows()->pluck('id'))
                ->where('status', 'failed')
                ->count(),
        ];
    }

    /**
     * Archive a project.
     */
    public function archive(Project $project): Project
    {
        $project->archive();
        
        return $project->fresh();
    }

    /**
     * Complete a project.
     */
    public function complete(Project $project): Project
    {
        $project->complete();
        
        return $project->fresh();
    }

    /**
     * Validate user has permission for the project.
     */
    public function validateUserAccess(Project $project, User $user): bool
    {
        return $project->organization->hasMember($user->id);
    }

    /**
     * Bulk delete projects.
     */
    public function bulkDelete(Organization $organization, array $projectIds): int
    {
        return DB::transaction(function () use ($organization, $projectIds) {
            return $organization->projects()
                ->whereIn('id', $projectIds)
                ->delete();
        });
    }
}
