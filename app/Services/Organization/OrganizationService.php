<?php

namespace App\Services\Organization;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationService
{
    public function getUserOrganizations(User $user)
    {
        return $user->organizations()->with('users:id,name,email')->get();
    }

    public function create(array $data, User $user): Organization
    {
        return DB::transaction(function () use ($data, $user) {
            $org = Organization::create($data);
            $org->users()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);
            return $org->load('users:id,name,email');
        });
    }

    public function update(Organization $org, array $data): Organization
    {
        $org->update($data);
        return $org->fresh();
    }

    public function delete(Organization $org): void
    {
        DB::transaction(function () use ($org) {
            $org->workflows()->delete();
            $org->teams()->delete();
            $org->credentials()->delete();
            $org->users()->detach();
            $org->delete();
        });
    }

    public function getMembers(Organization $org)
    {
        return $org->users()
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->withPivot('role', 'permissions', 'joined_at')
            ->get();
    }

    public function addMember(Organization $org, int $userId, string $role): void
    {
        if ($org->users()->where('user_id', $userId)->exists()) {
            throw ValidationException::withMessages(['user_id' => 'User already in organization']);
        }

        $org->users()->attach($userId, ['role' => $role, 'joined_at' => now()]);
    }

    public function removeMember(Organization $org, int $userId): void
    {
        if ($org->users()->wherePivot('role', 'owner')->count() === 1 && 
            $org->users()->wherePivot('user_id', $userId)->wherePivot('role', 'owner')->exists()) {
            throw ValidationException::withMessages(['user_id' => 'Cannot remove last owner']);
        }

        $org->users()->detach($userId);
    }

    public function updateMemberRole(Organization $org, int $userId, string $role): void
    {
        if ($role === 'owner' && $org->users()->wherePivot('role', 'owner')->count() === 1) {
            throw ValidationException::withMessages(['role' => 'Cannot change last owner role']);
        }

        $org->users()->updateExistingPivot($userId, ['role' => $role]);
    }

    public function getTeams(Organization $org)
    {
        return $org->teams()->with('members:id,name,email')->get();
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
            'workflows' => $org->workflows()->count(),
            'active_workflows' => $org->workflows()->where('is_active', true)->count(),
            'executions' => DB::table('executions')
                ->whereIn('workflow_id', $org->workflows()->pluck('id'))
                ->count(),
            'members' => $org->users()->count(),
            'teams' => $org->teams()->count(),
            'credentials' => $org->credentials()->count(),
        ];
    }
}
