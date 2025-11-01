<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        return $this->notificationService->getNotifications($request->user()->id);
    }

    public function markAsRead(Request $request, $id)
    {
        return $this->notificationService->markAsRead($id);
    }

    public function markAllAsRead(Request $request)
    {
        return $this->notificationService->markAllAsRead($request->user()->id);
    }

    public function destroy(Request $request, $id)
    {
        return $this->notificationService->deleteNotification($id);
    }

    public function getSettings(Request $request)
    {
        return $this->notificationService->getSettings($request->user()->id);
    }

    public function updateSettings(Request $request)
    {
        return $this->notificationService->updateSettings($request->user()->id, $request->all());
    }

    public function getChannels(Request $request)
    {
        return $this->notificationService->getChannels($request->user()->id);
    }

    public function createChannel(Request $request)
    {
        return $this->notificationService->createChannel($request->user()->id, $request->all());
    }

    public function deleteChannel(Request $request, $id)
    {
        return $this->notificationService->deleteChannel($id);
    }
}
