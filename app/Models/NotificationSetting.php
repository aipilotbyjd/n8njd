<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'event_type',
        'channels',
        'is_enabled',
        'conditions',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'conditions' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    public function scopeForEvent($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function enable(): void
    {
        $this->update(['is_enabled' => true]);
    }

    public function disable(): void
    {
        $this->update(['is_enabled' => false]);
    }

    public function addChannel(int $channelId): void
    {
        $channels = $this->channels ?? [];
        if (!in_array($channelId, $channels)) {
            $channels[] = $channelId;
            $this->update(['channels' => $channels]);
        }
    }

    public function removeChannel(int $channelId): void
    {
        $channels = array_filter($this->channels ?? [], fn ($id) => $id !== $channelId);
        $this->update(['channels' => array_values($channels)]);
    }
}
