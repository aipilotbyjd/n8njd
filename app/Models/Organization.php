<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Organization extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'plan', 'is_active', 'settings'];
    protected $casts = ['is_active' => 'boolean', 'settings' => 'array'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($org) => $org->slug = $org->slug ?? Str::slug($org->name) . '-' . Str::random(6));
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->using(OrganizationUser::class)
            ->withPivot('role', 'permissions', 'joined_at')
            ->withTimestamps();
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class);
    }

    public function userHasRole(int $userId, string $role): bool
    {
        return $this->users()->wherePivot('user_id', $userId)->wherePivot('role', $role)->exists();
    }

    public function userIsOwnerOrAdmin(int $userId): bool
    {
        return $this->users()->wherePivot('user_id', $userId)->whereIn('role', ['owner', 'admin'])->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
