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

        $students = Student::where('section_id', $section->id ?? $section->section_id)
                           ->with('section')
                           ->orderBy('last_name', 'asc')
                           ->get();

        $males = $students->where('gender', 'Male')->values();
        $females = $students->where('gender', 'Female')->values();

        return view('listofstudent', [
            'students' => $students,
            'grade'    => $section->grade_level,
            'section'  => $section,
            'males'    => $males,
            'females'  => $females
        ]);
    }

    public function showStudent($id)
    {
        $student = Student::with('section')->findOrFail($id);
        return view('student-view', compact('student'));
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'lrn'         => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'gender'      => 'required|string|in:Male,Female',
            'birth_date'  => 'required|date',
            'section_id'  => 'required', 
            'grade_level' => 'required|string'
        ]);

        Student::create([
            'lrn'         => $request->lrn,
            'first_name'  => strtoupper($request->first_name),
            'middle_name' => strtoupper($request->middle_name),
            'last_name'   => strtoupper($request->last_name),
            'gender'      => ucfirst($request->gender),
            'birth_date'  => $request->birth_date,
            'section_id'  => $request->section_id,
            'grade_level' => $request->grade_level,
        ]);

        return redirect()->back();
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

                // B. Handle Graduation for Grade 6
                if ($student->grade_level == '6') {
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

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Year Finalized',
                'description' => auth()->user()->username . ' finalized school year and auto-assigned students to new grades.'
            ]);

            DB::commit();
            return back()->with('success', 'School year finalized! Students promoted and auto-assigned to sections.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}