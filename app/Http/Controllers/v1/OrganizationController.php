<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\{StoreRequest, UpdateRequest, MemberRequest, TeamRequest};
use App\Models\{Organization, Team};
use App\Services\Organization\OrganizationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    use ApiResponse;

    public function __construct(private OrganizationService $service)
    {
        $this->middleware(function ($request, $next) {
            $org = $request->route('organization');
            if ($org && !$org->isAdmin($request->user()->id)) {
                return $this->forbidden('You do not have permission to perform this action');
            }
            return $next($request);
        })->except(['index', 'store', 'show']);
    }

    public function index(Request $request)
    {
        return $this->success($this->service->getUserOrganizations($request->user()));
    }

    public function store(StoreRequest $request)
    {
        $org = $this->service->create($request->validated(), $request->user());
        return $this->success($org, 'Organization created', 201);
    }

    public function show(Organization $organization)
    {
        if (!$organization->hasMember(request()->user()->id)) {
            return $this->forbidden('You are not a member of this organization');
        }
        return $this->success($organization->loadCount('users', 'workflows', 'teams'));
    }

    public function update(UpdateRequest $request, Organization $organization)
    {
        return $this->success($this->service->update($organization, $request->validated()));
    }

    public function destroy(Organization $organization)
    {
        if (!$organization->isOwner(request()->user()->id)) {
            return $this->forbidden('Only owners can delete the organization');
        }
        $this->service->delete($organization);
        return $this->success(null, 'Organization deleted');
    }

    public function getMembers(Organization $organization)
    {
        return $this->success($this->service->getMembers($organization));
    }

    public function addMember(MemberRequest $request, Organization $organization)
    {
        $this->service->addMember($organization, $request->user_id, $request->role ?? 'member');
        return $this->success(null, 'Member added');
    }

    public function removeMember(Organization $organization, int $userId)
    {
        $this->service->removeMember($organization, $userId);
        return $this->success(null, 'Member removed');
    }

    public function updateMemberRole(Request $request, Organization $organization, int $userId)
    {
        $request->validate(['role' => 'required|in:owner,admin,member,viewer']);
        $this->service->updateMemberRole($organization, $userId, $request->role);
        return $this->success(null, 'Role updated');
    }

    public function getTeams(Organization $organization)
    {
        return $this->success($this->service->getTeams($organization));
    }

    public function createTeam(TeamRequest $request, Organization $organization)
    {
        $team = $this->service->createTeam($organization, $request->validated(), $request->user());
        return $this->success($team, 'Team created', 201);
    }

    public function updateTeam(TeamRequest $request, Organization $organization, Team $team)
    {
        if ($team->organization_id !== $organization->id) {
            return $this->forbidden('Team does not belong to this organization');
        }
        return $this->success($this->service->updateTeam($team, $request->validated()));
    }

    public function deleteTeam(Organization $organization, Team $team)
    {
        if ($team->organization_id !== $organization->id) {
            return $this->forbidden('Team does not belong to this organization');
        }
        $this->service->deleteTeam($team);
        return $this->success(null, 'Team deleted');
    }

    public function getSettings(Organization $organization)
    {
        return $this->success($organization->settings ?? []);
    }

    public function updateSettings(Request $request, Organization $organization)
    {
        $request->validate(['settings' => 'required|array']);
        return $this->success($this->service->updateSettings($organization, $request->settings));
    }

    public function getUsage(Organization $organization)
    {
        return $this->success($this->service->getUsage($organization));
    }

    public function getBilling(Organization $organization)
    {
        return $this->success([
            'plan' => $organization->plan,
            'is_active' => $organization->is_active,
            'usage' => $this->service->getUsage($organization),
        ]);
    }
}
