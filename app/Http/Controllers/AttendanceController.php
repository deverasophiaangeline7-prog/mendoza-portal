<?php

namespace App\Http\Controllers;

use App\Models\Attendance; // IMPORTANT: This connects the new table
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display the main grid or redirect teachers.
     */
        public function index()
{
    $user = Auth::user();

    // 1. THE PARENT REDIRECT
    if ($user->role === 'parent') {
        // Find the child linked to this parent's account
        $student = \App\Models\Student::where('user_id', $user->user_id)->first();

        if ($student) {
            // Convert "Grade 5" to "grade-5" to match your slug routes
            $gradeSlug = strtolower(str_replace(' ', '-', $student->grade_level));
            
            // Redirect them straight to the sheet, bypassing the yellow grid
            return redirect()->route('attendance.show', $gradeSlug);
        }

        return "No child record found for this account.";
    }

    // 2. THE TEACHER REDIRECT (Keep your existing logic here)
    if ($user->role === 'teacher') {
        $sections = Section::where('teacher_id', $user->user_id)->get();

        if ($sections->count() > 1) {
            return view('teacher.select_section', compact('sections'));
        } 
        
        if ($sections->count() === 1) {
            $gradeSlug = strtolower(str_replace(' ', '-', $sections->first()->grade_level));
            return redirect()->route('attendance.show', $gradeSlug);
        }
    }

    // 3. THE ADMIN VIEW (Image #2)
    // Only the Admin will ever see the grid of all grades
    return view('attendance'); 
}

    /**
     * Display the attendance list for a specific grade.
     */
    public function show($grade)
    {
        $user = Auth::user();

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

        $dbGradeLevel = $gradeMap[$grade] ?? $grade;

        $query = Section::where('grade_level', 'like', "%$dbGradeLevel%");

        if ($user->role === 'teacher') {
            $section = $query->where('teacher_id', $user->user_id)->first();
        } elseif ($user->role === 'parent') {
            $student = Student::where('user_id', $user->user_id)->first();
            $section = Section::find($student->section_id);
        } else {
            $section = $query->first();
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
            $attendanceMap[$att->student_id][$att->attendance_date] = $statusMap[$att->status] ?? 0;
        }

        return view('section-attendance', [
            'grade'         => $grade,
            'displayName'   => strtoupper($section->grade_level . ' - ' . $section->section_name),
            'students'      => $students,
            'existingDates' => $existingDates,
            'attendanceMap' => $attendanceMap,
            'canManage'     => $user->role === 'teacher' && $section->teacher_id == $user->user_id
        ]);
    }

    /**
     * THE NEW SAVE FUNCTION: Stores the clicks into the database
     */
    public function store(Request $request)
    {
        // 1. Changed 'records' to 'attendance' to match your JavaScript
        $records = $request->input('attendance', []);

        foreach ($records as $record) {
            // updateOrCreate ensures we don't save duplicates. 
            Attendance::updateOrCreate(
                [
                    'student_id'      => $record['student_id'],
                    // 2. Changed to match the 'date' key sent by your JavaScript
                    'attendance_date' => $record['date'] 
                ],
                [
                    'status' => $record['status']
                ]
            );
        }

        return response()->json(['message' => 'Saved Successfully!']);
    }
}