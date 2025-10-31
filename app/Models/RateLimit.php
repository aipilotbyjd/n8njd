<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'type',
        'attempts',
        'max_attempts',
        'decay_minutes',
        'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'decay_minutes' => 'integer',
            'locked_until' => 'datetime',
        ];
    }

    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');

        if ($this->attempts >= $this->max_attempts) {
            $this->lock();
        }
    }

    public function lock(): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($this->decay_minutes)
        ]);
    }

    public function reset(): void
    {
        $this->update([
            'attempts' => 0,
            'locked_until' => null
        ]);
    }

    public function remainingAttempts(): int
    {
        return max(0, $this->max_attempts - $this->attempts);
    }

    public function isRateLimited(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }
}
