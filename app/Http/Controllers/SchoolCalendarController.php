<?php

namespace App\Http\Controllers;

use App\Models\SchoolCalendar;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class SchoolCalendarController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $dbEvents = \App\Models\SchoolCalendar::all();
    $eventsData = [];

    foreach ($dbEvents as $event) {
        $timeParts = $event->time ? explode(' - ', $event->time) : ['', ''];
        $eventsData[$event->start_date] = [
            'name'       => $event->event_title,
            'start_time' => $timeParts[0] ?? '',
            'end_time'   => $timeParts[1] ?? '',
            'ps'         => $event->description,
        ];
    }

    $announcementImages = \App\Models\AnnouncementImage::where('status', 'active')->get();

    // --- ROLE-BASED TRAFFIC CONTROL ---
    if ($user->role === 'admin') {
        return view('dashboard', compact('eventsData', 'announcementImages', 'user'));
    } 
    
    if ($user->role === 'teacher') {
        return view('teacher.dashboard', compact('eventsData', 'announcementImages', 'user'));
    }

    if ($user->role === 'parent') {
        // Since you are using user_id for parents, we find the student linked to this ID
        $student = \App\Models\Student::where('user_id', $user->user_id)->first();

        // If no student is found, you might want to handle that error
        if (!$student) {
            return "Error: No student record linked to this parent account.";
        }

        // We pass the $student variable so the parent dashboard can show their name/grade
        return view('parent.dashboard', compact('eventsData', 'announcementImages', 'user', 'student'));
    }

    return redirect('/');
}

    public function store(Request $request)
    {
        $combinedTime = $request->start_time . ' - ' . $request->end_time;

        // 1. Save or Update the Event
        $event = \App\Models\SchoolCalendar::updateOrCreate(
            ['start_date' => $request->start_date],
            [
                'event_title' => $request->event_title,
                'description' => $request->description,
                'time'        => $combinedTime,
            ]
        );

        // --- NEW AUDIT LOG LOGIC ---
        // We check if Laravel created a brand new row, or just updated an old one
        $actionType = $event->wasRecentlyCreated ? 'Create Event' : 'Update Event';
        $logDescription = $event->wasRecentlyCreated 
            ? Auth::user()->username . ' created a new calendar event: ' . $request->event_title 
            : Auth::user()->username . ' updated the calendar event: ' . $request->event_title;

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $actionType,
            'description' => $logDescription
        ]);
        // ---------------------------

        // 2. TRIGGER NOTIFICATIONS
        $usersToNotify = User::whereIn('role', ['parent', 'teacher'])->get();

        foreach ($usersToNotify as $user) {
            $user->notifyUser(
                'New School Event', 
                "A calendar event has been set: " . $request->event_title, 
                'announcement'
            );
        }

        // 3. RETURN RESPONSE
        return response()->json(['message' => 'Event saved and tracked!']);
    }
    public function edit(SchoolCalendar $schoolCalendar)
    {
        return view('calendar.edit', compact('schoolCalendar'));
    }

    public function update(Request $request, SchoolCalendar $schoolCalendar)
    {
        $request->validate([
            'event_title' => 'required|string|max:255',
            'start_date'  => 'required|date',
        ]);

        $schoolCalendar->update([
            'event_title' => $request->event_title,
            'description' => $request->description,
            'start_date'  => $request->start_date,
            'time'        => $request->start_time . ' - ' . $request->end_time,
        ]);

        return redirect()->route('dashboard')->with('success', 'Event updated!');
    }

    public function destroy($date)
    {
        $event = \App\Models\SchoolCalendar::where('start_date', $date)->first();
        
        if ($event) {
            // --- NEW AUDIT LOG LOGIC ---
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Delete Event',
                'description' => Auth::user()->username . ' deleted the calendar event: ' . $event->event_title . ' (' . $date . ')'
            ]);
            // ---------------------------

            $event->delete();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['error' => 'Event not found'], 404);
    }
}