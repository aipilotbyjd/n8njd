<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Variable extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'workflow_id',
        'created_by',
        'key',
        'value',
        'type',
        'description',
        'is_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeForWorkflow($query, int $workflowId)
    {
        return $query->where('workflow_id', $workflowId);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('workflow_id');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isEncrypted(): bool
    {
        return $this->is_encrypted;
    }

    public function getDecodedValue(): mixed
    {
        return match ($this->type) {
            'json' => json_decode($this->value, true),
            'number' => (float) $this->value,
            'boolean' => (bool) $this->value,
            default => $this->value,
        };
    }
}
