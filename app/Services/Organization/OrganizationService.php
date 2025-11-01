<?php

namespace App\Services\Organization;

use App\Models\Organization;
use Illuminate\Support\Str;

class OrganizationService
{
    public function getOrganizations()
    {
        return Organization::all();
    }

    public function createOrganization(array $data, string $userId): Organization
    {
        $data['id'] = Str::uuid();
        $data['owner_id'] = $userId;

        return Organization::create($data);
    }

    public function getOrganization(string $id): ?Organization
    {
        return Organization::find($id);
    }

    public function updateOrganization(string $id, array $data): ?Organization
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return null;
        }

        $organization->update($data);

        return $organization;
    }

    public function deleteOrganization(string $id): bool
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return false;
        }

        return $organization->delete();
    }

    public function getMembers(string $id)
    {
        $organization = $this->getOrganization($id);

        return $organization ? $organization->members : null;
    }

    public function addMember(string $id, string $userId, string $role)
    {
        $organization = $this->getOrganization($id);
        if ($organization) {
            $organization->members()->attach($userId, ['role' => $role]);
        }

        return $organization;
    }

    public function removeMember(string $id, string $userId)
    {
        $organization = $this->getOrganization($id);
        if ($organization) {
            $organization->members()->detach($userId);
        }

        return $organization;
    }

    public function updateMemberRole(string $id, string $userId, string $role)
    {
        $organization = $this->getOrganization($id);
        if ($organization) {
            $organization->members()->updateExistingPivot($userId, ['role' => $role]);
        }

        return $organization;
    }

    // Mocked methods for now

    public function getTeams(string $id)
    {
        return [];
    }

    public function createTeam(string $id, array $data)
    {
        return ['message' => 'Team created.'];
    }

    public function updateTeam(string $id, string $teamId, array $data)
    {
        return ['message' => 'Team updated.'];
    }

    public function deleteTeam(string $id, string $teamId)
    {
        return ['message' => 'Team deleted.'];
    }

    public function getSettings(string $id)
    {
        return [];
    }

    public function updateSettings(string $id, array $data)
    {
        return ['message' => 'Settings updated.'];
    }

    public function getUsage(string $id)
    {
        return [];
    }

    public function getBilling(string $id)
    {
        return [];
    }
}
