<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'version_number',
        'workflow_data',
        'created_by',
        'change_notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'workflow_data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function activate(): void
    {
        $this->workflow->versions()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
