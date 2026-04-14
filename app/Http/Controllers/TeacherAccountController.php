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
            'advisory'   => 'nullable|string|unique:users,advisory',
            'gender'     => 'nullable|string',   // Added validation
            'birthdate'  => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'cv'         => 'nullable|file|mimes:pdf|max:2048', // Validate the file type
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