<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credential extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
        'type',
        'data',
        'description',
        'is_active',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function getDecryptedData(): ?array
    {
        return $this->data;
    }
}
