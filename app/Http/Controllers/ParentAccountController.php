<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ParentAccountController extends Controller
{
    /**
     * Show the form to create a new parent account.
     */
    public function create()
    {
        $sections = Section::orderByRaw("
        CASE 
            WHEN grade_level = 'Nursery' THEN 1
            WHEN grade_level = 'Kindergarten' THEN 2
            WHEN grade_level = 'Preparatory' THEN 3
            ELSE 4 
        END ASC
    ")
    ->orderBy('grade_level', 'asc') // This sorts the numeric grades (1, 2, 3...) after the first three
    ->get();

    return view('create-parent-account', compact('sections'));
    }

    /**
     * Store a newly created parent account and student record.
     */
    public function store(Request $request)
    {
        // 1. Validation (Matches your Blade input 'name' attributes)
        $request->validate([
            'username'    => 'required|unique:users,username',
            'password'    => 'required|min:6|confirmed',
            'lrn'         => 'required|numeric|digits:12|unique:students,lrn',
            'first_name'  => 'required',
            'last_name'   => 'required',
            'section_id'  => 'required',
            'gender'      => 'required',
            'birthdate'   => 'required|date',
        ]);

        // 2. CREATE THE USER FIRST (The Parent)
        // Note: Using 'id' is standard, but if your User model uses 'user_id', change it here
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'parent',
        ]);

        // 3. CREATE THE STUDENT SECOND
        // We link the student to the parent using the ID we just created
        Student::create([
            'user_id'     => $user->user_id, // Use $user->user_id if that is your User PK
            'lrn'         => $request->lrn,
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name'   => $request->last_name,
            'ext_name'    => $request->ext_name,
            'gender'      => $request->gender,
            'birth_date'  => $request->birthdate, // Matches 'birthdate' from form to 'birth_date' in DB
            'grade_level' => $request->grade_level,
            'section_id'  => $request->section_id,
        ]);

        return redirect()->route('account.management')->with('success', 'Parent account and Student record created successfully!');
    }

    /**
     * Optional: List all parents if needed for the management page.
     */
    public function index()
    {
        $students = Student::with('user', 'section')->get();
        return view('account-management', compact('students'));
    }
}