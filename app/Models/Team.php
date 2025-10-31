<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'slug',
        'color',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'slug' => 'string',
            'color' => 'string',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
