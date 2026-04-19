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
    // 1. Updated Validation (Removed 'array' so it doesn't crash)
    $request->validate([
        'username' => 'required|unique:users',
        'password' => 'required|confirmed',
        'last_name' => 'required',
        'first_name' => 'required',
        'advisory' => 'required', 
    ]);

    // 2. Create the record in the 'users' table
    $user = \App\Models\User::create([
        'username' => $request->username,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        'role' => 'teacher',
        'status' => 'active',
    ]);

    // 3. Create the record in the 'teachers' table (This is what Micaela is missing!)
    $teacher = \App\Models\Teacher::create([
        'user_id' => $user->user_id, // Link to the user we just made
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'advisory' => $request->advisory === 'NKP' ? '1,2,3' : $request->advisory,
        // Add your CV upload logic here if needed
    ]);

    // 4. Link the Teacher to the Sections table
    $sectionIds = ($request->advisory === 'NKP') ? [1, 2, 3] : [$request->advisory];

    foreach ($sectionIds as $id) {
        $section = \App\Models\Section::find($id);
        if ($section) {
            $section->teacher_id = $user->user_id;
            $section->save();
        }
    }

    return redirect()->route('account.management')->with('success', 'Teacher created successfully!');
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