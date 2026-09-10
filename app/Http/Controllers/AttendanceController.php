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
        // 1. Properly validate the nested array objects coming from JavaScript
        $request->validate([
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required',
            'attendance.*.date' => 'required|date',
            'attendance.*.status' => 'required|in:1,2,3,4', // Frontend sends 1,2,3,4
        ], [
            'attendance.*.status.in' => 'All attendance fields must be filled out before saving.',
        ]);

        $records = $request->input('attendance', []);
        if (empty($records)) {
            return response()->json(['message' => 'No records to save.'], 400);
        }

        // Map frontend numbers to database text
        $numericToText = [
            '1' => 'Present',
            '2' => 'Absent',
            '3' => 'Late',
            '4' => 'Excused'
        ];

        $firstRecord = $records[0];
        $sampleStudent = Student::find($firstRecord['student_id']);
        $sectionName = $sampleStudent->section->section_name ?? 'Unknown Section';
        $gradeLevel = $sampleStudent->grade_level ?? '';
        $attendanceDate = $firstRecord['date'];

        foreach ($records as $record) {
            $textStatus = $numericToText[$record['status']] ?? 'Present';

            Attendance::updateOrCreate(
                [
                    'student_id'      => $record['student_id'],
                    'attendance_date' => $record['date']
                ],
                [
                    'status' => $textStatus
                ]
            );

            if (in_array($textStatus, ['Absent', 'Late'])) {
                $student = Student::find($record['student_id']);
                if ($student && $student->user_id) {
                    $parent = User::find($student->user_id);
                    if ($parent) {
                        $typeLabel = strtoupper($textStatus);
                        $parent->notifyUser(
                            'Attendance Alert', 
                            "Notice: {$student->first_name} was marked {$typeLabel} today.", 
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