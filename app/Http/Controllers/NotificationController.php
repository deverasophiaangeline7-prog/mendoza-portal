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
            default               => redirect()->route('dashboard')
        };
    }
}