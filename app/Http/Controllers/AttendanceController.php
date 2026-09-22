<?php

namespace App\Http\Controllers;

use App\Models\Attendance; 
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'parent') {
            $student = \App\Models\Student::where('user_id', $user->user_id)->first();
            if ($student && $student->section_id) {
                return redirect()->route('attendance.show', $student->section_id);
            }
            return "No child record found for this account.";
        }

        if ($user->role === 'teacher') {
            $sections = Section::where('teacher_id', $user->user_id)
                ->orderByRaw("
                    CASE 
                        WHEN grade_level IN ('Nursery', 'NURSERY') THEN 1
                        WHEN grade_level IN ('Kindergarten', 'Kinder', 'KINDER') THEN 2
                        WHEN grade_level IN ('Preparatory', 'Prep', 'PREPARATORY') THEN 3
                        ELSE 4 
                    END ASC
                ")
                ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
                ->orderBy('grade_level', 'asc')
                ->orderBy('section_name', 'asc')
                ->get();

            if ($sections->count() > 1) {
                return view('teacher.select_section', compact('sections'));
            } 
            
            if ($sections->count() === 1) {
                return redirect()->route('attendance.show', $sections->first()->section_id);
            }
        }

        $sections = Section::orderByRaw("
            CASE 
                WHEN grade_level IN ('Nursery', 'NURSERY') THEN 1
                WHEN grade_level IN ('Kindergarten', 'Kinder', 'KINDER') THEN 2
                WHEN grade_level IN ('Preparatory', 'Prep', 'PREPARATORY') THEN 3
                ELSE 4 
            END ASC
        ")
        ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
        ->orderBy('grade_level', 'asc')
        ->orderBy('section_name', 'asc')
        ->get();

        return view('attendance', compact('sections')); 
    }

    public function show($idOrSlug)
    {
        $user = Auth::user();
        $section = Section::find($idOrSlug);

        if (!$section) {
            $gradeMap = [
                'nursery'      => 'Nursery',
                'kinder'       => 'Kindergarten', 
                'kindergarten' => 'Kindergarten',
                'preparatory'  => 'Preparatory',
                'grade-1'      => '1',
                'grade-2'      => '2',
                'grade-3'      => '3',
                'grade-4'      => '4',
                'grade-5'      => '5',
                'grade-6'      => '6',
                '5'            => '5',
            ];

            $dbGradeLevel = $gradeMap[$idOrSlug] ?? $idOrSlug;
            $query = Section::where('grade_level', 'like', "%$dbGradeLevel%");

            if ($user->role === 'teacher') {
                $section = $query->where('teacher_id', $user->user_id)->first();
            } elseif ($user->role === 'parent') {
                $student = Student::where('user_id', $user->user_id)->first();
                $section = Section::find($student->section_id);
            } else {
                $section = $query->first();
            }
        }

        if (!$section) {
            return "Error: Section not found or you are not assigned to this grade.";
        }

        $students = Student::where('section_id', $section->section_id)->get();

        $attendances = Attendance::whereIn('student_id', $students->pluck('student_id'))
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->get();

        $existingDates = $attendances->pluck('attendance_date')->unique()->values()->toArray();

        $statusMap = ['present' => 1, 'absent' => 2, 'late' => 3, 'excused' => 4];
        $attendanceMap = [];
        
        foreach($attendances as $att) {
            $attendanceMap[$att->student_id][$att->attendance_date] = $statusMap[strtolower($att->status)] ?? 0;
        }

        $grade = in_array(strtoupper($section->grade_level), ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREP', 'PREPARATORY']) 
                    ? strtolower($section->grade_level) 
                    : 'grade-' . $section->grade_level;

        return view('section-attendance', [
            'grade'         => $grade,
            'displayName'   => strtoupper($section->grade_level . ' - ' . $section->section_name),
            'students'      => $students,
            'existingDates' => $existingDates,
            'attendanceMap' => $attendanceMap,
            'canManage'     => $user->role === 'teacher' && $section->teacher_id == $user->user_id
        ]);
    }

    public function store(Request $request)
    {
        // 1. Relaxed validation so it doesn't reject "excused" or other formats
        $request->validate([
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required',
            'attendance.*.date' => 'required|date',
            'attendance.*.status' => 'required', 
        ]);

        $records = $request->input('attendance', []);
        if (empty($records)) {
            return response()->json(['message' => 'No records to save.'], 400);
        }

        $firstRecord = $records[0];
        $sampleStudent = Student::find($firstRecord['student_id']);
        $sectionName = $sampleStudent->section->section_name ?? 'Unknown Section';
        $gradeLevel = $sampleStudent->grade_level ?? '';
        $attendanceDate = $firstRecord['date'];

        foreach ($records as $record) {
            
            // 2. Bulletproof mapping: Handles BOTH numbers ('4') and text ('excused') safely
            $rawStatus = strtolower(trim((string)$record['status']));
            $textStatus = match($rawStatus) {
                '1', 'present' => 'Present',
                '2', 'absent'  => 'Absent',
                '3', 'late'    => 'Late',
                '4', 'excused' => 'Excused',
                default        => 'Present' // Fallback
            };

            // 3. Save to the database
            $attendance = Attendance::updateOrCreate(
                [
                    'student_id'      => $record['student_id'],
                    'attendance_date' => $record['date']
                ],
                [
                    'status' => $textStatus
                ]
            );

        // 4. ONLY notify if the record is brand new OR the status actually changed
if ($attendance->wasRecentlyCreated || $attendance->wasChanged('status')) {
    
    $student = Student::find($record['student_id']);
    
    if ($student && $student->user_id) {
        $parent = User::find($student->user_id);
        
        if ($parent) {
            $typeLabel = strtoupper($textStatus);
            
            // Format the date using Philippine Time
            $formattedDate = \Carbon\Carbon::parse($record['date'])
                                ->timezone('Asia/Manila')
                                ->format('F j');
            
            $parent->notifyUser(
                'Attendance Alert', 
                "Notice: {$student->first_name} was marked {$typeLabel} for {$formattedDate}.", 
                'attendance'
            );
        }
    }
}
        }

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Attendance Submitted',
            'description' => Auth::user()->username . " submitted attendance for {$gradeLevel} - {$sectionName} on {$attendanceDate}."
        ]);

        return response()->json(['message' => 'Saved Successfully!']);
    }
}