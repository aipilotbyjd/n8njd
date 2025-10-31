<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'organization_id',
        'created_by',
        'settings',
        'last_executed_at',
        'execution_count',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'last_executed_at' => 'datetime',
            'execution_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The organization that owns the workflow.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user that created the workflow.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The nodes that belong to the workflow.
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class)->orderBy('position_index');
    }

    /**
     * The executions that belong to the workflow.
     */
    public function executions(): HasMany
    {
        return $this->hasMany(Execution::class);
    }

    /**
     * The versions that belong to the workflow.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(WorkflowVersion::class);
    }

    /**
     * The webhooks that belong to the workflow.
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    /**
     * The schedules that belong to the workflow.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * The variables that belong to the workflow.
     */
    public function variables(): HasMany
    {
        return $this->hasMany(Variable::class);
    }

    /**
     * The parent workflows that contain this workflow as a sub-workflow.
     */
    public function parentWorkflows(): HasMany
    {
        return $this->hasMany(WorkflowSubWorkflow::class, 'sub_workflow_id');
    }

    /**
     * The sub-workflows of this workflow.
     */
    public function subWorkflows(): HasMany
    {
        return $this->hasMany(WorkflowSubWorkflow::class, 'parent_workflow_id');
    }

    /**
     * Get trigger nodes for this workflow.
     */
    public function triggerNodes(): HasMany
    {
        return $this->nodes()->where('type', 'trigger');
    }

    /**
     * Get action nodes for this workflow.
     */
    public function actionNodes(): HasMany
    {
        return $this->nodes()->where('type', 'action');
    }

    /**
     * Get condition nodes for this workflow.
     */
    public function conditionNodes(): HasMany
    {
        return $this->nodes()->where('type', 'condition');
    }

    /**
     * Scope a query to only include active workflows.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include workflows with a specific status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include workflows of a specific organization.
     */
    public function scopeOfOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Increment the execution count.
     */
    public function incrementExecutionCount(): void
    {
        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);
    }

    /**
     * Check if workflow is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if workflow is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->is_active;
    }

    /**
     * Activate the workflow.
     */
    public function activate(): void
    {
        $this->update(['status' => 'active', 'is_active' => true]);
    }

    /**
     * Deactivate the workflow.
     */
    public function deactivate(): void
    {
        $this->update(['status' => 'inactive', 'is_active' => false]);
    }

    /**
     * Get the workflow's node count.
     */
    public function getNodeCountAttribute(): int
    {
        return $this->nodes()->count();
    }

    /**
     * Get the workflow's execution success rate.
     */
    public function getSuccessRateAttribute(): float
    {
        $total = $this->executions()->count();
        if ($total === 0) return 0;

        $successful = $this->executions()->where('status', 'success')->count();
        return ($successful / $total) * 100;
    }
}
