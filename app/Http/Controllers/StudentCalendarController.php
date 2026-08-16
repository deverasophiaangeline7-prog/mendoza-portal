<?php

namespace App\Http\Controllers;

use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\User;
use App\Models\EventParticipant;
use Illuminate\Http\Request;

class StudentCalendarController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Fetch all events with participant relationships
        $events = SchoolCalendar::with('participants.student')->orderBy('start_date', 'asc')->get();
        
        // Filter students list based on user role
        if ($user->role === 'admin') {
            $students = Student::orderBy('last_name', 'asc')->get();
        } else {
            // FIX: Use 'teacher_id' and the custom '$user->user_id' based on your models
            $students = Student::whereHas('section', function ($query) use ($user) {
                $query->where('teacher_id', $user->user_id); 
            })->orderBy('last_name', 'asc')->get();
        }

        return view('teacher-calendar', compact('events', 'students'));
    }

    public function studentCalendar()
    {
        $userId = auth()->id(); // This works if configured right, but let's be safe below
        
        $student = \App\Models\Student::where('user_id', $userId)->first();

        if (!$student) {
            return view('my-calendar', ['events' => collect()]);
        }

        $studentId = $student->student_id;

        $events = SchoolCalendar::whereHas('participants', function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })
        ->with(['participants' => function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])
        ->orderBy('start_date', 'asc')
        ->get();

        return view('parent.my-calendar', compact('events', 'student'));
    }

    public function addParticipant(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') { 
            abort(403, 'Admins cannot modify student participation.');
        }

        $request->validate([
            'event_id'    => 'required',
            'student_ids' => 'required|array',
            'roles'       => 'nullable|array',
        ]);

        // FIX: Strict Backend Validation using 'teacher_id' and '$user->user_id'
        $allowedStudentIds = Student::whereHas('section', function ($query) use ($user) {
            $query->where('teacher_id', $user->user_id);
        })->pluck('student_id')->toArray();

        foreach ($request->student_ids as $student_id) {
            if (!in_array($student_id, $allowedStudentIds)) {
                abort(403, 'Unauthorized: You can only assign students that belong to your assigned advisory section.');
            }
        }

        // 1. Fetch the event details to include the title in the message
        $event = SchoolCalendar::find($request->event_id);
        $roles = $request->roles ?? [null]; 

        foreach ($request->student_ids as $student_id) {
            // 2. Fetch the student record to find the linked parent (user_id)
            // Explicitly use 'student_id' as the column
            $student = Student::where('student_id', $student_id)->first();

            foreach ($roles as $role) {
                EventParticipant::firstOrCreate([
                    'event_id'   => $request->event_id,
                    'student_id' => $student_id,
                    'role'       => $role,
                ]);
            }

            // 3. TRIGGER NOTIFICATION
            if ($student && $student->user_id) {
                $parent = User::find($student->user_id);
                if ($parent) {
                    $parent->notifyUser(
                        'Event Participation',
                        "{$student->first_name} has been assigned a role in the event: " . ($event->event_title ?? 'School Event'),
                        'event_participation'
                    );
                }
            }
        }

        return back()->with('success', 'Student assignments updated!');
    }

    public function destroyParticipant($id)
    {
        if (auth()->user()->role === 'admin') {
            abort(403, 'Admins cannot remove participants.');
        }

        $participant = EventParticipant::findOrFail($id);
        $participant->delete();

        return back()->with('success', 'Participant removed successfully!');
    }
}