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

        // Get most recent notification per booking (student view) ordered by latest update
        $notifications = Notification::where('user_id', $userId)
            ->select('*')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique('booking_id')
            ->take(10)
            ->values();

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

    // Professor notification methods
    public function getProfessorNotifications()
    {
        $professorId = Auth::guard('professor')->user()->Prof_ID ?? null;
        
        if (!$professorId) {
            return response()->json(['notifications' => []]);
        }
        // Fetch all relevant notifications first (limit to recent range for performance if needed)
        $all = Notification::where('user_id', $professorId)
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by booking (null booking_id treated as unique via fallback key) then select the latest (by updated_at) per group
        $latestPerBooking = $all->groupBy(function ($n) {
            return $n->booking_id ? ('b_'.$n->booking_id) : ('solo_'.$n->id);
        })->map(function ($items) {
            return $items->sortByDesc('updated_at')->first();
        });

        // Final ordering: strictly newest first using updated_at (fallback created_at)
        $ordered = $latestPerBooking->sortByDesc(function ($n) {
            return $n->updated_at ?? $n->created_at;
        })->values()->take(10);

        return response()->json(['notifications' => $ordered]);
    }

    public function markProfessorAsRead(Request $request)
    {
        $notificationId = $request->get('notification_id');
        
        $notification = Notification::find($notificationId);
        
        if ($notification && $notification->user_id == (Auth::guard('professor')->user()->Prof_ID ?? null)) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function markAllProfessorAsRead()
    {
        $professorId = Auth::guard('professor')->user()->Prof_ID ?? null;
        
        if (!$professorId) {
            return response()->json(['success' => false]);
        }

        Notification::where('user_id', $professorId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function getProfessorUnreadCount()
    {
        $professorId = Auth::guard('professor')->user()->Prof_ID ?? null;
        
        if (!$professorId) {
            return response()->json(['unread_count' => 0]);
        }
        
        $unreadCount = Notification::where('user_id', $professorId)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    // Admin notification methods - for admin to see ALL notifications system-wide
    public function getAdminNotifications()
    {
        // Get all notifications with booking and user details
        $allNotifications = DB::table('notifications as n')
            ->leftJoin('t_consultation_bookings as b', 'b.Booking_ID', '=', 'n.booking_id')
            ->leftJoin('professors as bp', 'bp.Prof_ID', '=', 'b.Prof_ID')
            ->leftJoin('t_student as bs', 'bs.Stud_ID', '=', 'b.Stud_ID')
            ->leftJoin('t_student as s', 's.Stud_ID', '=', 'n.user_id')
            ->leftJoin('professors as p', 'p.Prof_ID', '=', 'n.user_id')
            ->select([
                'n.id',
                'n.user_id',
                'n.booking_id',
                'n.type',
                'n.title',
                'n.message',
                'n.is_read',
                'n.created_at',
                'n.updated_at',
                DB::raw('COALESCE(s.Name, p.Name) as user_name'),
                'bp.Name as professor_name',
                'bs.Name as student_name',
                DB::raw('CASE WHEN s.Stud_ID IS NOT NULL THEN 1 ELSE 0 END as is_student_notification')
            ])
            ->orderBy('n.updated_at', 'desc')
            ->get();

        // Filter to show only one notification per booking (prefer student notifications)
        $uniqueNotifications = collect();
        $seenBookings = [];

        foreach ($allNotifications as $notification) {
            $bookingId = $notification->booking_id;

            if (!isset($seenBookings[$bookingId])) {
                // First encountered (currently most recent due to initial ordering)
                $seenBookings[$bookingId] = $notification;
                $uniqueNotifications->push($notification);
                continue;
            }

            $existing = $seenBookings[$bookingId];

            // Decide replacement: pick the one with newer updated_at.
            // If same timestamp, prefer student notification for clearer admin context.
            if (
                $notification->updated_at > $existing->updated_at ||
                ($notification->updated_at == $existing->updated_at && $notification->is_student_notification && !$existing->is_student_notification)
            ) {
                // Replace existing entry in collection
                $uniqueNotifications = $uniqueNotifications->filter(function ($item) use ($existing) {
                    return $item->id !== $existing->id;
                });
                $uniqueNotifications->push($notification);
                $seenBookings[$bookingId] = $notification;
            }
        }

        // Sort by updated_at desc and limit
        $notifications = $uniqueNotifications->sortByDesc('updated_at')->take(50)->values();

        // Transform messages for admin view
        $notifications = $notifications->map(function ($notification) {
            // Create admin-appropriate messages
            $adminMessage = $notification->message;
            
            if ($notification->student_name && $notification->professor_name) {
                // Create admin-specific messages based on notification type
                switch ($notification->type) {
                    case 'accepted':
                        $adminMessage = "{$notification->student_name}'s consultation with {$notification->professor_name} has been accepted.";
                        break;
                    case 'completed':
                        $adminMessage = "{$notification->student_name}'s consultation with {$notification->professor_name} has been completed.";
                        break;
                    case 'rescheduled':
                        $adminMessage = "{$notification->student_name}'s consultation with {$notification->professor_name} has been rescheduled.";
                        break;
                    case 'cancelled':
                        $adminMessage = "{$notification->student_name}'s consultation with {$notification->professor_name} has been cancelled.";
                        break;
                    case 'booking_request':
                        $adminMessage = "{$notification->student_name} has booked a consultation with {$notification->professor_name}.";
                        break;
                    default:
                        // For any other types, try to replace "Your" with student name if it exists
                        if (strpos($adminMessage, 'Your ') === 0 && $notification->student_name) {
                            $adminMessage = str_replace('Your ', "{$notification->student_name}'s ", $adminMessage);
                        }
                        // Remove admin-inappropriate phrases
                        $adminMessage = str_replace([
                            'Please rate your experience.',
                            'Please rate your experience',
                            'Rate your experience.',
                            'Rate your experience'
                        ], '', $adminMessage);
                        $adminMessage = trim($adminMessage);
                        break;
                }
            }
            
            // Update the message for admin view
            $notification->message = $adminMessage;
            
            return $notification;
        });

        return response()->json(['notifications' => $notifications]);
    }

    public function markAdminAsRead(Request $request)
    {
        $notificationId = $request->get('notification_id');
        
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function markAllAdminAsRead()
    {
        // Admin can mark ALL notifications as read
        Notification::where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function getAdminUnreadCount()
    {
        // Count ALL unread notifications for admin overview
        $unreadCount = Notification::where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }
}
