<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Execution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'workflow_id',
        'triggered_by',
        'status',
        'mode',
        'input_data',
        'output_data',
        'error_message',
        'metadata',
        'started_at',
        'finished_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_data' => 'array',
            'output_data' => 'array',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * The workflow that owns the execution.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * The user who triggered the execution.
     */
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    /**
     * The node executions for this execution.
     */
    public function nodeExecutions(): HasMany
    {
        return $this->hasMany(NodeExecution::class)->orderBy('execution_order');
    }

    /**
     * Scope a query to only include executions with a specific status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include executions with a specific mode.
     */
    public function scopeOfMode($query, string $mode)
    {
        return $query->where('mode', $mode);
    }

    /**
     * Scope a query to only include successful executions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed executions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Scope a query to only include running executions.
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Check if execution is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if execution is running.
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if execution was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if execution failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'error';
    }

    /**
     * Check if execution was cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Mark execution as running.
     */
    public function markAsRunning(): void
    {
        $this->update(['status' => 'running', 'started_at' => now()]);
    }

    /**
     * Mark execution as successful.
     */
    public function markAsSuccessful(array $outputData = []): void
    {
        $this->update([
            'status' => 'success',
            'output_data' => $outputData,
            'finished_at' => now(),
        ]);
    }

    /**
     * Mark execution as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $errorMessage,
            'finished_at' => now(),
        ]);
    }

    /**
     * Cancel the execution.
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Get the execution duration in seconds.
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->finished_at) {
            return null;
        }

        return $this->finished_at->diffInSeconds($this->started_at);
    }

    /**
     * Check if execution is complete.
     */
    public function isComplete(): bool
    {
        return in_array($this->status, ['success', 'error', 'cancelled']);
    }
}
