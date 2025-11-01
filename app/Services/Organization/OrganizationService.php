<?php

namespace App\Services\Organization;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrganizationService
{
    public function getUserOrganizations(User $user)
    {
        return $user->organizations()->with('users')->get();
    }

    public function create(array $data, User $user): Organization
    {
        return DB::transaction(function () use ($data, $user) {
            $org = Organization::create($data);
            $org->users()->attach($user->id, [
                'role' => 'owner',
                'joined_at' => now(),
            ]);
            return $org->load('users');
        });
    }

    public function update(Organization $org, array $data): Organization
    {
        $org->update($data);
        return $org->fresh();
    }

    public function delete(Organization $org): bool
    {
        return $org->delete();
    }

    public function addMember(Organization $org, int $userId, string $role = 'member'): void
    {
        $org->users()->attach($userId, [
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    public function removeMember(Organization $org, int $userId): void
    {
        $org->users()->detach($userId);
    }

    public function updateMemberRole(Organization $org, int $userId, string $role): void
    {
        $org->users()->updateExistingPivot($userId, ['role' => $role]);
    }

    public function getMembers(Organization $org)
    {
        return $org->users()->withPivot('role', 'permissions', 'joined_at')->get();
    }

    public function getTeams(Organization $org)
    {
        return $org->teams()->with('members')->get();
    }

    public function createTeam(Organization $org, array $data): Team
    {
        return $org->teams()->create($data);
    }

    public function updateTeam(Team $team, array $data): Team
    {
        $team->update($data);
        return $team->fresh();
    }

    public function deleteTeam(Team $team): bool
    {
        return $team->delete();
    }

    public function getSettings(Organization $org): array
    {
        return $org->settings ?? [];
    }

    public function updateSettings(Organization $org, array $settings): Organization
    {
        $org->update(['settings' => array_merge($org->settings ?? [], $settings)]);
        return $org->fresh();
    }

    public function getUsage(Organization $org): array
    {
        return [
            'workflows' => $org->workflows()->count(),
            'executions' => $org->workflows()->withCount('executions')->get()->sum('executions_count'),
            'members' => $org->users()->count(),
            'teams' => $org->teams()->count(),
            'credentials' => $org->credentials()->count(),
            'storage_used' => 0, // TODO: Calculate actual storage
        ];
    }

    public function getBilling(Organization $org): array
    {
        return [
            'plan' => $org->plan,
            'is_active' => $org->is_active,
            'usage' => $this->getUsage($org),
        ];
    }
}
