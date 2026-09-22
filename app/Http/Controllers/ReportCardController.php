<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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

// EXCEL & TEACHER IMPORTS
use App\Models\Teacher;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            $teacher = \App\Models\Teacher::where('user_id', $user->user_id)->first();
            
            // If they are a Subject Teacher (e.g., 'GMRC') and NOT an Adviser ('ALL')
            if ($teacher && $teacher->assigned_subject !== 'ALL' && !empty($teacher->assigned_subject)) {
                
                // STRICT OPTION B: Pull ONLY the sections they are explicitly assigned to teach
                $assignedSectionIds = \App\Models\SubjectAssignment::where('teacher_id', $user->user_id)
                    ->pluck('section_id')
                    ->toArray();

                $sections = Section::whereIn('section_id', $assignedSectionIds)
                    ->orderByRaw($orderLogic)
                    ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
                    ->orderBy('section_name', 'asc')
                    ->get();

            } else {
                
                // ADVISERS / NKP TEACHERS: Pull the section where they are the official adviser
                $sections = Section::where('teacher_id', $user->user_id)
                    ->orderByRaw($orderLogic)
                    ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
                    ->orderBy('section_name', 'asc')
                    ->get();
            }
            
            if ($sections->count() > 1) {
                return view('report-card-index', compact('sections'));
            }

            if ($sections->count() === 1) {
                return redirect()->route('reportcard.show', $sections->first()->section_id);
            }
            
            return abort(403, 'You do not have any sections assigned to you.');
        }

        return abort(403, 'Unauthorized access.');
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
        
        // 1. Extract the exact number from the grade level
        preg_match('/\d+/', $gradeLevel, $matches);
        $gradeNum = isset($matches[0]) ? (int)$matches[0] : 0;

        // 2. Dynamically assign subjects based on the curriculum differences
        if ($gradeNum == 1) {
            $subjects = [
                'Language', 
                'Reading and Literacy', 
                'Mathematics', 
                'Makabansa', 
                'GMRC'
            ];
        } elseif ($gradeNum == 2 || $gradeNum == 3) {
            $subjects = [
                'English', 
                'Filipino', 
                'Mathematics', 
                'Makabansa', 
                'GMRC'
            ];
        } elseif ($gradeNum >= 4 && $gradeNum <= 6) {
            $subjects = [
                'Filipino', 
                'English', 
                'Mathematics', 
                'Science', 
                'Araling Panlipunan', 
                'GMRC', 
                'TLE', 
                'MAPEH'
            ];
        } else {
            $subjects = []; // Fallback
        }

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
            'canManage' => $canManage,
            'activeYear' => $activeYear
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
        // BACKEND RESTRICTION: Block Admins and Parents from saving grades
        if (Auth::user()->role !== 'teacher') {
            return response()->json(['message' => 'Unauthorized action. Only teachers can update grades.'], 403);
        }

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

        // 1. Save Numeric Grades
        if ($grades) {
            foreach ($grades as $subject => $data) {
                Grade::updateOrCreate(
                    ['student_id' => $student_id, 'subject_name' => $subject, 'school_year_id' => $activeYearId],
                    [
                        'term1' => $data['term1'] ?? null,
                        'term2' => $data['term2'] ?? null,
                        'term3' => $data['term3'] ?? null,
                        'final_grade' => $data['final_grade'] ?? null,
                        'remarks' => $data['remarks'] ?? null
                    ]
                );
            }
        }

        // 2. Save Observed Values (Grades 1-6) - SAFELY UPDATED TO TERMS!
        if ($behaviors) {
            foreach ($behaviors as $value => $data) {
                BehaviorReport::updateOrCreate(
                    ['student_id' => $student_id, 'core_value' => $value, 'school_year_id' => $activeYearId],
                    [
                        'term1' => $data['term1'] ?? null,
                        'term2' => $data['term2'] ?? null,
                        'term3' => $data['term3'] ?? null,
                    ]
                );
            }
        }

        // 3. Save NKP Checklist Evaluations (Nursery, Kinder, Prep) - SAFELY UPDATED TO TERMS!
        if ($nkpEvaluations) {
            foreach ($nkpEvaluations as $skill => $data) {
                NkpEvaluation::updateOrCreate(
                    ['student_id' => $student_id, 'skill' => $skill, 'school_year_id' => $activeYearId],
                    [
                        'category' => $data['category'] ?? 'General',
                        'term1' => $data['term1'] ?? null,
                        'term2' => $data['term2'] ?? null,
                        'term3' => $data['term3'] ?? null,
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

    /**
     * 5. THE NEW EXCEL SUBJECT IMPORT ENGINE (PhpSpreadsheet + Term Lock + STRICT LRN)
     */
    public function importBatch(Request $request, $section_id)
    {
        $request->validate([
            'subject' => 'required|string',
            'excel_file' => 'required|mimes:xlsx,xls'
        ]);
        
        $teacher = Teacher::where('user_id', Auth::id())->first();
        
        if ($teacher && !str_contains(strtoupper($teacher->assigned_subject), 'ALL') && !empty($teacher->assigned_subject)) {
            if (!str_contains(strtoupper($teacher->assigned_subject), strtoupper($request->subject))) {
                return back()->with('error', 'Unauthorized. You are only allowed to upload grades for: ' . $teacher->assigned_subject);
            }
        }

        $activeYear = SchoolYear::where('status', 'active')->first();
        
        if (!$activeYear) {
            return back()->with('error', 'No active school year found.');
        }

        // ==========================================
        // DYNAMIC TERM CHECK (USING SCHOOL YEAR MODEL)
        // ==========================================
        $currentDate = now();
        $activeTerm = 0;

        // Automatically determine active term based on SchoolYear deadlines
        // Change 'term1_end' to match your actual database column names if they are different (e.g., 'term1_deadline')
        if ($currentDate->lessThanOrEqualTo(\Carbon\Carbon::parse($activeYear->term1_end)->endOfDay())) {
            $activeTerm = 1;
        } elseif ($currentDate->lessThanOrEqualTo(\Carbon\Carbon::parse($activeYear->term2_end)->endOfDay())) {
            $activeTerm = 2;
        } elseif ($currentDate->lessThanOrEqualTo(\Carbon\Carbon::parse($activeYear->term3_end)->endOfDay())) {
            $activeTerm = 3;
        }

        if ($activeTerm === 0) {
            return back()->with('error', 'All grading terms have ended.');
        }

        // --- THE PHPSPREADSHEET APPROACH ---
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('excel_file')->getPathname());
        $targetSheet = null;

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            for ($row = 1; $row <= 20; $row++) {
                $cellValue = (string) $sheet->getCell('A' . $row)->getCalculatedValue();
                
                if (stripos(trim($cellValue), 'Summary of Quarterly Grades') !== false) {
                    $targetSheet = $sheet;
                    break 2;
                }
            }
        }

        if (!$targetSheet) {
            return back()->with('error', 'Could not detect the "Summary of Quarterly Grades" page. Please upload a valid DepEd e-Class Record.');
        }

        // SUBJECT MISMATCH PROTECTOR
        $subjectFound = false;
        $expectedSubject = trim($request->subject);
        $fileName = $request->file('excel_file')->getClientOriginalName();

        if (stripos(str_replace(['_', '-'], ' ', $fileName), $expectedSubject) !== false) {
            $subjectFound = true;
        }

        if (!$subjectFound) {
            for ($r = 1; $r <= 15; $r++) {
                foreach (range('A', 'K') as $col) {
                    $cellValue = (string) $targetSheet->getCell($col . $r)->getCalculatedValue();
                    if (stripos($cellValue, $expectedSubject) !== false) {
                        $subjectFound = true;
                        break 2;
                    }
                }
            }
        }

        if (!$subjectFound) {
            return back()->with('error', "SUBJECT MISMATCH: You selected '{$expectedSubject}', but this Excel file appears to be for a different subject.");
        }

        $processed = 0;
        $errors = []; 
        $highestRow = $targetSheet->getHighestDataRow();

        // PROCESS THE TARGET SHEET
        for ($row = 1; $row <= $highestRow; $row++) {
            $lrn = trim((string) $targetSheet->getCell('A' . $row)->getCalculatedValue());
            $excelName = trim((string) $targetSheet->getCell('B' . $row)->getCalculatedValue());
            
            if (empty($lrn) && empty($excelName)) {
                continue;
            }

            if (!preg_match('/^\d{12}$/', $lrn)) {
                $errors[] = "Row {$row}: Invalid LRN for '{$excelName}'.";
                continue; 
            }

            $term1Val = (string) $targetSheet->getCell('F' . $row)->getCalculatedValue();
            $term2Val = (string) $targetSheet->getCell('J' . $row)->getCalculatedValue();
            $term3Val = (string) $targetSheet->getCell('N' . $row)->getCalculatedValue();

            $term1 = trim($term1Val) !== '' ? trim($term1Val) : null;
            $term2 = trim($term2Val) !== '' ? trim($term2Val) : null;
            $term3 = trim($term3Val) !== '' ? trim($term3Val) : null;

            if ($term1 === null && $term2 === null && $term3 === null) {
                continue;
            }

            $student = Student::where('lrn', trim($lrn))->where('section_id', $section_id)->first();

            if (!$student) {
                $excelName = (string) $targetSheet->getCell('B' . $row)->getCalculatedValue();
                $errors[] = "Row {$row}: LRN {$lrn} does not match.";
                continue; 
            }

            $updateData = [];

            // ==========================================
            // STRICT ACTIVE-TERM-ONLY IMPORT
            // ==========================================
            // It completely ignores the columns for inactive terms
            if ($activeTerm === 1 && $term1 !== null) {
                $updateData['term1'] = $term1;
            } elseif ($activeTerm === 2 && $term2 !== null) {
                $updateData['term2'] = $term2;
            } elseif ($activeTerm === 3 && $term3 !== null) {
                $updateData['term3'] = $term3;
            }

            if (!empty($updateData)) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'subject_name' => $request->subject,
                        'school_year_id' => $activeYear->id,
                    ],
                    $updateData
                );
                $processed++;
            }
        }

        if (count($errors) > 0) {
            $errorMessage = "Import partially completed. {$processed} student(s) updated, but we blocked invalid LRNs: " . implode(" | ", $errors);
            return back()->with('error', $errorMessage);
        }

        return back()->with('success', "{$request->subject} Term {$activeTerm} grades successfully imported. {$processed} student(s) updated.");
    }

    /**
     * 6. EXCEL DOWNLOAD TEMPLATE
     */
    public function downloadTemplate(Request $request, $section_id)
    {
        $request->validate([
            'subject' => 'required|string'
        ]);

        $students = Student::where('section_id', $section_id)
                        ->orderBy('gender', 'desc') // Puts MALE first
                        ->orderBy('last_name', 'asc')
                        ->get();

        $export = new class($students) implements FromArray, WithHeadings {
            private $students;

            public function __construct($students) {
                $this->students = $students;
            }

            public function headings(): array {
                return ['LRN', 'LEARNERS\' NAMES', 'TERM 1', 'TERM 2', 'TERM 3', 'FINAL GRADE', 'DESCRIPTOR', 'REMARK'];
            }

            public function array(): array {
                $data = [];
                foreach($this->students as $student) {
                    $fullName = $student->last_name . ', ' . $student->first_name;
                    if ($student->middle_name) {
                        $fullName .= ' ' . substr($student->middle_name, 0, 1) . '.';
                    }
                    
                    $data[] = [
                        $student->lrn,
                        $fullName,
                        '', '', '', '', '', '' 
                    ];
                }
                return $data;
            }
        };

        $fileName = str_replace(' ', '_', $request->subject) . '_Grade_Template.xlsx';
        return Excel::download($export, $fileName);
    }

    /**
     * 7. ARCHIVES
     */
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
        
        // 1. Fetch the student's historical data for this specific archived year
        $history = \App\Models\StudentHistory::where('student_id', $student_id)
            ->where('school_year_id', $school_year_id)
            ->first();

        // 2. Smart fallback: Use history if available, otherwise guess based on current grade minus 1
        $gradeLevel = '';
        $displaySection = 'ARCHIVED RECORD';

        if ($history) {
            // Use historical data directly
            $gradeLevel = $history->grade_level ? strtoupper(trim($history->grade_level)) : '';
            
            // The section_name already contains the grade level, so just use it directly
            $displaySection = strtoupper($history->section_name);
        }else {
            // Fallback if history row is missing entirely: assume they were 1 grade lower last year
            $curr = strtoupper(trim($student->grade_level));
            
            if ($curr === '1') {
                $gradeLevel = 'PREPARATORY';
            } elseif (is_numeric($curr)) {
                $gradeLevel = (string)max(1, (int)$curr - 1);
            } else {
                $gradeLevel = $curr; 
            }
            
            $displaySection = $student->section ? strtoupper($gradeLevel . ' - ' . $student->section->section_name) : 'ARCHIVED';
        }

        $nkpLevels = ['NURSERY', 'KINDER', 'KINDERGARTEN', 'PREP', 'PREPARATORY'];
        $hasNkp = in_array($gradeLevel, $nkpLevels) || NkpEvaluation::where('student_id', $student_id)->where('school_year_id', $school_year_id)->exists();
        
        $canManage = false;

        if ($hasNkp) {
            $existingEvaluations = NkpEvaluation::where('student_id', $student_id)
                ->where('school_year_id', $school_year_id)
                ->get()->keyBy('skill')->toArray();

            return view('nkp-report-card', [
                'studentName' => strtoupper($student->last_name . ', ' . $student->first_name),
                'sectionName' => 'ARCHIVED - SY ' . $schoolYear->school_year . ' | ' . $displaySection,
                'student_id' => $student_id,
                'savedEvaluations' => $existingEvaluations,
                'canManage' => $canManage,
                'activeYear' => $schoolYear
            ]);
        } else {
            // Dynamic Subjects for Archived Students based on PAST grade level
            preg_match('/\d+/', $gradeLevel, $matches);
            $gradeNum = isset($matches[0]) ? (int)$matches[0] : 0;

            if ($gradeNum == 1) {
                $subjects = ['Language', 'Reading and Literacy', 'Mathematics', 'Makabansa', 'GMRC'];
            } elseif ($gradeNum == 2 || $gradeNum == 3) {
                $subjects = ['English', 'Filipino', 'Mathematics', 'Makabansa', 'GMRC'];
            } elseif ($gradeNum >= 4 && $gradeNum <= 6) {
                $subjects = ['Filipino', 'English', 'Mathematics', 'Science', 'Araling Panlipunan', 'GMRC', 'TLE', 'MAPEH'];
            } else {
                $subjects = []; 
            }

            $existingGrades = Grade::where('student_id', $student_id)
                ->where('school_year_id', $school_year_id)
                ->get()->keyBy('subject_name')->toArray();
                
            $existingBehaviors = BehaviorReport::where('student_id', $student_id)
                ->where('school_year_id', $school_year_id)
                ->get()->keyBy('core_value')->toArray();

            return view('student-report-card', [
                'studentName' => strtoupper($student->last_name . ', ' . $student->first_name),
                'sectionName' => 'ARCHIVED - SY ' . $schoolYear->school_year . ' | ' . $displaySection,
                'student_id' => $student_id,
                'subjects' => $subjects,
                'coreValues' => ['Maka-Diyos', 'Makatao', 'Maka-kalikasan', 'Maka-bansa'],
                'savedGrades' => $existingGrades,
                'savedBehaviors' => $existingBehaviors,
                'canManage' => $canManage,
                'activeYear' => $schoolYear
            ]);
        }
    }

    public function fetchGrades($student_id)
    {
        $activeYear = SchoolYear::where('status', 'active')->first();
        if (!$activeYear) {
            return response()->json(['grades' => [], 'behaviors' => []]);
        }

        $grades = Grade::where('student_id', $student_id)
            ->where('school_year_id', $activeYear->id)
            ->get()->keyBy('subject_name')->toArray();

        $behaviors = BehaviorReport::where('student_id', $student_id)
            ->where('school_year_id', $activeYear->id)
            ->get()->keyBy('core_value')->toArray();

        return response()->json([
            'grades' => $grades,
            'behaviors' => $behaviors
        ]);
    }
}