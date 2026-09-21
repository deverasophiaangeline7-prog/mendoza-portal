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
}