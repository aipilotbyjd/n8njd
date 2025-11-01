<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Services\Organization\OrganizationService;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    protected $organizationService;

    public function __construct(OrganizationService $organizationService)
    {
        $this->organizationService = $organizationService;
    }

    public function index(Request $request)
    {
        return $this->organizationService->getOrganizations();
    }

    public function store(Request $request)
    {
        return $this->organizationService->createOrganization($request->all(), $request->user()->id);
    }

    public function show(Request $request, $id)
    {
        return $this->organizationService->getOrganization($id);
    }

    public function update(Request $request, $id)
    {
        return $this->organizationService->updateOrganization($id, $request->all());
    }

    public function destroy(Request $request, $id)
    {
        return $this->organizationService->deleteOrganization($id);
    }

    public function getMembers(Request $request, $id)
    {
        return $this->organizationService->getMembers($id);
    }

    public function addMember(Request $request, $id)
    {
        return $this->organizationService->addMember($id, $request->input('user_id'), $request->input('role'));
    }

    public function removeMember(Request $request, $id, $userId)
    {
        return $this->organizationService->removeMember($id, $userId);
    }

    public function updateMemberRole(Request $request, $id, $userId)
    {
        return $this->organizationService->updateMemberRole($id, $userId, $request->input('role'));
    }

    public function getTeams(Request $request, $id)
    {
        return $this->organizationService->getTeams($id);
    }

    public function createTeam(Request $request, $id)
    {
        return $this->organizationService->createTeam($id, $request->all());
    }

    public function updateTeam(Request $request, $id, $teamId)
    {
        return $this->organizationService->updateTeam($id, $teamId, $request->all());
    }

    public function deleteTeam(Request $request, $id, $teamId)
    {
        return $this->organizationService->deleteTeam($id, $teamId);
    }

    public function getSettings(Request $request, $id)
    {
        return $this->organizationService->getSettings($id);
    }

    public function updateSettings(Request $request, $id)
    {
        return $this->organizationService->updateSettings($id, $request->all());
    }

    public function getUsage(Request $request, $id)
    {
        return $this->organizationService->getUsage($id);
    }

    public function getBilling(Request $request, $id)
    {
        return $this->organizationService->getBilling($id);
    }
}
