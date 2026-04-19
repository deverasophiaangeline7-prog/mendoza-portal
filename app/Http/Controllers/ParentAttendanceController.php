<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ParentAttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = $user->student; // Finds the parent's child

        if (!$student) {
            return back()->with('error', 'No student linked to this account.');
        }

        // Get current month info
        $now = Carbon::now();
        $daysInMonth = $now->daysInMonth;
        $monthName = $now->format('F');
        $firstDayOfWeek = $now->copy()->firstOfMonth()->dayOfWeek; // 0=Sun, 1=Mon, etc.

        // Fetch attendance from the database
        $attendanceRecords = Attendance::where('student_id', $student->student_id)
            ->whereMonth('attendance_date', $now->month)
            ->whereYear('attendance_date', $now->year)
            ->get()
            ->pluck('status', 'attendance_date'); // Turns it into ["2026-03-15" => "present"]

        // Build the days array for the calendar
        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateString = $now->copy()->day($i)->format('Y-m-d');
            // If there's a record in the DB, use it. Otherwise, mark it 'none'.
            $days[$i] = $attendanceRecords[$dateString] ?? 'none'; 
        }

        return view('parent.parent_attendance', compact('days', 'monthName', 'student', 'firstDayOfWeek'));
    }
}