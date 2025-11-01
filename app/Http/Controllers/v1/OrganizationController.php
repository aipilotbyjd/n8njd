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
            if ($org && !$org->userIsOwnerOrAdmin($request->user()->id)) {
                return response()->json(['message' => 'Unauthorized'], 403);
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
        return $this->success($this->service->create($request->validated(), $request->user()), 'Organization created', 201);
    }

    public function show(Organization $organization)
    {
        return $this->success($organization->load('users:id,name,email'));
    }

    public function update(UpdateRequest $request, Organization $organization)
    {
        return $this->success($this->service->update($organization, $request->validated()), 'Organization updated');
    }

    public function destroy(Organization $organization)
    {
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
        return $this->success($this->service->createTeam($organization, $request->validated(), $request->user()), 'Team created', 201);
    }

    public function updateTeam(TeamRequest $request, Organization $organization, Team $team)
    {
        return $this->success($this->service->updateTeam($team, $request->validated()), 'Team updated');
    }

    public function deleteTeam(Organization $organization, Team $team)
    {
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
        return $this->success($this->service->updateSettings($organization, $request->settings), 'Settings updated');
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
