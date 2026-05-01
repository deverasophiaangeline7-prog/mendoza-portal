<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function promoteStudent($student_id)
    {
        $student = Student::findOrFail($student_id);

        // Map out the grade progression
        $gradeProgression = [
            'Nursery'      => 'Kindergarten',
            'Kindergarten' => 'Preparatory',
            'Preparatory'  => '1',
            '1' => '2', '2' => '3', '3' => '4', 
            '4' => '5', '5' => '6', '6' => 'Graduate'
        ];

        // Get the next grade. If they are in Grade 6, they graduate.
        $nextGrade = $gradeProgression[$student->grade_level] ?? null;

        if ($nextGrade) {
            $student->update([
                'promotion_status' => 'pending',
                'next_grade_level' => $nextGrade
            ]);
            
            // Format the text: Add "Grade " only if it's a number
            $displayGrade = is_numeric($nextGrade) ? 'Grade ' . $nextGrade : $nextGrade;
            
            return back()->with('success', $student->first_name . ' is queued for promotion to ' . $displayGrade);
        }

        return back()->with('error', 'Cannot promote this student further.');
    }
}