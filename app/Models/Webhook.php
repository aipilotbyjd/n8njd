<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'name',
        'path',
        'http_method',
        'is_active',
        'secret',
        'headers',
        'description',
        'call_count',
        'last_called_at',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'is_active' => 'boolean',
            'call_count' => 'integer',
            'last_called_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfMethod($query, string $method)
    {
        return $query->where('http_method', strtoupper($method));
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function incrementCallCount(): void
    {
        $this->increment('call_count');
        $this->update(['last_called_at' => now()]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function getFullPathAttribute(): string
    {
        return $this->path;
    }
}
