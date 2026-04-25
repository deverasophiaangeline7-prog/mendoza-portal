<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Section;
use App\Models\BehaviorReport;
use App\Models\NkpEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportCardController extends Controller
{
    /**
     * 1. THE MENU 
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $sections = Section::all();
            return view('report-card-index', compact('sections'));
        }

        if ($user->role === 'teacher') {
            $sections = Section::where('teacher_id', $user->user_id)->get();
            
            if ($sections->count() > 1) {
                return view('report-card-index', compact('sections'));
            } 
            
            if ($sections->count() === 1) {
                return redirect()->route('reportcard.show', $sections->first()->section_id);
            }
        }

        return abort(403, 'You do not have any sections assigned.');
    }

    /**
     * 2. THE STUDENT LIST
     */
    public function show($section_id)
    {
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
     * 3. THE GRADE SHEET (Branching Logic)
     * This replaces your old showStudent method.
     */
    public function showStudent($student_id)
    {
        $student = Student::with('section')->findOrFail($student_id);
        
        // Security check: Only assigned teachers can manage
        $canManage = false;
        if (Auth::user()->role === 'teacher' && $student->section) {
            $canManage = $student->section->teacher_id == Auth::user()->user_id;
        }

        $gradeLevel = strtoupper($student->section ? $student->section->grade_level : '');
        $isNkp = in_array($gradeLevel, ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY']);

        // --- BRANCH 1: NKP STUDENTS (Nursery, Kinder, Prep) ---
        if ($isNkp) {
            $existingEvaluations = NkpEvaluation::where('student_id', $student_id)
                ->get()
                ->keyBy('skill')
                ->toArray();

            return view('nkp-report-card', [
                'studentName' => strtoupper($student->last_name . ', ' . $student->first_name),
                'sectionName' => $student->section ? strtoupper($gradeLevel . ' - ' . $student->section->section_name) : 'UNASSIGNED',
                'student_id' => $student_id,
                'savedEvaluations' => $existingEvaluations,
                'canManage' => $canManage
            ]);
        }

        // --- BRANCH 2: GRADE 1 TO 6 STUDENTS ---
        $subjects = ['Language', 'English', 'Mathematics', 'Makabansa', 'GMRC', 'Music', 'Art', 'PE', 'Health'];
        $coreValues = ['Maka-Diyos', 'Makatao', 'Maka-kalikasan', 'Maka-bansa'];

        $existingGrades = Grade::where('student_id', $student_id)->get()->keyBy('subject_name')->toArray();
        $existingBehaviors = BehaviorReport::where('student_id', $student_id)->get()->keyBy('core_value')->toArray();

        return view('student-report-card', [
            'studentName' => strtoupper($student->last_name . ', ' . $student->first_name),
            'sectionName' => $student->section ? strtoupper($gradeLevel . ' - ' . $student->section->section_name) : 'UNASSIGNED',
            'student_id' => $student_id,
            'subjects' => $subjects,
            'coreValues' => $coreValues,
            'savedGrades' => $existingGrades,
            'savedBehaviors' => $existingBehaviors,
            'canManage' => $canManage
        ]);
    }

    public function showParentReportCard()
    {
        $parentId = Auth::id();
        $student = \App\Models\Student::where('user_id', $parentId)->first();

        if (!$student) {
            return "No student record linked to Parent Account (user_id): " . $parentId;
        }

        return $this->showStudent($student->student_id);
    }

    /**
     * 4. THE SAVE ENGINE (Handles both standard and NKP data)
     */
    public function store(Request $request)
    {
        $student_id = $request->input('student_id');
        $grades = $request->input('grades');
        $behaviors = $request->input('behaviors');
        $nkpEvaluations = $request->input('nkp_evaluations'); // Added for NKP

        // 1. Save Numeric Grades (Grades 1-6)
        if ($grades) {
            foreach ($grades as $subject => $data) {
                Grade::updateOrCreate(
                    ['student_id' => $student_id, 'subject_name' => $subject],
                    [
                        'q1' => $data['q1'] ?? null, 'q2' => $data['q2'] ?? null,
                        'q3' => $data['q3'] ?? null, 'q4' => $data['q4'] ?? null,
                        'final_grade' => $data['final_grade'] ?? null,
                        'remarks' => $data['remarks'] ?? null
                    ]
                );
            }
        }

        // 2. Save Observed Values (Grades 1-6)
        if ($behaviors) {
            foreach ($behaviors as $value => $data) {
                BehaviorReport::updateOrCreate(
                    ['student_id' => $student_id, 'core_value' => $value],
                    [
                        'q1' => $data['q1'] ?? null, 'q2' => $data['q2'] ?? null,
                        'q3' => $data['q3'] ?? null, 'q4' => $data['q4'] ?? null,
                    ]
                );
            }
        }

        // 3. Save NKP Checklist Evaluations (Nursery, Kinder, Prep)
        if ($nkpEvaluations) {
            foreach ($nkpEvaluations as $skill => $data) {
                NkpEvaluation::updateOrCreate(
                    ['student_id' => $student_id, 'skill' => $skill],
                    [
                        'category' => $data['category'] ?? 'General',
                        'q1' => $data['q1'] ?? null, 'q2' => $data['q2'] ?? null,
                        'q3' => $data['q3'] ?? null, 'q4' => $data['q4'] ?? null,
                    ]
                );
            }
        }

        return response()->json(['message' => 'Saved Successfully!']);
    }
}