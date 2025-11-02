<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodeExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'execution_id',
        'node_id',
        'status',
        'input',
        'output',
        'error_message',
        'error_details',
        'started_at',
        'finished_at',
        'execution_order',
        'retry_count',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'output' => 'array',
            'error_details' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'execution_order' => 'integer',
            'retry_count' => 'integer',
        ];
    }

    public function execution(): BelongsTo
    {
        return $this->belongsTo(Execution::class);
    }

    public function workflowExecution(): BelongsTo
    {
        return $this->belongsTo(WorkflowExecution::class, 'execution_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'error';
    }

    public function markAsRunning(): void
    {
        $this->update(['status' => 'running', 'started_at' => now()]);
    }

    public function markAsSuccessful(array $output = []): void
    {
        $this->update([
            'status' => 'success',
            'output' => $output,
            'finished_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $error,
            'finished_at' => now(),
        ]);
    }

    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->finished_at) {
            return null;
        }

        return $this->finished_at->diffInSeconds($this->started_at);
    }
}
