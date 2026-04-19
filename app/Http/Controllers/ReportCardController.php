<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportCardController extends Controller
{
    /**
     * 1. THE MENU 
     * Handles the initial click on "Report Card" in the sidebar.
     */
    public function index()
    {
        $user = Auth::user();

        // ADMIN LOGIC: Show every section in the school
        if ($user->role === 'admin') {
            $sections = Section::all();
            return view('report-card-index', compact('sections'));
        }

        // TEACHER LOGIC
        if ($user->role === 'teacher') {
            $sections = Section::where('teacher_id', $user->user_id)->get();
            
            // NKP Advisor (Multiple Sections): Show the menu
            if ($sections->count() > 1) {
                return view('report-card-index', compact('sections'));
            } 
            
            // Grade 1-6 Teacher (Single Section): Fast-track to the student list
            if ($sections->count() === 1) {
                return redirect()->route('reportcard.show', $sections->first()->section_id);
            }
        }

        return abort(403, 'You do not have any sections assigned.');
    }

    /**
     * 2. THE STUDENT LIST
     * Shows the Male/Female roster for a specific section.
     */
    public function show($section_id)
    {
        // Get all students in this section
        $students = Student::where('section_id', $section_id)->get();
        $section = Section::findOrFail($section_id);

        $sectionName = strtoupper($section->grade_level . ' - ' . $section->section_name);

        return view('section-report-card', [
            'students' => $students,
            'sectionName' => $sectionName,
            'section_id' => $section_id
        ]);
    }

    /**
     * 3. THE GRADE SHEET
     * Shows the individual auto-computing table for one student.
     */
    public function showStudent($student_id)
    {
        $student = Student::with('section')->findOrFail($student_id);
        
        // Define your subjects list
        $subjects = [
            'Filipino', 'English', 'Mathematics', 'Science', 
            'Araling Panlipunan', 'EsP', 'TLE', 'MAPEH'
        ];

        // Fetch any existing grades already saved in the DB
        $existingGrades = Grade::where('student_id', $student_id)
            ->get()
            ->keyBy('subject_name')
            ->toArray();

        // Check if the current user is allowed to edit this student
        $canManage = false;
        if (Auth::user()->role === 'teacher' && $student->section) {
            $canManage = $student->section->teacher_id == Auth::user()->user_id;
        } elseif (Auth::user()->role === 'admin') {
            $canManage = true; // Admins can always view/edit if needed
        }

        return view('student-report-card', [
            'studentName' => strtoupper($student->last_name . ', ' . $student->first_name),
            'sectionName' => $student->section ? strtoupper($student->section->grade_level . ' - ' . $student->section->section_name) : 'UNASSIGNED',
            'student_id' => $student_id,
            'subjects' => $subjects,
            'savedGrades' => $existingGrades,
            'canManage' => $canManage
        ]);
    }



public function showParentReportCard()
{
    // This will now correctly grab '11' (for Jocelyn)
    $parentId = Auth::id();

    // Find the student where 'user_id' matches the parent
    $student = \App\Models\Student::where('user_id', $parentId)->first();

    if (!$student) {
        // This is our debug message
        return "No student record linked to Parent Account (user_id): " . $parentId;
    }

    return $this->showStudent($student->student_id);
}


    /**
     * 4. THE SAVE ENGINE
     * Handles the AJAX/Fetch request to save grades into the database.
     */
    public function store(Request $request)
    {
        $student_id = $request->input('student_id');
        $grades = $request->input('grades');

        foreach ($grades as $subject => $data) {
            Grade::updateOrCreate(
                ['student_id' => $student_id, 'subject_name' => $subject],
                [
                    'q1' => $data['q1'],
                    'q2' => $data['q2'],
                    'q3' => $data['q3'],
                    'q4' => $data['q4'],
                    'final_grade' => $data['final_grade'],
                    'remarks' => $data['remarks']
                ]
            );
        }

        return response()->json(['message' => 'Grades Saved Successfully!']);
    }
}