<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Teacher;
use App\Models\TeacherSchedule;
use App\Models\User;
use App\Models\Student;
use App\Models\Section;
use App\Models\Notification;

class AppointmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'teacher') {
            $incomingRequests = Appointment::with(['parent.student'])
                ->where('teacher_id', $user->user_id)
                ->whereIn('status', ['pending', 'reschedule'])
                ->where('created_by', '!=', $user->user_id)
                ->orderBy('appointment_date', 'asc')
                ->get();

            $mySentRequests = Appointment::with(['parent.student'])
                ->where('teacher_id', $user->user_id)
                ->whereIn('status', ['pending', 'reschedule'])
                ->where('created_by', $user->user_id)
                ->orderBy('appointment_date', 'asc')
                ->get();

            $bookedAppointments = Appointment::with(['parent.student'])
                ->where('teacher_id', $user->user_id)
                ->where('status', 'booked')
                ->orderBy('appointment_date', 'asc')
                ->get();

            $sectionIds = $user->sections->pluck('section_id');
            $parents = User::where('role', 'parent')
                ->whereHas('student', function ($query) use ($sectionIds) {
                    $query->whereIn('section_id', $sectionIds);
                })
                ->with('student')
                ->get()
                ->sortBy(function ($parent) {
                    return strtoupper($parent->student->last_name ?? $parent->username);
                });

            $schedules = TeacherSchedule::where('teacher_id', $user->user_id)->get();

