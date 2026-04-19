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

        if ($user->role === 'teacher') {
            // Query sections based on teacher_id
            $sections = Section::where('teacher_id', $user->user_id)->get();

            // NKP Teacher: Show the selection grid (Image 2)
            if ($sections->count() > 1) {
                return view('teacher.select_section', compact('sections'));
            } 
            
            // Regular Teacher: Go straight to the sheet (Image 3)
            if ($sections->count() === 1) {
                $gradeSlug = strtolower(str_replace(' ', '-', $sections->first()->grade_level));
                return redirect()->route('attendance.show', $gradeSlug);
            }
        }

        return view('attendance'); 
    }

    /**
     * Display the attendance list for a specific grade.
     */
    public function show($grade)
    {
        // 1. Improved Mapping to handle "kinder" slug
        $gradeMap = [
            'nursery'     => 'Nursery',
            'kinder'      => 'Kindergarten', 
            'kindergarten'=> 'Kindergarten',
            'preparatory' => 'Preparatory',
            'grade-1'     => '1',
            'grade-2'     => '2',
            'grade-3'     => '3',
            'grade-4'     => '4',
            'grade-5'     => '5',
            'grade-6'     => '6',
        ];

        $dbGradeLevel = $gradeMap[$grade] ?? ucfirst($grade);

        // 2. Search for the section
        $section = Section::where('grade_level', 'like', "%$dbGradeLevel%")->first();

        if (!$section) {
            return "Error: Could not find a section in the database for grade: " . $dbGradeLevel;
        }

        // 3. Fetch Students (We need the full object so the Blade can access student_id)
        $students = Student::where('section_id', $section->section_id)->get();

        // 4. LOAD EXISTING ATTENDANCE DATA FROM DATABASE
        $attendances = Attendance::whereIn('student_id', $students->pluck('student_id'))
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->get();

        // Grab the dates that already have attendance recorded
        $existingDates = $attendances->pluck('attendance_date')->unique()->values()->toArray();

        // Create a map so the frontend knows which circles to color in
        $statusMap = ['present' => 1, 'absent' => 2, 'late' => 3, 'excused' => 4];
        $attendanceMap = [];
        
        foreach($attendances as $att) {
            // This builds an array like: [ student_id => [ date => status_number ] ]
            $attendanceMap[$att->student_id][$att->attendance_date] = $statusMap[$att->status] ?? 0;
        }

        return view('section-attendance', [
            'grade'         => $grade,
            'displayName'   => strtoupper($section->grade_level . ' - ' . $section->section_name),
            'students'      => $students,
            'existingDates' => $existingDates,
            'attendanceMap' => $attendanceMap,
            'canManage'     => Auth::user()->role === 'teacher' && $section->teacher_id == Auth::user()->user_id
        ]);
    }
    
    /**
     * Legacy Create Method (Kept for your other pages)
     */
    public function create()
    {
        $user = Auth::user();
        
        $sectionIds = Section::where('teacher_id', $user->user_id)->pluck('section_id');
        $students = Student::whereIn('section_id', $sectionIds)->get();
        $date = now()->format('Y-m-d');
        
        return view('teacher-attendance', compact('students', 'date'));
    }

    /**
     * THE NEW SAVE FUNCTION: Stores the clicks into the database
     */
    public function store(Request $request)
    {
        $records = $request->input('records', []);

        foreach ($records as $record) {
            // updateOrCreate ensures we don't save duplicates. 
            // If the date exists, it updates it. If not, it creates it.
            Attendance::updateOrCreate(
                [
                    'student_id'      => $record['student_id'],
                    'attendance_date' => $record['attendance_date']
                ],
                [
                    'status' => $record['status']
                ]
            );
        }

        return response()->json(['message' => 'Saved Successfully!']);
    }
}