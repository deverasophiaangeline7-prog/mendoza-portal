<?php

namespace App\Http\Controllers\Admin;

use App\Models\Section;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentHistory;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;

class StudentController extends Controller
{
    public function index()
    {
        $gradeOrder = [
            'NURSERY'      => 1,
            'KINDERGARTEN' => 2,
            'KINDER'       => 2,  
            'PREPARATORY'  => 3,
            '1'            => 4,  
            'GRADE 1'      => 4,  
            '2'            => 5,
            'GRADE 2'      => 5,
            '3'            => 6,
            'GRADE 3'      => 6,
            '4'            => 7,
            'GRADE 4'      => 7,
            '5'            => 8,
            'GRADE 5'      => 8,
            '6'            => 9,
            'GRADE 6'      => 9,
        ];

        if (auth()->user()->role === 'admin') {
            $sectionsQuery = \App\Models\Section::orderBy('section_name', 'asc')->get();
        } else {
            $sectionsQuery = \App\Models\Section::where('teacher_id', auth()->id())
                                                ->orderBy('section_name', 'asc')
                                                ->get();
        }

        $sections = $sectionsQuery->sortBy(function ($section) use ($gradeOrder) {
            return $gradeOrder[strtoupper($section->grade_level)] ?? 99;
        });

        $totalStudents = User::where('role', 'parent')->count(); 

        return view('student-dashboard', compact('sections', 'totalStudents')); 
    }

    public function storeSection(Request $request)
    {
        $request->validate([
            'grade_level' => 'required|string|max:255',
            'section_name' => 'required|string|max:255',
        ]);

        \App\Models\Section::create([
            'grade_level' => strtoupper($request->grade_level),
            'section_name' => ucfirst($request->section_name), 
        ]);

        return redirect()->back();
    }

    public function destroySection($id)
    {
        Section::findOrFail($id)->delete();
        return redirect()->back(); 
    }

    public function showSection($id)
    {
        $section = Section::findOrFail($id);

        // Load grades so we can check for passing/failing status
        $students = Student::where('section_id', $section->id ?? $section->section_id)
                           ->with(['section', 'grades']) 
                           ->orderBy('last_name', 'asc')
                           ->get();

        $males = $students->where('gender', 'Male')->values();
        $females = $students->where('gender', 'Female')->values();

        return view('listofstudent', [
            'students' => $students,
            'grade'    => $section->grade_level, // 👈 FIXED: This solves the error in your screenshot
            'section'  => $section,
            'males'    => $males,
            'females'  => $females
        ]);
    }

    public function storeStudent(Request $request)
{
    $request->validate([
        'lrn'         => 'required|string|max:255',
        'first_name'  => 'required|string|max:255',
        'last_name'   => 'required|string|max:255',
        'gender'      => 'required|string|in:Male,Female',
        'birthdate'   => 'required|date',
        'section_id'  => 'required', 
        'grade_level' => 'required|string'
    ]);

    $birthDate = \Carbon\Carbon::parse($request->birthdate);
    $gradeLevel = strtoupper($request->grade_level);

    // FUTURE-PROOF FIX: Get the target year from your database, not the clock!
    $activeSy = \App\Models\SchoolYear::where('status', 'active')->first();
    
    if (!$activeSy) {
        return back()->with('error', 'No active school year found. Please set an active school year first.');
    }

    // EXACT COLUMN NAME APPLIED HERE:
    $syText = $activeSy->school_year;
    preg_match('/\d{4}/', $syText, $matches);
    $targetYear = $matches[0] ?? now()->year; 

    $birthDate = \Carbon\Carbon::parse($request->birthdate);
    
    // --- GLOBAL MAXIMUM AGE CHECK (Limit to 17) ---
    $ageAtStartOfSy = $targetYear - $birthDate->year;
    
    if ($ageAtStartOfSy >= 18) {
        return back()->withErrors(['birthdate' => 'THE BIRTHYEAR IS NOT QUALIFIED FOR THIS GRADE LEVEL.'])->withInput();
    }

    // --- NKP RULES (3 to 17 years old) ---
    $nkpLevels = ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY'];
    if (in_array($gradeLevel, $nkpLevels)) {
        $deadline = \Carbon\Carbon::create($targetYear, 10, 31);
        $ageAtDeadline = $birthDate->diffInYears($deadline);

        if ($ageAtDeadline < 3) {
            return back()->withErrors(['birthdate' => 'THE BIRTHYEAR IS NOT QUALIFIED. MUST BE 3 BY OCT 31.'])->withInput();
        }
    }

    // --- ELEMENTARY RULES (6 to 17 years old) ---
    $elementaryLevels = ['1', 'GRADE 1', '2', 'GRADE 2', '3', 'GRADE 3', '4', 'GRADE 4', '5', 'GRADE 5', '6', 'GRADE 6'];
    if (in_array($gradeLevel, $elementaryLevels)) {
        $deadline = \Carbon\Carbon::create($targetYear, 12, 31);
        $ageAtDeadline = $birthDate->diffInYears($deadline);

        if ($ageAtDeadline < 6) {
            return back()->withErrors(['birthdate' => 'THE BIRTHYEAR IS NOT QUALIFIED. MUST BE 6 BY DEC 31.'])->withInput();
        }
    }

    Student::create([
        'lrn'         => $request->lrn,
        'first_name'  => strtoupper($request->first_name),
        'middle_name' => strtoupper($request->middle_name),
        'last_name'   => strtoupper($request->last_name),
        'gender'      => ucfirst($request->gender),
        'birthdate'   => $request->birthdate,
        'section_id'  => $request->section_id,
        'grade_level' => $request->grade_level,
    ]);

    return redirect()->back()->with('success', 'Student registered successfully!');
}


