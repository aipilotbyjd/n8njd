<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'shared_by',
        'shared_with_user_id',
        'shared_with_organization_id',
        'permission',
        'message',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function sharedWithUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }

    public function sharedWithOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'shared_with_organization_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('shared_with_user_id', $userId);
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('shared_with_organization_id', $organizationId);
    }

    public function scopeWithPermission($query, string $permission)
    {
        return $query->where('permission', $permission);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasViewPermission(): bool
    {
        return in_array($this->permission, ['view', 'edit', 'execute']);
    }

    public function hasEditPermission(): bool
    {
        return in_array($this->permission, ['edit', 'execute']);
    }

    public function hasExecutePermission(): bool
    {
        return $this->permission === 'execute';
    }
}
