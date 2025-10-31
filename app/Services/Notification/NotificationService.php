<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\NotificationChannel;
use App\Models\NotificationSetting;
use Illuminate\Support\Str;

class NotificationService
{
    public function getNotifications(string $userId)
    {
        return Notification::where('user_id', $userId)->get();
    }

    public function markAsRead(string $id)
    {
        $notification = Notification::find($id);
        if ($notification) {
            $notification->read_at = now();
            $notification->save();
        }

        return $notification;
    }

    public function markAllAsRead(string $userId)
    {
        return Notification::where('user_id', $userId)->update(['read_at' => now()]);
    }

    public function deleteNotification(string $id)
    {
        return Notification::destroy($id);
    }

    public function getSettings(string $userId)
    {
        return NotificationSetting::where('user_id', $userId)->get();
    }

    public function updateSettings(string $userId, array $settings)
    {
        foreach ($settings as $key => $value) {
            NotificationSetting::updateOrCreate(
                ['user_id' => $userId, 'key' => $key],
                ['value' => $value]
            );
        }

        return $this->getSettings($userId);
    }

    public function getChannels(string $userId)
    {
        return NotificationChannel::where('user_id', $userId)->get();
    }

    public function createChannel(string $userId, array $channel): NotificationChannel
    {
        $channel['id'] = Str::uuid();
        $channel['user_id'] = $userId;

        return NotificationChannel::create($channel);
    }

    public function deleteChannel(string $id): bool
    {
        return NotificationChannel::destroy($id);
    }
}
