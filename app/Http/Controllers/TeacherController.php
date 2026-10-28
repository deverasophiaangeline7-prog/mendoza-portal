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

        public function promoteAll(Request $request, $section_id)
        {
            $studentIds = $request->input('student_ids');

            if (empty($studentIds)) {
                return back()->with('error', 'No students were selected for promotion.');
            }

            $promotedCount = 0;
            $activeYear = \App\Models\SchoolYear::where('status', 'active')->first();
            
            $gradeProgression = [
                'NURSERY'      => 'Kindergarten',
                'KINDER'       => 'Preparatory',
                'KINDERGARTEN' => 'Preparatory',
                'PREP'         => '1',
                'PREPARATORY'  => '1',
                '1' => '2', '2' => '3', '3' => '4', 
                '4' => '5', '5' => '6', '6' => 'Graduate'
            ];

            foreach ($studentIds as $id) {
                $student = Student::with(['grades' => function($query) use ($activeYear) {
                    $query->where('school_year_id', $activeYear ? $activeYear->id : null);
                }])->find($id);

                if ($student && $student->promotion_status !== 'pending') {
                    
                    // BACKEND SECURITY CHECK: Verify they actually passed before applying
                    $gradeLvl = strtoupper(trim($student->grade_level));
                    $nkpLevels = ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY'];
                    $isValid = false;

                    if (in_array($gradeLvl, $nkpLevels)) {
                        $isValid = (bool)($student->has_nkp_eval ?? false);
                    } else {
                        $failingGrade = $student->grades->first(function($g) {
                            return trim((string)$g->term1) === '' || 
                                trim((string)$g->term2) === '' || 
                                trim((string)$g->term3) === '' || 
                                trim((string)$g->final_grade) === '' || 
                                (float)$g->final_grade < 75;
                        });
                        $isValid = !$failingGrade && $student->grades->isNotEmpty();
                    }

                    // Only promote if the backend confirms they passed
                    if ($isValid) {
                        $nextGrade = $gradeProgression[$gradeLvl] ?? null;

                        if ($nextGrade) {
                            $student->update([
                                'promotion_status' => 'pending',
                                'next_grade_level' => $nextGrade
                            ]);
                            $promotedCount++;
                        }
                    }
                }
            }

            if ($promotedCount > 0) {
                return back()->with('success', "Successfully queued {$promotedCount} student(s) for promotion!");
            }

            return back()->with('error', "Failed to promote. The selected students may have failing grades or incomplete records.");
        }
    }