<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Team extends Model
{
    protected $fillable = ['organization_id', 'name', 'description', 'slug', 'color', 'created_by'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($team) => $team->slug = $team->slug ?? Str::slug($team->name));
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
