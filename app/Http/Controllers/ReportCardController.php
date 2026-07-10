<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Section;
use App\Models\BehaviorReport;
use App\Models\NkpEvaluation;
use App\Models\User; // Added for Notifications
use App\Models\AuditLog;
use App\Notifications\GradeUploaded; // Added for Notifications
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification; // Added for Notifications

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
     */
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

        // --- NEW: GET THE ACTIVE SCHOOL YEAR ---
        $activeYear = SchoolYear::where('status', 'active')->first();
        $activeYearId = $activeYear ? $activeYear->id : null;

        // --- BRANCH 1: NKP STUDENTS ---
        if ($isNkp) {
            // NEW: Added the where('school_year_id') filter
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

        // NEW: Added the where('school_year_id') filters
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

        // --- NEW: GET THE ACTIVE SCHOOL YEAR ---
        $activeYear = SchoolYear::where('status', 'active')->first();
        
        // Safety check: if no active year exists, stop them from saving
        if (!$activeYear) {
            return response()->json(['message' => 'Error: No active school year found!'], 400);
        }
        
        $activeYearId = $activeYear->id;

        // 1. Save Numeric Grades (Grades 1-6)
        if ($grades) {
            foreach ($grades as $subject => $data) {
                // Notice we added school_year_id to the first array!
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
        'created_at' => now(), // Manually set the time
    ]);
}

if ($student) {
            // We format the names to look clean in the log table
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
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
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

        $headerSkipped = false;
        $processedStudents = 0;
        $updatedValues = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            if ($row === [null] || $row === false) {
                continue;
            }

            $isEmptyRow = true;
            foreach ($row as $value) {
                if ($value !== null && trim((string) $value) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }

            if ($isEmptyRow) {
                continue;
            }

            $studentName = isset($row[0]) ? trim((string) $row[0]) : '';
            if ($studentName === '') {
                continue;
            }

            $student = Student::where('section_id', $section_id)
                ->where(function ($query) use ($studentName) {
                    $normalized = strtolower(trim($studentName));
                    $query->whereRaw('LOWER(CONCAT(first_name, " ", last_name)) = ?', [$normalized])
                        ->orWhereRaw('LOWER(CONCAT(last_name, ", ", first_name)) = ?', [$normalized]);
                })
                ->first();

            if (!$student) {
                continue;
            }

            $processedStudents++;

            $subjects = [
                'Language' => isset($row[1]) ? trim((string) $row[1]) : '',
                'English' => isset($row[2]) ? trim((string) $row[2]) : '',
                'Mathematics' => isset($row[3]) ? trim((string) $row[3]) : '',
                'Makabansa' => isset($row[4]) ? trim((string) $row[4]) : '',
                'GMRC' => isset($row[5]) ? trim((string) $row[5]) : '',
                'MAPEH' => isset($row[6]) ? trim((string) $row[6]) : '',
                'Music' => isset($row[7]) ? trim((string) $row[7]) : '',
                'Art' => isset($row[8]) ? trim((string) $row[8]) : '',
                'PE' => isset($row[9]) ? trim((string) $row[9]) : '',
                'Health' => isset($row[10]) ? trim((string) $row[10]) : '',
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
        
        // Find all student IDs that have grades OR NKP evaluations in this specific year
        $gradeStudentIds = Grade::where('school_year_id', $school_year_id)->pluck('student_id')->toArray();
        $nkpStudentIds = NkpEvaluation::where('school_year_id', $school_year_id)->pluck('student_id')->toArray();
        
        // Combine them and remove duplicates
        $allStudentIds = array_unique(array_merge($gradeStudentIds, $nkpStudentIds));
        
        // Fetch those specific students
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
        
        // Check if they have NKP evaluations for this year
        $hasNkp = NkpEvaluation::where('student_id', $student_id)->where('school_year_id', $school_year_id)->exists();

        // Admin is viewing past records, so managing/editing is strictly disabled
        $canManage = false; 

        if ($hasNkp) {
            $existingEvaluations = NkpEvaluation::where('student_id', $student_id)
                ->where('school_year_id', $school_year_id) 
                ->get()->keyBy('skill')->toArray();
            
            return view('nkp-report-card', [
                'studentName' => strtoupper($student->last_name . ', ' . $student->first_name),
                'sectionName' => 'ARCHIVED - SY ' . $schoolYear->school_year, // Shows it is an old record
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
                'sectionName' => 'ARCHIVED - SY ' . $schoolYear->school_year, // Shows it is an old record
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