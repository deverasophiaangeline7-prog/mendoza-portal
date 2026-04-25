<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ParentAttendanceController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Find the specific child linked to this parent account
        $student = $user->student; 

        if (!$student) {
            return back()->with('error', 'No student linked to this account.');
        }

        // Get month and year from URL parameters, default to current month/year if none exist
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        // Create a Carbon date object for the requested month
        $currentDate = \Carbon\Carbon::createFromDate($year, $month, 1);

        $daysInMonth = $currentDate->daysInMonth;
        $monthName = $currentDate->format('F Y'); 
        $firstDayOfWeek = $currentDate->copy()->firstOfMonth()->dayOfWeek;

        // Generate Prev/Next dates to pass to the buttons
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        // Fetch ONLY this specific student's attendance from the database for the selected month
        $attendances = Attendance::where('student_id', $student->student_id)
            ->whereMonth('attendance_date', $currentDate->month)
            ->whereYear('attendance_date', $currentDate->year)
            ->get();

        // Safely map the dates and grab the text status directly from the database!
        $rawAttendance = [];
        foreach ($attendances as $att) {
            $dateOnly = date('Y-m-d', strtotime($att->attendance_date));
            // Force it to lowercase just to perfectly match your CSS classes
            $rawAttendance[$dateOnly] = strtolower($att->status); 
        }

        // Build the days array for the calendar grid
        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateString = $currentDate->copy()->day($i)->format('Y-m-d');
            
            // If there's a status in the DB for this date, use it. Otherwise, use 'none'.
            $days[$i] = $rawAttendance[$dateString] ?? 'none'; 
        }

        // Pass the new prevDate and nextDate to the view
        return view('parent.parent_attendance', compact('days', 'monthName', 'student', 'firstDayOfWeek', 'prevDate', 'nextDate'));
    }
}