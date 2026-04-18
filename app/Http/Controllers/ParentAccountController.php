<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class ParentAccountController extends Controller
{
    /**
     * Store a newly created parent account in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lrn'        => 'required|string|unique:students,lrn', // Check uniqueness in students table
            'last_name'  => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'username'   => 'required|string|unique:users,username',
            'password'   => 'required|string|min:8|confirmed',
            'gender'     => 'required|in:Male,Female',
            'birthdate'  => 'required|date',
            'section_id' => 'required|exists:sections,section_id', // Ensure the section exists
            'advisory'   => 'required|string', // This represents the 'grade_level' in your logic
        ]);

        // 1. Create the User (Login Credentials)
        $user = User::create([
            'username' => $request->username,
            'email'    => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'parent',
        ]);

        // 2. Create the Student Profile linked to this User and the chosen Section
        Student::create([
            'user_id'     => $user->user_id,
            'section_id'  => $request->section_id, // Link to the section_id from your form
            'lrn'         => $request->lrn,
            'first_name'  => $request->first_name,
            'last_name'   => $request->last_name,
            'gender'      => $request->gender,
            'birth_date'  => $request->birthdate,
            'grade_level' => $request->advisory, // Store 'Nursery', '1', etc.
        ]);

        return redirect()->route('account.management')
            ->with('success', 'Parent account for ' . $request->first_name . ' has been created!');
    }

    /**
     * List all parents for the general management view.
     */
    public function index()
    {
        // Eager load 'student.section' to show names and sections in the list
        $parents = User::where('role', 'parent')->with('student.section')->get();
        return view('parent-list', compact('parents')); 
    }

    /**
     * Display students based on the grade slug clicked (e.g., 'grade-1').
     */
    public function showGrade($grade)
    {
        // This maps the button IDs to the actual database values
        $lookup = [
            'nursery'     => 'Nursery',
            'kinder'      => 'Kindergarten',
            'preparatory' => 'Preparatory',
            'grade-1'     => '1',
            'grade-2'     => '2',
            'grade-3'     => '3',
            'grade-4'     => '4',
            'grade-5'     => '5',
            'grade-6'     => '6',
        ];

        // Get the database value. If not found, use the input itself.
        $dbValue = $lookup[$grade] ?? $grade;

        // Get students where 'grade_level' matches our translated value
        $students = Student::where('grade_level', $dbValue)
                           ->with('section') // Include section details (St. Mary, Faith, etc.)
                           ->orderBy('last_name', 'asc')
                           ->get();

        // Split by gender for the view
        $males = $students->where('gender', 'Male');
        $females = $students->where('gender', 'Female');

        // Get section info from the first student if available
        $section = $students->first()->section ?? null;

        return view('sections', [
            'students' => $students,
            'grade'    => strtoupper(str_replace('-', ' ', $grade)), // Formats 'grade-1' to 'GRADE 1'
            'section'  => $section,
            'males'    => $males,
            'females'  => $females
        ]);
    }
}