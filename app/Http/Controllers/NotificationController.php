<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $userId = Auth::user()->Stud_ID ?? null;
        
        if (!$userId) {
            return response()->json(['notifications' => []]);
        }

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    public function markAsRead(Request $request)
    {
        $notificationId = $request->get('notification_id');
        
        $notification = Notification::find($notificationId);
        
        if ($notification && $notification->user_id == (Auth::user()->Stud_ID ?? null)) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function markAllAsRead()
    {
        $userId = Auth::user()->Stud_ID ?? null;
        
        if (!$userId) {
            return response()->json(['success' => false]);
        }

        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $userId = Auth::user()->Stud_ID ?? null;
        
        if (!$userId) {
            return response()->json(['count' => 0]);
        }

        $count = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
