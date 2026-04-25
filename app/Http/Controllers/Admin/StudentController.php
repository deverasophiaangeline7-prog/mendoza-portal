<?php

namespace App\Http\Controllers\Admin;

use App\Models\Section;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;

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

        // ROLE-BASED CHECK: Admin sees all, Teachers see only their section
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

    // ==========================================
    // NEW METHODS FOR ADDING & DELETING STUDENTS
    // ==========================================

    public function storeStudent(Request $request)
    {
        // 1. Validate the incoming data from your modal
        $request->validate([
            'lrn'         => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'gender'      => 'required|string|in:Male,Female',
            'birth_date'  => 'required|date',
            'section_id'  => 'required', 
            'grade_level' => 'required|string'
        ]);

        // 2. Save the new student to the database
        Student::create([
            'lrn'         => $request->lrn,
            'first_name'  => strtoupper($request->first_name), // Forces names to be ALL CAPS
            'last_name'   => strtoupper($request->last_name),
            'gender'      => ucfirst($request->gender),
            'birth_date'  => $request->birth_date,
            'section_id'  => $request->section_id,
            'grade_level' => $request->grade_level,
        ]);

        // 3. Reload the page so the student appears in the table
        return redirect()->back();
    }

    public function destroyStudent($id)
    {
        // Find the specific student by their primary key and delete them
        Student::findOrFail($id)->delete();
        
        return redirect()->back();
    }
    // ADD THIS TO THE BOTTOM OF YOUR STUDENT CONTROLLER
    public function sendMessage(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string',
        ]);

        // Right now, this simulates a successful send for your system build.
        // Once you have a 'messages' database table set up, you will replace 
        // this comment with the actual database save command!

        // Example for later: 
        // \App\Models\Message::create([
        //     'sender_id' => auth()->id(),
        //     'student_id' => $request->student_id,
        //     'subject' => $request->subject,
        //     'content' => $request->message,
        // ]);

        // Redirect back with a success alert
        return redirect()->back()->with('success', 'Message sent to parent successfully!');
    }
}