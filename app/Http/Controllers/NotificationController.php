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

    // SMART REDIRECT
    return match($notification->type) {
        'attendance'   => redirect()->route('attendance.index'),
        'announcement' => redirect()->route('dashboard'),
        'grade_upload' => redirect()->route('parent.reportcard'),
        'event_participation' => redirect()->route('student.calendar'),
        default               => redirect()->route('dashboard')
    };
}
}