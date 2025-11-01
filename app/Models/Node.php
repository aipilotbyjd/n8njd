<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'workflow_id',
        'type',
        'node_type',
        'name',
        'description',
        'position',
        'configuration',
        'credentials',
        'position_index',
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
            'position' => 'array',
            'configuration' => 'array',
            'credentials' => 'encrypted:array',
            'position_index' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The workflow that owns the node.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * The node executions for this node.
     */
    public function nodeExecutions(): HasMany
    {
        return $this->hasMany(NodeExecution::class);
    }

    /**
     * Scope a query to only include active nodes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include nodes of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include nodes of a specific node type.
     */
    public function scopeOfNodeType($query, string $nodeType)
    {
        return $query->where('node_type', $nodeType);
    }

    /**
     * Check if node is a trigger node.
     */
    public function isTrigger(): bool
    {
        return $this->type === 'trigger';
    }

    /**
     * Check if node is an action node.
     */
    public function isAction(): bool
    {
        return $this->type === 'action';
    }

    /**
     * Check if node is a condition node.
     */
    public function isCondition(): bool
    {
        return $this->type === 'condition';
    }

    /**
     * Get the node's X coordinate.
     */
    public function getXAttribute(): float
    {
        return $this->position['x'] ?? 0;
    }

    /**
     * Get the node's Y coordinate.
     */
    public function getYAttribute(): float
    {
        return $this->position['y'] ?? 0;
    }

    /**
     * Set the node's position.
     */
    public function setPosition(float $x, float $y): void
    {
        $this->update([
            'position' => array_merge($this->position ?? [], [
                'x' => $x,
                'y' => $y,
            ]),
        ]);
    }
}
