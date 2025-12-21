<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(20);
        
        return $this->sendPaginatedResponse($notifications, 'notifications retrieved');
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $user = $request->user();
        
        // Check if notification belongs to user
        if ($notification->user_id !== $user->id) {
            return $this->sendError('unauthorized');
        }
        
        $notification->markAsRead();
        
        return $this->sendResponse(new NotificationResource($notification), 'notification marked as read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        
        $user->notifications()->unread()->update(['read_at' => now()]);
        
        return $this->sendResponse([], 'all notifications marked as read');
    }
}