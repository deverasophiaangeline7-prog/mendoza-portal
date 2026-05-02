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

        // --- NEW AUDIT LOG FOR CREATING ---
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create Teacher',
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
        $user->status = 'archived'; // Update the status instead of deleting
        $user->save();
        
        // --- AUDIT LOG FOR ARCHIVING (You already had this one!) ---
        AuditLog::create([
            'user_id' => Auth::id(), 
            'action' => 'Archive Teacher',
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
        $user->status = 'active'; // Change them back to active!
        $user->save();
        
        // --- NEW AUDIT LOG FOR RESTORING ---
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Restore Teacher',
            'description' => Auth::user()->username . ' successfully restored Teacher account ID: ' . $id
        ]);

        return redirect()->back()->with('success', 'Teacher account restored successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        // --- NEW AUDIT LOG FOR PERMANENT DELETION ---
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete Teacher',
            'description' => Auth::user()->username . ' permanently deleted Teacher account ID: ' . $id
        ]);

        return redirect()->back()->with('success', 'Teacher deleted successfully!');
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'advisory' => 'required' 
    ]);

    $targetAdvisory = ($request->advisory === 'NKP') ? '1,2,3' : $request->advisory;

    // Use a Transaction to wrap the whole switch
    \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id, $targetAdvisory) {
        
        // 1. Find the "Other" teacher who currently has the room you want
        $otherTeacher = \App\Models\Teacher::where('advisory', $targetAdvisory)
            ->where('user_id', '!=', $id)
            ->first();

        // 2. If someone else is there, "Kick them out" (set to null) to free the seat
        if ($otherTeacher) {
            $otherTeacher->update(['advisory' => null]);
            
            // Also clear them from the sections table
            \App\Models\Section::where('teacher_id', $otherTeacher->user_id)
                ->update(['teacher_id' => null]);
        }

        // 3. Clear the Current Teacher's OLD assignments
        \App\Models\Section::where('teacher_id', $id)->update(['teacher_id' => null]);

        // 4. Assign the Current Teacher to the NEW section(s)
        $sectionIds = ($request->advisory === 'NKP') ? [1, 2, 3] : [$request->advisory];
        foreach ($sectionIds as $secId) {
            $section = \App\Models\Section::where('section_id', $secId)->first();
            if ($section) {
                $section->teacher_id = $id;
                $section->save();
            }
        }

        // 5. Finally, update the Teacher's record
        $teacher = \App\Models\Teacher::where('user_id', $id)->first();
        if ($teacher) {
            $teacher->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'advisory' => $targetAdvisory,
            ]);
        }
    });

    // Log the successful switch
    \App\Models\AuditLog::create([
        'user_id' => \Illuminate\Support\Facades\Auth::id(),
        'action' => 'Teacher Swapped',
        'description' => \Illuminate\Support\Facades\Auth::user()->username . " reassigned Teacher ID {$id} to Advisory {$targetAdvisory}."
    ]);

    return redirect()->back()->with('success', 'Teacher reassigned! (Note: The previous teacher is now unassigned).');
}
}