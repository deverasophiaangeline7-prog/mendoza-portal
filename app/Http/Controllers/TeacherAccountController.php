<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherAccountController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'username'   => 'required|unique:users,username',
            'password'   => 'required|min:8|confirmed',
            // Update: We now check if the advisory (section_id) exists in the sections table
            'advisory'   => 'nullable|exists:sections,section_id|unique:teachers,advisory', 
            'gender'     => 'nullable|string',
            'birthdate'  => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'cv'         => 'nullable|file|mimes:pdf|max:2048',
        ]);

        // 1. Handle the File Upload
        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cvs', 'public');
        }

        // 2. Create the User (Login Credentials)
        $user = User::create([
            'username' => $request->username,
            'email'    => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'teacher',
        ]);

        // 3. Create the Teacher Profile
        Teacher::create([
            'user_id'        => $user->user_id, 
            'first_name'     => $request->first_name,
            'last_name'      => $request->last_name,
            'advisory'       => $request->advisory, // This now stores the section_id (1-9)
            'cv_path'        => $cvPath,
        ]);

        return redirect()->route('account.management')->with('success', 'Teacher account created successfully!');
    }

    public function index()
    {
        $teachers = User::where('role', 'teacher')->with('teacher')->get();
        return view('teacher-list', compact('teachers')); 
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return redirect()->back()->with('success', 'Teacher deleted successfully!');
    }
}