            return view('appointment_teacher', compact('incomingRequests', 'mySentRequests', 'parents', 'schedules', 'bookedAppointments'));
            
        } elseif ($user->role === 'parent') {
            $student = Student::with('section.teacher')
                ->where('user_id', $user->user_id)
                ->first();

            $incomingRequests = Appointment::with(['parent.student'])
                ->where('parent_id', $user->user_id)
                ->whereIn('status', ['pending', 'reschedule'])
                ->where('created_by', '!=', $user->user_id)
                ->orderBy('appointment_date', 'asc')
                ->get();

            $mySentRequests = Appointment::with(['parent.student'])
                ->where('parent_id', $user->user_id)
                ->whereIn('status', ['pending', 'reschedule'])
                ->where('created_by', $user->user_id)
                ->orderBy('appointment_date', 'asc')
                ->get();

            $bookedAppointments = collect();
            $adviserSchedule = collect();
            $adviserName = null;

            $adviserTeacher = $student?->section?->teacher;
            if (!$adviserTeacher && $student?->section) {
                $adviserTeacher = Teacher::whereIn('advisory', ['1,2,3', 'NKP', 'Nursery', 'Kinder', 'Prep'])
                    ->orWhere('advisory', $student->section->grade_level)
                    ->first();
            }

            if ($student && $adviserTeacher) {
                $adviserSchedule = TeacherSchedule::where('teacher_id', $adviserTeacher->user_id)->get();
                $bookedAppointments = Appointment::with(['parent.student'])
                    ->where('teacher_id', $adviserTeacher->user_id)
                    ->where('status', 'booked')
                    ->get();
                $adviserName = $adviserTeacher->name ?? ($adviserTeacher->first_name . ' ' . $adviserTeacher->last_name);
            }

            return view('appointment_parent', compact('incomingRequests', 'mySentRequests', 'adviserSchedule', 'student', 'adviserName', 'bookedAppointments'));
            
        } elseif ($user->role === 'admin') {
            
            $advisersList = [];

            $nkpTeacher = Teacher::whereIn('advisory', ['1,2,3', 'NKP', 'Nursery', 'Kinder', 'Prep', 'KINDERGARTEN', 'PREPARATORY'])->first();
            $advisersList[] = [
                'section' => 'NKP',
                'name' => $nkpTeacher ? $nkpTeacher->first_name . ' ' . $nkpTeacher->last_name : 'Unassigned',
                'user_id' => $nkpTeacher ? $nkpTeacher->user_id : null,
            ];

            $sections = Section::with('teacher')
                ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
                ->orderBy('section_name', 'asc')
                ->get()
                ->filter(function ($section) {
                    return !in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREP', 'PREPARATORY', 'NKP']);
                });

            foreach ($sections as $section) {
                $teacherProfile = \App\Models\Teacher::where('user_id', $section->teacher_id)->first();
                $advisersList[] = [
                    'section' => 'Grade ' . $section->grade_level . ' - ' . $section->section_name,
                    'name' => $teacherProfile ? $teacherProfile->first_name . ' ' . $teacherProfile->last_name : 'Unassigned',
                    'user_id' => $teacherProfile ? $teacherProfile->user_id : null,
                ];
            }

            $selectedTeacherId = request('teacher_id');
            $scheduleRows = [];
            
            if ($selectedTeacherId) {
                $teacherSchedules = TeacherSchedule::where('teacher_id', $selectedTeacherId)->get();
                foreach ($teacherSchedules as $schedule) {
                    $scheduleRows[$schedule->date . '|' . $schedule->time_slot] = $schedule->status;
                }
            }

            return view('appointment_admin', compact('advisersList', 'scheduleRows', 'selectedTeacherId'));
        }

        return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
    }

    public function store(Request $request)
    {
        $validationRules = [
            'discussion_topic' => 'required|string|max:255',
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ];

        if (auth()->user()->role === 'teacher') {
            $validationRules['parent_id'] = 'required|exists:users,user_id';
        }

        $validated = $request->validate($validationRules);

        if (auth()->user()->role === 'teacher') {
            $validated['teacher_id'] = auth()->id();
            $validated['parent_id'] = $request->parent_id;
            $notifyUserId = $request->parent_id;
        } else {
            $student = Student::with('section.teacher')->where('user_id', auth()->id())->first();
            $teacherUserId = $student?->section?->teacher?->user_id;

            if (!$teacherUserId && $student?->section) {
                $teacher = Teacher::whereIn('advisory', ['1,2,3', 'NKP', 'Nursery', 'Kinder', 'Prep'])
                    ->orWhere('advisory', $student->section->grade_level)
                    ->first();
                $teacherUserId = $teacher?->user_id;
            }

            if (!$teacherUserId) {
                return redirect()->back()->with('error', 'Unable to determine your adviser for this appointment.');
            }

            $validated['teacher_id'] = $teacherUserId;
            $validated['parent_id'] = auth()->id();
            $notifyUserId = $teacherUserId;
        }

        $validated['status'] = 'pending';
        $validated['created_by'] = auth()->id(); 

        Appointment::create($validated);

        Notification::create([
            'user_id' => $notifyUserId,
            'title' => 'New Appointment Request',
            'message' => auth()->user()->name . ' has requested an appointment regarding "' . $validated['discussion_topic'] . '".',
            'type' => 'appointment',
            'is_read' => 0,
        ]);

        return redirect()->back()->with('success', 'Appointment request submitted successfully.');
    }

    public function approve(Appointment $appointment)
    {
        $appointment->update(['status' => 'booked']);

        $startTime = \Carbon\Carbon::parse($appointment->start_time);
        $endTime = \Carbon\Carbon::parse($appointment->end_time);
        $durationMinutes = $startTime->diffInMinutes($endTime);
        $status = $durationMinutes < 60 ? 'booked-half' : 'booked';

        $timeSlot = $startTime->format('gA');
        TeacherSchedule::updateOrCreate(
            [
                'teacher_id' => $appointment->teacher_id,
                'date' => $appointment->appointment_date,
                'time_slot' => $timeSlot,
            ],
            [
                'status' => $status,
            ]
        );

        Notification::create([
            'user_id' => $appointment->created_by,
            'title' => 'Appointment Approved',
            'message' => 'Your appointment request for "' . $appointment->discussion_topic . '" has been approved by ' . auth()->user()->name . '.',
            'type' => 'appointment',
            'is_read' => 0,
        ]);

        return back()->with('success', 'Appointment request approved.');
    }

    public function decline(Request $request, Appointment $appointment)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'suggested_date' => 'nullable|date',
            'suggested_start_time' => 'nullable|date_format:H:i',
            'suggested_end_time' => 'nullable|date_format:H:i|after:suggested_start_time',
        ]);

        $originalCreatorId = $appointment->created_by;

        if ($request->suggested_date && $request->suggested_start_time && $request->suggested_end_time) {
            $appointment->update([
                'status' => 'reschedule',
                'reschedule_reason' => $request->reason,
                'appointment_date' => $request->suggested_date,
                'start_time' => $request->suggested_start_time,
                'end_time' => $request->suggested_end_time,
                'created_by' => auth()->id(), 
            ]);

            $message = auth()->user()->name . ' requested to reschedule. Reason: ' . $request->reason;
            $successResponse = 'Appointment rescheduled and sent back to requests.';
        } else {
            $appointment->update([
                'status' => 'declined',
                'reschedule_reason' => $request->reason
            ]);
            $message = 'Your appointment request for "' . $appointment->discussion_topic . '" was declined by ' . auth()->user()->name . '. Reason: ' . $request->reason;
            $successResponse = 'Appointment request declined successfully.';
        }

        Notification::create([
            'user_id' => $originalCreatorId,
            'title' => 'Appointment Declined/Rescheduled',
            'message' => $message,
            'type' => 'appointment',
            'is_read' => 0,
        ]);

        return back()->with('success', $successResponse);
    }

    public function getAvailability(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer|exists:users,user_id',
        ]);

        $schedules = TeacherSchedule::where('teacher_id', $request->teacher_id)->get(['date', 'time_slot', 'status']);

        return response()->json([
            'success' => true,
            'schedules' => $schedules->map(function ($schedule) {
                return [
                    'date' => $schedule->date,
                    'time' => $schedule->time_slot,
                    'status' => $schedule->status,
                ];
            }),
        ]);
    }

    public function updateAvailability(Request $request) 
    {
        $request->validate([
            'teacher_id' => 'required|integer|exists:users,user_id',
            'schedules' => 'required|array',
            'schedules.*.date' => 'required|date',
            'schedules.*.time' => 'required|string',
            'schedules.*.status' => 'required|string',
        ]);

        foreach ($request->schedules as $schedule) {
            TeacherSchedule::updateOrCreate(
                ['teacher_id' => $request->teacher_id, 'date' => $schedule['date'], 'time_slot' => $schedule['time']],
                ['status' => $schedule['status']]
            );
        }

        return response()->json(['success' => true]);
    }
}