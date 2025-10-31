<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowSubWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_workflow_id',
        'sub_workflow_id',
        'node_id',
        'name',
        'parameters',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'order' => 'integer',
        ];
    }

    public function parentWorkflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'parent_workflow_id');
    }

    public function subWorkflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'sub_workflow_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function scopeForParent($query, int $parentWorkflowId)
    {
        return $query->where('parent_workflow_id', $parentWorkflowId);
    }

    public function scopeForSubWorkflow($query, int $subWorkflowId)
    {
        return $query->where('sub_workflow_id', $subWorkflowId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