    public function destroyStudent($id)
    {
        Student::findOrFail($id)->delete();
        return redirect()->back();
    }

    /**
     * Updated: Edit student with Middle Name and Section assignment
     */
    public function updateStudent(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'section_id' => 'required|integer',
        ]);

        $student = Student::findOrFail($id);
        
        $student->update([
            'first_name'  => strtoupper($request->first_name),
            'middle_name' => strtoupper($request->middle_name),
            'last_name'   => strtoupper($request->last_name),
            'section_id'  => $request->section_id,
        ]);

        if ($student->user) {
            $student->user->update([
                'name' => strtoupper($request->first_name . ' ' . $request->last_name)
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Edit Student',
            'description' => auth()->user()->username . ' updated profile for: ' . $student->first_name . ' ' . $student->last_name
        ]);

        return redirect()->back()->with('success', 'Student profile updated successfully!');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string',
        ]);

        return redirect()->back()->with('success', 'Message sent to parent successfully!');
    }

    /**
     * Updated: Automates Section Assignment and Archives Section History
     */
    public function finalizeSchoolYear()
    {
        $currentYear = SchoolYear::where('status', 'active')->first();
        if (!$currentYear) return back()->with('error', 'No active school year found.');

        // ---------------------------------------------------------
        // 🛑 GUARD 1: Prevent Premature Finalization
        // ---------------------------------------------------------
        // Count if anyone has a grade OR an NKP evaluation
        $studentsWithAnyGrades = Student::whereNotNull('section_id')->has('grades')->count();
        $studentsWithAnyEvals = DB::table('nkp_evaluations')->count();

        // If literally zero records exist in BOTH tables, the year just started!
        if ($studentsWithAnyGrades === 0 && $studentsWithAnyEvals === 0) {
            return back()->with('error', 'FINALIZATION BLOCKED: This school year has no grades or evaluations recorded yet! You cannot close a school year that just started.');
        }

        // ---------------------------------------------------------
        // 🛑 GUARD 2: Vanilla PHP Loop Approach (Elementary & NKP Check)
        // ---------------------------------------------------------
        $requiredSubjects = 9; 
        $elementaryLevels = ['1', 'GRADE 1', '2', 'GRADE 2', '3', 'GRADE 3', '4', 'GRADE 4', '5', 'GRADE 5', '6', 'GRADE 6'];
        $nkpLevels = ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY'];

        // Fetch active students and count their standard grades
        $activeStudents = Student::whereNotNull('section_id')
            ->where('section_id', '!=', '')
            ->withCount('grades')
            ->get();

        $incompleteElementaryCount = 0;
        $incompleteNkpCount = 0;

        foreach ($activeStudents as $student) {
            $grade = strtoupper(trim($student->grade_level));
            
            // A. Check Elementary (Must have exactly 9 subjects)
            if (in_array($grade, $elementaryLevels)) {
                if ($student->grades_count < $requiredSubjects) {
                    $incompleteElementaryCount++;
                }
            }

            // B. Check NKP (Must have at least 1 evaluation record)
            if (in_array($grade, $nkpLevels)) {
                // Check directly in the database if this student has an evaluation
                $hasEvaluation = DB::table('nkp_evaluations')
                    ->where('student_id', $student->student_id ?? $student->id)
                    ->exists();

                if (!$hasEvaluation) {
                    $incompleteNkpCount++;
                }
            }
        }

        // 3. Combine errors so the Admin sees EVERYTHING at once
        if ($incompleteElementaryCount > 0 || $incompleteNkpCount > 0) {
            $errorMessage = "FINALIZATION BLOCKED: ";
            
            if ($incompleteElementaryCount > 0) {
                $errorMessage .= "{$incompleteElementaryCount} Elementary student(s) missing grades. ";
            }
            if ($incompleteNkpCount > 0) {
                $errorMessage .= "{$incompleteNkpCount} NKP student(s) missing evaluations. ";
            }
            
            $errorMessage .= "Please complete these records before closing the year.";

            return back()->with('error', trim($errorMessage));
        }
        // ---------------------------------------------------------

        // 1. Get all students waiting for promotion (pending or promoted)
        $pendingStudents = Student::with('section')->whereIn('promotion_status', ['pending', 'promoted'])->get();

        if ($pendingStudents->isEmpty()) {
            return back()->with('info', 'There are no pending promotions to finalize.');
        }

        DB::beginTransaction();
        try {
            foreach ($pendingStudents as $student) {
                // A. Archive their current section history before wiping it
                if ($student->section) {
                    StudentHistory::create([
                        'student_id' => $student->student_id,
                        'school_year_id' => $currentYear->id,
                        'section_name' => strtoupper($student->section->grade_level . ' - ' . $student->section->section_name)
                    ]);
                }

                // B. Handle Graduation for Grade 6 (Hardcoded check)
                $graduatingGrade = strtoupper(trim($student->grade_level));
                if ($graduatingGrade == '6' || $graduatingGrade == 'GRADE 6') {
                    if ($student->user) {
                        $student->user->update(['status' => 'archived']);
                    }
                    $student->update([
                        'section_id' => null,
                        'promotion_status' => 'none',
                        'next_grade_level' => null
                    ]);
                } else {
                    // C. Promote others and Auto-Assign a section in the new grade
                    $targetGrade = $student->next_grade_level;
                    $newSection = Section::where('grade_level', $targetGrade)->first();

                    $student->update([
                        'grade_level'      => $targetGrade,
                        'section_id'       => $newSection ? $newSection->section_id : null,
                        'promotion_status' => 'none',
                        'next_grade_level' => null
                    ]);
                }
            }

            // ---------------------------------------------------------
            // 🛑 NEW: ROLL OVER THE SCHOOL YEAR IN THE DATABASE
            // ---------------------------------------------------------
            $currentSyText = $currentYear->school_year; 
            $parts = explode('-', $currentSyText);
            
            if (count($parts) === 2) {
                $nextStartYear = (int)$parts[0] + 1; 
                $nextEndYear = (int)$parts[1] + 1;   
                $nextSyText = $nextStartYear . '-' . $nextEndYear; 

                // 1. Archive the old year 
                $currentYear->update(['status' => 'archived']);

                // 2. Create and activate the brand new year 
                SchoolYear::create([
                    'school_year' => $nextSyText,
                    'status'      => 'active'
                ]);
            }
            // ---------------------------------------------------------

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Year Finalized',
                'description' => auth()->user()->username . ' finalized school year and auto-assigned students to new grades.'
            ]);

            DB::commit();
            return back()->with('success', 'School year finalized! Students promoted, and system advanced to SY ' . ($nextSyText ?? 'Next Year') . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}