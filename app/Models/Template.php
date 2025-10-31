<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
        'description',
        'workflow_data',
        'category',
        'image_url',
        'usage_count',
        'is_public',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'workflow_data' => 'array',
            'tags' => 'array',
            'is_public' => 'boolean',
            'usage_count' => 'integer',
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

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function makePublic(): void
    {
        $this->update(['is_public' => true]);
    }

    public function makePrivate(): void
    {
        $this->update(['is_public' => false]);
    }

    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }
}
