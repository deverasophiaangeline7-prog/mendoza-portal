<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher; 
use App\Models\Section;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
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
        ->orderByRaw("CAST(grade_level AS UNSIGNED) ASC")
        ->orderBy('section_name', 'asc')
        ->get(); 
        
        return view('create-teacher-account', compact('sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'      => 'required|unique:users',
            'password'      => 'required|confirmed',
            'last_name'     => 'required',
            'first_name'    => 'required',
            'advisory'      => 'required',
            'gender'        => 'required',
            'birthdate'     => 'required|date',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $user = User::create([
            'username'           => $request->username,
            'password'           => Hash::make($request->password),
            'role'               => 'teacher',
            'status'             => 'active',
            'profile_photo_path' => $path,
        ]);

        $teacher = Teacher::create([
            'user_id'     => $user->user_id, 
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name'   => $request->last_name,
            'gender'      => $request->gender,
            'birthdate'   => $request->birthdate,
            'advisory'    => $request->advisory === 'NKP' ? '1,2,3' : $request->advisory,
        ]);

        // DYNAMIC NKP ASSIGNMENT (No more hardcoded IDs 1, 2, 3)
        if ($request->advisory === 'NKP') {
            $nkpSections = Section::whereIn(DB::raw('UPPER(grade_level)'), ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY', 'PREP', 'NKP'])->get();
            foreach ($nkpSections as $section) {
                $section->teacher_id = $user->user_id;
                $section->save();
            }
        } else {
            $section = Section::where('section_id', $request->advisory)->first();
            if ($section) {
                $section->teacher_id = $user->user_id;
                $section->save();
            }
        }

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Create Teacher',
            'description' => Auth::user()->username . ' created a new Teacher account for: ' . $request->first_name . ' ' . $request->last_name
        ]);

        return redirect()->route('account.management')->with('success', 'Teacher created successfully!');
    }

    public function index()
    {
        $teachers = User::where('role', 'teacher')
                    ->where('status', 'active')
                    ->with('teacher')
                    ->get();
                    
        return view('teacher-list', compact('teachers')); 
    }

    public function archive($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'archived'; 
        $user->save();
        
        // 1. Clear the teacher's advisory so it becomes available for others
        $teacher = Teacher::where('user_id', $id)->first();
        if ($teacher) {
            $teacher->update(['advisory' => null]);
        }

        // 2. Unlink sections from this teacher so they can be reassigned
        Section::where('teacher_id', $id)->update(['teacher_id' => null]);

        AuditLog::create([
            'user_id'     => Auth::id(), 
            'action'      => 'Archive Teacher',
            'description' => Auth::user()->username . ' successfully archived Teacher account ID: ' . $id
        ]);

        return redirect()->back()->with('success', 'Teacher account archived successfully!');
    }

    public function archivedIndex()
    {
        $archivedTeachers = User::where('role', 'teacher')
                        ->where('status', 'archived')
                        ->with('teacher')
                        ->get();
                        
        return view('teacher-archived-list', compact('archivedTeachers')); 
    }

    public function restore($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active'; 
        $user->save();
        
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Restore Teacher',
            'description' => Auth::user()->username . ' successfully restored Teacher account ID: ' . $id
        ]);

        return redirect()->back()->with('success', 'Teacher account restored successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Delete Teacher',
            'description' => Auth::user()->username . ' permanently deleted Teacher account ID: ' . $id
        ]);

        return redirect()->back()->with('success', 'Teacher deleted successfully!');
    }

    public function update(Request $request, $id)
    {
        // REMOVED: gender and birthdate constraints since the modal does not send them
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'advisory'   => 'required',
        ]);

        $targetAdvisory = ($request->advisory === 'NKP') ? '1,2,3' : $request->advisory;

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id, $targetAdvisory) {
            
            // Unlink the previous teacher who held this advisory
            $otherTeacher = \App\Models\Teacher::where('advisory', $targetAdvisory)
                ->where('user_id', '!=', $id)
                ->first();

            if ($otherTeacher) {
                $otherTeacher->update(['advisory' => null]);
                
                \App\Models\Section::where('teacher_id', $otherTeacher->user_id)
                    ->update(['teacher_id' => null]);
            }

            // Unlink current teacher from their old sections
            \App\Models\Section::where('teacher_id', $id)->update(['teacher_id' => null]);

            // DYNAMIC NKP ASSIGNMENT
            if ($request->advisory === 'NKP') {
                $nkpSections = \App\Models\Section::whereIn(\Illuminate\Support\Facades\DB::raw('UPPER(grade_level)'), ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY', 'PREP', 'NKP'])->get();
                foreach ($nkpSections as $section) {
                    $section->teacher_id = $id;
                    $section->save();
                }
            } else {
                $section = \App\Models\Section::where('section_id', $request->advisory)->first();
                if ($section) {
                    $section->teacher_id = $id;
                    $section->save();
                }
            }

            // Update Teacher Profile
            $teacher = \App\Models\Teacher::where('user_id', $id)->first();
            if ($teacher) {
                $teacher->update([
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'advisory'   => $targetAdvisory,
                ]);
            }
        });

        AuditLog::create([
            'user_id'     => \Illuminate\Support\Facades\Auth::id(),
            'action'      => 'Teacher Swapped',
            'description' => \Illuminate\Support\Facades\Auth::user()->username . " reassigned Teacher ID {$id} to Advisory {$targetAdvisory}."
        ]);

        return redirect()->back()->with('success', 'Teacher reassigned successfully!');
    }
}