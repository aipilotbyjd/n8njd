<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'name',
        'cron_expression',
        'status',
        'last_run_at',
        'next_run_at',
        'run_count',
        'settings',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
            'run_count' => 'integer',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDue(): bool
    {
        return $this->next_run_at && $this->next_run_at->isPast();
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    public function deactivate(): void
    {
        $this->update(['status' => 'inactive']);
    }

    public function markAsRun(): void
    {
        $this->increment('run_count');
        $this->update(['last_run_at' => now()]);
    }

    public function calculateNextRun(): void
    {
        // Logic to calculate next run based on cron expression
        // This would typically use a cron parser library
    }
}
