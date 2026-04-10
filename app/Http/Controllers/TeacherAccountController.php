<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherAccountController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'username'   => 'required|unique:users,username',
            'password'   => 'required|min:8|confirmed',
            'advisory'   => 'nullable|string', // Added validation
            'gender'     => 'nullable|string',   // Added validation
            'cv'         => 'nullable|file|mimes:pdf,docx|max:2048', // Validate the file type
        ]);

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cvs', 'public');
        }

        User::create([
            'name'     => $request->first_name . ' ' . $request->last_name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'teacher',
            'advisory' => $request->advisory,
            'gender'   => $request->gender,
            'cv_path'  => $cvPath, // <--- ADDED THIS LINE TO SAVE THE CV
        ]);

        return redirect()->route('account.management')->with('success', 'Teacher account created successfully!');
    }

    public function index()
    {
        $teachers = User::where('role', 'teacher')->get();
        return view('teacher-list', compact('teachers')); 
    }

    public function destroy($id)
    {
        $teacher = User::findOrFail($id);
        $teacher->delete();
        return redirect()->back()->with('success', 'Teacher deleted successfully!');
    }
}