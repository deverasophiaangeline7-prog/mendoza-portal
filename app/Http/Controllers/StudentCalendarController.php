<?php

namespace App\Http\Controllers;

use App\Models\SchoolCalendar;
use App\Models\Student;
use App\Models\EventParticipant;
use Illuminate\Http\Request;

class StudentCalendarController extends Controller
{
    public function index()
{
    // This fetches the events AND their participants AND the student names all at once
    $events = SchoolCalendar::with('participants.student')->orderBy('start_date', 'asc')->get();
    
    $students = Student::orderBy('last_name', 'asc')->get();

    return view('teacher-calendar', compact('events', 'students'));
}

public function studentCalendar()
    {
        $userId = auth()->id();
        
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
    if (auth()->user()->role === 'admin') { 
        abort(403, 'Admins cannot modify student participation.');
    }
    $request->validate([
        'event_id'    => 'required',
        'student_ids' => 'required|array',
        'roles'       => 'nullable|array', // CHANGE THIS FROM 'required' TO 'nullable'
    ]);

    // If the teacher didn't pick a role, we use [null] so the loop still runs once
    $roles = $request->roles ?? [null]; 

    foreach ($request->student_ids as $student_id) {
        foreach ($roles as $role) {
            \App\Models\EventParticipant::firstOrCreate([
                'event_id'   => $request->event_id,
                'student_id' => $student_id,
                'role'       => $role,
            ]);
        }
    }

    return back()->with('success', 'Student assignments updated!');
}

public function destroyParticipant($id)
{
    // Fix: Use direct role check to match the addParticipant method
    if (auth()->user()->role === 'admin') {
        abort(403, 'Admins cannot remove participants.');
    }

    $participant = \App\Models\EventParticipant::findOrFail($id);
    $participant->delete();

    return back()->with('success', 'Participant removed successfully!');
}
}