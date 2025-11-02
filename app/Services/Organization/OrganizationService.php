<?php

namespace App\Services\Organization;

use App\Models\{Organization, Team, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationService
{
    public function getUserOrganizations(User $user)
    {
        return $user->organizations()
            ->select('organizations.*')
            ->withCount('users', 'workflows', 'teams')
            ->get();
    }

    public function create(array $data, User $user): Organization
    {
        return DB::transaction(function () use ($data, $user) {
            $org = Organization::create($data);
            $org->users()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);
            return $org->loadCount('users', 'workflows', 'teams');
        });
    }

    public function update(Organization $org, array $data): Organization
    {
        $org->update($data);
        return $org->fresh()->loadCount('users', 'workflows', 'teams');
    }

    public function delete(Organization $org): void
    {
        DB::transaction(fn() => $org->delete());
    }

    public function getMembers(Organization $org)
    {
        return $org->users()
            ->select('users.id', 'users.name', 'users.email')
            ->withPivot('role', 'permissions', 'joined_at')
            ->orderByPivot('joined_at', 'desc')
            ->get();
    }

    public function addMember(Organization $org, int $userId, string $role): void
    {
        if ($org->hasMember($userId)) {
            throw ValidationException::withMessages(['user_id' => 'User already in organization']);
        }

        $org->users()->attach($userId, ['role' => $role, 'joined_at' => now()]);
    }

    public function removeMember(Organization $org, int $userId): void
    {
        $ownerCount = $org->users()->wherePivot('role', 'owner')->count();
        $isOwner = $org->users()->wherePivot('user_id', $userId)->wherePivot('role', 'owner')->exists();

        if ($ownerCount === 1 && $isOwner) {
            throw ValidationException::withMessages(['user_id' => 'Cannot remove last owner']);
        }

        $org->users()->detach($userId);
    }

    public function updateMemberRole(Organization $org, int $userId, string $role): void
    {
        if (!$org->hasMember($userId)) {
            throw ValidationException::withMessages(['user_id' => 'User not in organization']);
        }

        $org->users()->updateExistingPivot($userId, ['role' => $role]);
    }

    public function getTeams(Organization $org)
    {
        return $org->teams()->with('creator:id,name')->latest()->get();
    }

    public function createTeam(Organization $org, array $data, User $user): Team
    {
        return $org->teams()->create(array_merge($data, ['created_by' => $user->id]));
    }

    public function updateTeam(Team $team, array $data): Team
    {
        $team->update($data);
        return $team->fresh();
    }

    public function deleteTeam(Team $team): void
    {
        $team->delete();
    }

    public function updateSettings(Organization $org, array $settings): Organization
    {
        $org->update(['settings' => array_merge($org->settings ?? [], $settings)]);
        return $org->fresh();
    }

    public function getUsage(Organization $org): array
    {
        return [
            'workflows' => [
                'total' => $org->workflows()->count(),
                'active' => $org->workflows()->where('is_active', true)->count(),
            ],
            'executions' => DB::table('executions')
                ->whereIn('workflow_id', $org->workflows()->pluck('id'))
                ->count(),
            'members' => $org->users()->count(),
            'teams' => $org->teams()->count(),
            'credentials' => $org->credentials()->count(),
        ];
    }
}
