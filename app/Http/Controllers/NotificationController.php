<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = Notification::where('notification_id', $id)
                                    ->where('user_id', Auth::id())
                                    ->firstOrFail();

        $notification->update(['is_read' => 1]);

        // ✨ THE MAGIC FIX: If type starts with 'group_chat:', grab the ID and redirect to the chat!
        if (str_starts_with($notification->type, 'group_chat:')) {
            $groupId = explode(':', $notification->type)[1];
            return redirect()->route('messages.show', ['id' => $groupId]);
        }

        $user = Auth::user();
        $isTeacher = strtolower(trim($user->role)) === 'teacher';

        // SMART ROLE-AWARE REDIRECT
        return match($notification->type) {
            'attendance'          => redirect()->route($isTeacher ? 'attendance.index' : 'parent.attendance'),
            'announcement'        => redirect()->route('dashboard'),
            'grade_upload'        => redirect()->route($isTeacher ? 'reportcard.index' : 'parent.reportcard'),
            'event_participation', 
            'event', 
            'school event'        => redirect()->route($isTeacher ? 'student.calendar.index' : 'student.calendar'),
            'appointment'         => redirect()->route('appointments.index'), 
            default               => redirect()->route('dashboard')
        };
    }

    public function fetchNotifications()
    {
        $user = auth()->user();
        if (!$user) return response()->json([]);

        $filteredNotifications = $user->customNotifications
            ->unique('notification_id') 
            ->sortByDesc('created_at') 
            ->filter(function($notification) use ($user) {
                $role = strtolower(trim($user->role));
                $type = strtolower(trim($notification->type));

                if ($role === 'teacher') {
                    return in_array($type, ['announcement', 'event', 'school event', 'calendar', 'deadline_alert', 'appointment']); 
                }
                return true; 
            })->values(); 

       
        $formatted = $filteredNotifications->map(function($notif) {
            return [
                'notification_id' => $notif->notification_id,
                'type' => strtolower(trim($notif->type)),
                'title' => $notif->title,
                'message' => $notif->message,
                'time_ago' => $notif->created_at ? $notif->created_at->setTimezone('Asia/Manila')->diffForHumans() : 'Just now'
            ];
        });

        return response()->json($formatted);
    }
}