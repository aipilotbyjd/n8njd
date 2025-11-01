<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreRequest;
use App\Http\Requests\Organization\UpdateRequest;
use App\Http\Requests\Organization\MemberRequest;
use App\Http\Requests\Organization\TeamRequest;
use App\Models\Organization;
use App\Models\Team;
use App\Services\Organization\OrganizationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    use ApiResponse;

    public function __construct(private OrganizationService $service) {}

    public function index(Request $request)
    {
        $orgs = $this->service->getUserOrganizations($request->user());
        return $this->success($orgs);
    }

    public function store(StoreRequest $request)
    {
        $org = $this->service->create($request->validated(), $request->user());
        return $this->success($org, 'Organization created', 201);
    }

    public function show(Organization $organization)
    {
        return $this->success($organization->load('users'));
    }

    public function update(UpdateRequest $request, Organization $organization)
    {
        $org = $this->service->update($organization, $request->validated());
        return $this->success($org, 'Organization updated');
    }

    public function destroy(Organization $organization)
    {
        $this->service->delete($organization);
        return $this->success(null, 'Organization deleted');
    }

    public function getMembers(Organization $organization)
    {
        $members = $this->service->getMembers($organization);
        return $this->success($members);
    }

    public function addMember(MemberRequest $request, Organization $organization)
    {
        $this->service->addMember(
            $organization,
            $request->validated('user_id'),
            $request->validated('role', 'member')
        );
        return $this->success(null, 'Member added');
    }

    public function removeMember(Organization $organization, int $userId)
    {
        $this->service->removeMember($organization, $userId);
        return $this->success(null, 'Member removed');
    }

    public function updateMemberRole(Request $request, Organization $organization, int $userId)
    {
        $request->validate(['role' => 'required|string|in:owner,admin,member,viewer']);
        $this->service->updateMemberRole($organization, $userId, $request->role);
        return $this->success(null, 'Member role updated');
    }

    public function getTeams(Organization $organization)
    {
        $teams = $this->service->getTeams($organization);
        return $this->success($teams);
    }

    public function createTeam(TeamRequest $request, Organization $organization)
    {
        $team = $this->service->createTeam($organization, $request->validated());
        return $this->success($team, 'Team created', 201);
    }

    public function updateTeam(TeamRequest $request, Organization $organization, Team $team)
    {
        $team = $this->service->updateTeam($team, $request->validated());
        return $this->success($team, 'Team updated');
    }

    public function deleteTeam(Organization $organization, Team $team)
    {
        $this->service->deleteTeam($team);
        return $this->success(null, 'Team deleted');
    }

    public function getSettings(Organization $organization)
    {
        $settings = $this->service->getSettings($organization);
        return $this->success($settings);
    }

    public function updateSettings(Request $request, Organization $organization)
    {
        $request->validate(['settings' => 'required|array']);
        $org = $this->service->updateSettings($organization, $request->settings);
        return $this->success($org, 'Settings updated');
    }

    public function getUsage(Organization $organization)
    {
        $usage = $this->service->getUsage($organization);
        return $this->success($usage);
    }

    public function getBilling(Organization $organization)
    {
        $billing = $this->service->getBilling($organization);
        return $this->success($billing);
    }
}
