<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Section;
use App\Models\BehaviorReport;
use App\Models\NkpEvaluation;
use App\Models\User; 
use App\Models\AuditLog;
use App\Notifications\GradeUploaded; 
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification; 

class ReportCardController extends Controller
{
    /**
     * 1. THE MENU 
     */
    public function index()
    {
        $user = Auth::user();

        // Custom sorting string to keep NKP on top and Grades 1-6 in order
        $orderLogic = "
            CASE 
                WHEN grade_level IN ('Nursery', 'NURSERY') THEN 1
                WHEN grade_level IN ('Kindergarten', 'Kinder', 'KINDER') THEN 2
                WHEN grade_level IN ('Preparatory', 'Prep', 'PREPARATORY') THEN 3
                ELSE 4 
            END ASC
        ";

        if ($user->role === 'admin') {
            $sections = Section::orderByRaw($orderLogic)
                ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
                ->orderBy('section_name', 'asc')
                ->get();
                
            return view('report-card-index', compact('sections'));
        }

        if ($user->role === 'teacher') {
            $sections = Section::where('teacher_id', $user->user_id)
                ->orderByRaw($orderLogic)
                ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
                ->orderBy('section_name', 'asc')
                ->get();
            
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
     */
    public function showStudent($student_id)
    {
        $student = Student::with('section')->findOrFail($student_id);
        
        // Security check
        $canManage = false;
        if (Auth::user()->role === 'teacher' && $student->section) {
            $canManage = $student->section->teacher_id == Auth::user()->user_id;
        }

        $gradeLevel = strtoupper($student->section ? $student->section->grade_level : '');
        $isNkp = in_array($gradeLevel, ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREPARATORY']);

        // --- GET THE ACTIVE SCHOOL YEAR ---
        $activeYear = SchoolYear::where('status', 'active')->first();
        $activeYearId = $activeYear ? $activeYear->id : null;

        // --- BRANCH 1: NKP STUDENTS ---
        if ($isNkp) {
            $existingEvaluations = NkpEvaluation::where('student_id', $student_id)
                ->where('school_year_id', $activeYearId) 
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

        $existingGrades = Grade::where('student_id', $student_id)
            ->where('school_year_id', $activeYearId)
            ->get()->keyBy('subject_name')->toArray();
            
        $existingBehaviors = BehaviorReport::where('student_id', $student_id)
            ->where('school_year_id', $activeYearId)
            ->get()->keyBy('core_value')->toArray();

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
        $nkpEvaluations = $request->input('nkp_evaluations');

        // --- GET THE ACTIVE SCHOOL YEAR ---
        $activeYear = SchoolYear::where('status', 'active')->first();
        
        // Safety check: if no active year exists, stop them from saving
        if (!$activeYear) {
            return response()->json(['message' => 'Error: No active school year found!'], 400);
        }
        
        $activeYearId = $activeYear->id;

        // 1. Save Numeric Grades (Grades 1-6)
        if ($grades) {
            foreach ($grades as $subject => $data) {
                Grade::updateOrCreate(
                    ['student_id' => $student_id, 'subject_name' => $subject, 'school_year_id' => $activeYearId],
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
                    ['student_id' => $student_id, 'core_value' => $value, 'school_year_id' => $activeYearId],
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
                    ['student_id' => $student_id, 'skill' => $skill, 'school_year_id' => $activeYearId],
                    [
                        'category' => $data['category'] ?? 'General',
                        'q1' => $data['q1'] ?? null, 'q2' => $data['q2'] ?? null,
                        'q3' => $data['q3'] ?? null, 'q4' => $data['q4'] ?? null,
                    ]
                );
            }
        }

        // 4. NOTIFY THE PARENT (Custom Table Logic)
        $student = Student::find($student_id);

        if ($student && $student->user_id) {
            \App\Models\Notification::create([
                'user_id'    => $student->user_id,
                'title'      => 'Grades Uploaded',
                'message'    => 'New grades have been posted for ' . $student->first_name . '.',
                'type'       => 'grade_upload',
                'is_read'    => 0,
                'created_at' => now(), 
            ]);
        }

        if ($student) {
            $studentName = strtoupper($student->last_name . ', ' . $student->first_name);
            $sectionName = $student->section ? strtoupper($student->section->grade_level . ' - ' . $student->section->section_name) : 'UNASSIGNED';

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Report Card Updated',
                'description' => Auth::user()->username . " updated the report card for {$studentName} ({$sectionName})."
            ]);
        }

        return response()->json(['message' => 'Saved Successfully!']);
    }

    public function importBatch(Request $request, $section_id)
    {
        $request->validate([
            'quarter' => ['required', 'in:q1,q2,q3,q4'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt,xlsx'], 
        ]);

        $quarter = $request->input('quarter');
        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        if (!is_file($path)) {
            return redirect()->back()->with('error', 'Unable to read the uploaded CSV file.');
        }

        $activeYear = SchoolYear::where('status', 'active')->first();
        if (!$activeYear) {
            return redirect()->back()->with('error', 'No active school year found.');
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return redirect()->back()->with('error', 'Unable to open the uploaded CSV file.');
        }

        $processedStudents = 0;
        $updatedValues = 0;

        $sectionStudents = Student::where('section_id', $section_id)->get();

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $studentName = isset($row[1]) ? trim((string) $row[1]) : '';
            if ($studentName === '') {
                continue;
            }

            $cleanCsvName = str_replace([',', ' '], '', strtolower($studentName));

            $student = $sectionStudents->first(function ($s) use ($cleanCsvName) {
                $dbName1 = str_replace(' ', '', strtolower($s->first_name . $s->last_name));
                $dbName2 = str_replace(' ', '', strtolower($s->last_name . $s->first_name));
                
                return $cleanCsvName === $dbName1 || $cleanCsvName === $dbName2;
            });

            if (!$student) {
                continue;
            }

            $processedStudents++;

            $subjects = [
                'Language'    => isset($row[6]) ? trim((string) $row[6]) : '',
                'English'     => isset($row[7]) ? trim((string) $row[7]) : '',
                'Mathematics' => isset($row[8]) ? trim((string) $row[8]) : '',
                'Makabansa'   => isset($row[9]) ? trim((string) $row[9]) : '', 
                'GMRC'        => isset($row[10]) ? trim((string) $row[10]) : '',
                'MAPEH'       => isset($row[11]) ? trim((string) $row[11]) : '',
                'Music'       => isset($row[12]) ? trim((string) $row[12]) : '',
                'Art'         => isset($row[13]) ? trim((string) $row[13]) : '', 
                'PE'          => isset($row[14]) ? trim((string) $row[14]) : '',
                'Health'      => isset($row[15]) ? trim((string) $row[15]) : '',
            ];

            foreach ($subjects as $subjectName => $rawValue) {
                $gradeValue = trim((string) $rawValue);
                if ($gradeValue === '') {
                    continue;
                }

                $existingGrade = Grade::where('student_id', $student->student_id)
                    ->where('subject_name', $subjectName)
                    ->where('school_year_id', $activeYear->id)
                    ->first();

                if ($existingGrade) {
                    $existingGrade->fill([
                        'q1' => $quarter === 'q1' ? $gradeValue : ($existingGrade->q1 ?? null),
                        'q2' => $quarter === 'q2' ? $gradeValue : ($existingGrade->q2 ?? null),
                        'q3' => $quarter === 'q3' ? $gradeValue : ($existingGrade->q3 ?? null),
                        'q4' => $quarter === 'q4' ? $gradeValue : ($existingGrade->q4 ?? null),
                    ])->save();
                } else {
                    Grade::create([
                        'student_id' => $student->student_id,
                        'school_year_id' => $activeYear->id,
                        'subject_name' => $subjectName,
                        'q1' => $quarter === 'q1' ? $gradeValue : null,
                        'q2' => $quarter === 'q2' ? $gradeValue : null,
                        'q3' => $quarter === 'q3' ? $gradeValue : null,
                        'q4' => $quarter === 'q4' ? $gradeValue : null,
                        'final_grade' => null,
                        'remarks' => null,
                    ]);
                }

                $updatedValues++;
            }
        }

        fclose($handle);

        return redirect()->route('reportcard.show', ['section_id' => $section_id])
            ->with('success', "Import successful. {$processedStudents} student row(s) processed and {$updatedValues} grade value(s) updated.");
    }

    public function archivedIndex($school_year_id)
    {
        $schoolYear = SchoolYear::findOrFail($school_year_id);
        
        $gradeStudentIds = Grade::where('school_year_id', $school_year_id)->pluck('student_id')->toArray();
        $nkpStudentIds = NkpEvaluation::where('school_year_id', $school_year_id)->pluck('student_id')->toArray();
        
        $allStudentIds = array_unique(array_merge($gradeStudentIds, $nkpStudentIds));
        
        $students = Student::whereIn('student_id', $allStudentIds)->orderBy('last_name')->get();
        
        $histories = \App\Models\StudentHistory::where('school_year_id', $school_year_id)
            ->whereIn('student_id', $allStudentIds)
            ->get()
            ->keyBy('student_id');
        
        return view('archived-students-list', compact('students', 'schoolYear', 'histories'));
    }

    /**
     * VIEW AN ARCHIVED REPORT CARD (READ-ONLY)
     */
    public function archivedShowStudent($student_id, $school_year_id)
    {
        $student = Student::findOrFail($student_id);
        $schoolYear = SchoolYear::findOrFail($school_year_id);
        
        $hasNkp = NkpEvaluation::where('student_id', $student_id)->where('school_year_id', $school_year_id)->exists();

        $canManage = false; 

        if ($hasNkp) {
            $existingEvaluations = NkpEvaluation::where('student_id', $student_id)
                ->where('school_year_id', $school_year_id) 
                ->get()->keyBy('skill')->toArray();
            
            return view('nkp-report-card', [
                'studentName' => strtoupper($student->last_name . ', ' . $student->first_name),
                'sectionName' => 'ARCHIVED - SY ' . $schoolYear->school_year, 
                'student_id' => $student_id,
                'savedEvaluations' => $existingEvaluations,
                'canManage' => $canManage
            ]);
        } else {
             $existingGrades = Grade::where('student_id', $student_id)
                ->where('school_year_id', $school_year_id)
                ->get()->keyBy('subject_name')->toArray();
                
            $existingBehaviors = BehaviorReport::where('student_id', $student_id)
                ->where('school_year_id', $school_year_id)
                ->get()->keyBy('core_value')->toArray();

             return view('student-report-card', [
                'studentName' => strtoupper($student->last_name . ', ' . $student->first_name),
                'sectionName' => 'ARCHIVED - SY ' . $schoolYear->school_year, 
                'student_id' => $student_id,
                'subjects' => ['Language', 'English', 'Mathematics', 'Makabansa', 'GMRC', 'Music', 'Art', 'PE', 'Health'],
                'coreValues' => ['Maka-Diyos', 'Makatao', 'Maka-kalikasan', 'Maka-bansa'],
                'savedGrades' => $existingGrades,
                'savedBehaviors' => $existingBehaviors,
                'canManage' => $canManage
            ]);
        }
    }
}