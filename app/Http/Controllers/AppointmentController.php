<?php
// app/Http/Controllers/AppointmentController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Teacher;
use App\Models\TeacherSchedule;
use App\Models\User;
use App\Models\Student;

class AppointmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'teacher') {
            $incomingRequests = Appointment::with(['parent.student'])
                ->where('teacher_id', $user->user_id)
                ->where('status', 'pending')
                ->where('created_by', '!=', $user->user_id)
                ->orderBy('appointment_date', 'asc')
                ->get();

            $mySentRequests = Appointment::with(['parent.student'])
                ->where('teacher_id', $user->user_id)
                ->where('status', 'pending')
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
                ->where('status', 'pending')
                ->where('created_by', '!=', $user->user_id)
                ->orderBy('appointment_date', 'asc')
                ->get();

            $mySentRequests = Appointment::with(['parent.student'])
                ->where('parent_id', $user->user_id)
                ->where('status', 'pending')
                ->where('created_by', $user->user_id)
                ->orderBy('appointment_date', 'asc')
                ->get();

            $bookedAppointments = collect();
            $adviserSchedule = collect();
            $adviserName = null;
            if ($student && $student->section && $student->section->teacher) {
                $adviserSchedule = TeacherSchedule::where('teacher_id', $student->section->teacher->user_id)->get();
                $bookedAppointments = Appointment::with(['parent.student'])
                    ->where('teacher_id', $student->section->teacher->user_id)
                    ->where('status', 'booked')
                    ->get();
                $adviserName = $student->section->teacher->name ?? ($student->section->teacher->first_name . ' ' . $student->section->teacher->last_name);
            }

            return view('appointment_parent', compact('incomingRequests', 'mySentRequests', 'adviserSchedule', 'student', 'adviserName', 'bookedAppointments'));
            
        } elseif ($user->role === 'admin') {
            $advisersList = [
                ['section' => 'NKP', 'name' => 'Adviser Name', 'user_id' => null],
                ['section' => 'Grade 1 - Faith', 'name' => 'Adviser Name', 'user_id' => null],
                ['section' => 'Grade 2 - Hope', 'name' => 'Adviser Name', 'user_id' => null],
                ['section' => 'Grade 3 - Love', 'name' => 'Adviser Name', 'user_id' => null],
                ['section' => 'Grade 4 - Grace', 'name' => 'Adviser Name', 'user_id' => null],
                ['section' => 'Grade 5 - Light', 'name' => 'Adviser Name', 'user_id' => null],
                ['section' => 'Grade 6 - Wisdom', 'name' => 'Adviser Name', 'user_id' => null],
            ];

            $teachers = \App\Models\Teacher::with('section')->get();

            foreach ($advisersList as $key => $adviser) {
                foreach ($teachers as $teacher) {
                    if ($teacher->section) {
                        $constructedName = 'Grade ' . $teacher->section->grade_level . ' - ' . $teacher->section->section_name;
                        
                        if ($adviser['section'] === $constructedName || $adviser['section'] === $teacher->section->section_name) {
                            $advisersList[$key]['name'] = $teacher->first_name . ' ' . $teacher->last_name;
                            $advisersList[$key]['user_id'] = $teacher->user_id;
                        }
                    }
                }
            }

            $selectedTeacherId = request('teacher_id');
            $scheduleRows = [];
            if ($selectedTeacherId) {
                $teacherSchedules = TeacherSchedule::where('teacher_id', $selectedTeacherId)->get();
                foreach ($teacherSchedules as $schedule) {
                    $scheduleRows[$schedule->date . '|' . $schedule->time_slot] = $schedule->status;
                }
            }

            return view('appointment_admin', [
                'advisers' => $advisersList,
                'scheduleRows' => $scheduleRows,
                'selectedTeacherId' => $selectedTeacherId,
            ]);
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
        } else {
            $student = Student::with('section.teacher')->where('user_id', auth()->id())->first();
            if (!$student || !$student->section || !$student->section->teacher) {
                return redirect()->back()->with('error', 'Unable to determine your adviser for this appointment.');
            }
            $validated['teacher_id'] = $student->section->teacher->user_id;
            $validated['parent_id'] = auth()->id();
        }

        $validated['status'] = 'pending';
        // NEW: Track exactly who pressed the "Submit" button
        $validated['created_by'] = auth()->id(); 

        Appointment::create($validated);

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

        return back()->with('success', 'Appointment request approved.');
    }

    public function decline(Appointment $appointment)
    {
        $appointment->update(['status' => 'declined']);
        return back()->with('success', 'Appointment request declined.');
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $appointment->update([
            'status' => 'reschedule',
            'reschedule_reason' => $request->reason
        ]);

        return back()->with('success', 'Reschedule request sent to parent.');
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