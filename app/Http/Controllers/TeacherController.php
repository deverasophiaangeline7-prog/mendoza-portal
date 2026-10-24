<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher; // Make sure to import the Teacher model
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function view()
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403, 'Unauthorized action.');
        }

        $teacher = Teacher::where('user_id', auth()->id())
                        ->with(['user', 'section'])
                        ->firstOrFail();

        return view('teacher-view', compact('teacher'));
    }

    public function promoteStudent($student_id)
    {
        $student = Student::findOrFail($student_id);

        // Standardize the database value to ALL CAPS to prevent spelling/case errors
        $currentGrade = strtoupper(trim($student->grade_level));

        // Map out the grade progression using bulletproof ALL CAPS keys
        $gradeProgression = [
            'NURSERY'      => 'Kindergarten',
            'KINDER'       => 'Preparatory',
            'KINDERGARTEN' => 'Preparatory',
            'PREP'         => '1',
            'PREPARATORY'  => '1',
            '1' => '2', '2' => '3', '3' => '4', 
            '4' => '5', '5' => '6', '6' => 'Graduate'
        ];

        // Get the next grade. If they are in Grade 6, they graduate.
        $nextGrade = $gradeProgression[$currentGrade] ?? null;

        if ($nextGrade) {
            $student->update([
                'promotion_status' => 'pending',
                'next_grade_level' => $nextGrade
            ]);
            
            // Format the text: Add "Grade " only if it's a number
            $displayGrade = is_numeric($nextGrade) ? 'Grade ' . $nextGrade : $nextGrade;
            
            return back()->with('success', $student->first_name . ' is queued for promotion to ' . $displayGrade);
        }

        return back()->with('error', 'Cannot promote this student further. Check if their grade level is formatted correctly.');
    }

   public function promoteAll($section_id)
    {
        // 1. Get all students in this specific section, along with their grades
        $students = Student::with('grades')->where('section_id', $section_id)->get();
        $activeYear = \App\Models\SchoolYear::where('status', 'active')->first();
        
        $promotedCount = 0;
        $skippedCount = 0; // Track students skipped due to failing/incomplete grades
        
        // 2. Grade progression map
        $gradeProgression = [
            'NURSERY'      => 'Kindergarten',
            'KINDER'       => 'Preparatory',
            'KINDERGARTEN' => 'Preparatory',
            'PREP'         => '1',
            'PREPARATORY'  => '1',
            '1' => '2', '2' => '3', '3' => '4', 
            '4' => '5', '5' => '6', '6' => 'Graduate'
        ];

        // 3. Loop through every student and check if they are eligible
        foreach ($students as $student) {
            // Skip students who are already queued for promotion (Don't count them as failed)
            if ($student->promotion_status === 'pending') {
                continue;
            }

            $gradeLvl = strtoupper(trim($student->grade_level));
            $nkpLevels = ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY'];
            
            $isPassed = false;

            // --- REPLICATING YOUR BLADE LOGIC FOR PASS/FAIL ---
            if (in_array($gradeLvl, $nkpLevels)) {
                $isPassed = (bool)($student->has_nkp_eval ?? false);
            } else {
                preg_match('/\d+/', $gradeLvl, $matches);
                $gradeNum = isset($matches[0]) ? (int)$matches[0] : 0;
                
                if ($gradeNum >= 1 && $gradeNum <= 3) {
                    $requiredSubjects = 5;
                } elseif ($gradeNum >= 4 && $gradeNum <= 6) {
                    $requiredSubjects = 8;
                } else {
                    $requiredSubjects = 9; 
                }

                $passingMark = 75;
                
                $currentGrades = $student->grades->where('school_year_id', $activeYear ? $activeYear->id : null);
                $gradesCount = $currentGrades->count();
                
                $failingGrade = $currentGrades->first(function($g) use ($passingMark) {
                    return is_null($g->final_grade) || $g->final_grade === '' || (float)$g->final_grade < $passingMark;
                });
                
                $isComplete = $gradesCount >= $requiredSubjects;
                $isPassed = $isComplete && !$failingGrade;
            }
            // ---------------------------------------------------

            // 4. If they legitimately passed, safely promote them!
            if ($isPassed) {
                $nextGrade = $gradeProgression[$gradeLvl] ?? null;
                
                if ($nextGrade) {
                    $student->update([
                        'promotion_status' => 'pending',
                        'next_grade_level' => $nextGrade
                    ]);
                    $promotedCount++;
                }
            } else {
                // If they did not pass, add them to the skipped counter
                $skippedCount++;
            }
        }

        // 5. Return dynamic success or error messages
        if ($promotedCount > 0) {
            $message = "Successfully queued {$promotedCount} eligible student(s) for promotion!";
            
            // Append a warning if any students were left behind
            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} student/s were skipped due to failing or incomplete grades).";
            }
            
            return back()->with('success', $message);
        } else {
            return back()->with('error', "No eligible students found to promote. {$skippedCount} student(s) were skipped due to failing or incomplete records.");
        }
    }
}