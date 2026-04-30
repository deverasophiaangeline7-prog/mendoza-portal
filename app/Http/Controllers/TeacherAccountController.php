<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher; 
use App\Models\Section;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherAccountController extends Controller
{
    public function create()
    {
        $sections = Section::whereNotIn('grade_level', [
            'Nursery', 'Kindergarten', 'Preparatory', 
            'NURSERY', 'KINDER', 'PREPARATORY'
        ])
        ->orderBy('grade_level', 'asc')
        ->get(); 
        return view('create-teacher-account', compact('sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|confirmed',
            'last_name' => 'required',
            'first_name' => 'required',
            'advisory' => 'required', 
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->user_id, 
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'advisory' => $request->advisory === 'NKP' ? '1,2,3' : $request->advisory,
        ]);

        $sectionIds = ($request->advisory === 'NKP') ? [1, 2, 3] : [$request->advisory];

        foreach ($sectionIds as $id) {
            $section = Section::where('section_id', $id)->first();
            
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