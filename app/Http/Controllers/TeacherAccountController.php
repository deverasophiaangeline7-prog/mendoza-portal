<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher; 
use App\Models\Section;
use App\Models\AuditLog;
use App\Models\SubjectAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TeacherAccountController extends Controller
{
    public function create()
    {
        $sections = Section::orderByRaw("
            CASE 
                WHEN grade_level IN ('Nursery', 'NURSERY') THEN 1 
                WHEN grade_level IN ('Kindergarten', 'Kinder', 'KINDER') THEN 2 
                WHEN grade_level IN ('Preparatory', 'Prep', 'PREPARATORY') THEN 3 
                ELSE 4 
            END ASC
        ")
        ->orderByRaw("CAST(REGEXP_REPLACE(grade_level, '[^0-9]', '') AS UNSIGNED) ASC")
        ->orderBy('section_name', 'asc')
        ->get(); 
        
        return view('create-teacher-account', compact('sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'         => 'required|unique:users,username',
            'password'         => 'required|confirmed',
            'last_name'        => 'required',
            'first_name'       => 'required',
            'advisory'         => 'required',
            'gender'           => 'required',
            'birthdate'        => 'required|date',
            'profile_photo'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'assignments'              => 'nullable|array',
            'assignments.*.section_id' => 'required_with:assignments',
            'assignments.*.subject'    => 'nullable|string',
        ]);

        $path = null;
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $user = User::create([
            'username'           => $request->username,
            'email'              => $request->username,
            'password'           => Hash::make($request->password),
            'role'               => 'teacher',
            'status'             => 'active',
            'section_id'         => $request->advisory === 'NKP' ? null : $request->advisory,
            'profile_photo_path' => $path,
        ]);

        $assignedSubjectString = 'Class Adviser';
        if (!empty($request->assignments)) {
            $subjects = array_unique(array_column($request->assignments, 'subject'));
            $assignedSubjectString = implode(', ', $subjects);
        }

        // 1. DYNAMICALLY FETCH REAL NKP IDs 
        $advisoryValue = $request->advisory;
        $nkpSections = collect();

        if ($request->advisory === 'NKP') {
            $nkpSections = Section::whereIn(DB::raw('UPPER(grade_level)'), ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY', 'PREP', 'NKP'])->get();
            // Grabs the real database IDs (e.g., "4,5,6") and combines them
            $advisoryValue = $nkpSections->pluck('section_id')->implode(','); 
        }

        $teacher = Teacher::create([
            'user_id'          => $user->user_id, 
            'first_name'       => $request->first_name,
            'middle_name'      => $request->middle_name,
            'last_name'        => $request->last_name,
            'gender'           => $request->gender,
            'birthdate'        => $request->birthdate,
            'advisory'         => $advisoryValue, // <-- Now uses dynamic IDs!
            'assigned_subject' => $assignedSubjectString,
        ]);

        if (!empty($request->assignments)) {
            foreach ($request->assignments as $assignment) {
                SubjectAssignment::create([
                    'teacher_id'   => $user->user_id,
                    'section_id'   => $assignment['section_id'],
                    'subject_name' => $assignment['subject'],
                ]);
            }
        }

        if ($request->advisory === 'NKP') {
            foreach ($nkpSections as $section) {
                $section->teacher_id = $user->user_id;
                $section->save();
            }
        } else {
            if (is_numeric($request->advisory)) {
                $section = Section::where('section_id', $request->advisory)->first();
                if ($section) {
                    $section->teacher_id = $user->user_id;
                    $section->save();
                }
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
        
        $teacher = Teacher::where('user_id', $id)->first();
        if ($teacher) {
            $teacher->update(['advisory' => null]);
        }

        Section::where('teacher_id', $id)->update(['teacher_id' => null]);
        SubjectAssignment::where('teacher_id', $id)->delete();

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
        SubjectAssignment::where('teacher_id', $id)->delete();
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
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'advisory'   => 'required',
            'assignments'              => 'nullable|array',
            'assignments.*.section_id' => 'required_with:assignments',
            'assignments.*.subject'    => 'nullable|string',
        ]);

        // DYNAMIC UPDATE FOR NKP IDs
        $targetAdvisory = $request->advisory;
        $nkpSections = collect();
        
        if ($request->advisory === 'NKP') {
            $nkpSections = Section::whereIn(DB::raw('UPPER(grade_level)'), ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY', 'PREP', 'NKP'])->get();
            $targetAdvisory = $nkpSections->pluck('section_id')->implode(','); 
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id, $targetAdvisory, $nkpSections) {
            
            if (is_numeric($targetAdvisory)) {
                $otherTeacher = \App\Models\Teacher::where('advisory', $targetAdvisory)
                    ->where('user_id', '!=', $id)
                    ->first();

                if ($otherTeacher) {
                    $otherTeacher->update(['advisory' => null]);
                    \App\Models\Section::where('teacher_id', $otherTeacher->user_id)->update(['teacher_id' => null]);
                }
            }

            \App\Models\Section::where('teacher_id', $id)->update(['teacher_id' => null]);
            SubjectAssignment::where('teacher_id', $id)->delete();
            
            $assignedSubjectString = 'Class Adviser';
            if (!empty($request->assignments)) {
                $subjects = array_unique(array_column($request->assignments, 'subject'));
                $assignedSubjectString = implode(', ', $subjects);

                foreach ($request->assignments as $assignment) {
                    SubjectAssignment::create([
                        'teacher_id'   => $id,
                        'section_id'   => $assignment['section_id'],
                        'subject_name' => $assignment['subject'],
                    ]);
                }
            }

            if ($request->advisory === 'NKP') {
                foreach ($nkpSections as $section) {
                    $section->teacher_id = $id;
                    $section->save();
                }
            } else {
                if (is_numeric($targetAdvisory)) {
                    $section = \App\Models\Section::where('section_id', $targetAdvisory)->first();
                    if ($section) {
                        $section->teacher_id = $id;
                        $section->save();
                    }
                }
            }
            
            $teacher = \App\Models\Teacher::where('user_id', $id)->first();
            if ($teacher) {
                $teacher->update([
                    'first_name'       => $request->first_name,
                    'last_name'        => $request->last_name,
                    'advisory'         => $targetAdvisory,
                    'assigned_subject' => $assignedSubjectString,
                ]);
            }
        });

        AuditLog::create([
            'user_id'     => \Illuminate\Support\Facades\Auth::id(),
            'action'      => 'Teacher Updated',
            'description' => \Illuminate\Support\Facades\Auth::user()->username . " updated Teacher ID {$id} profile."
        ]);

        return redirect()->back()->with('success', 'Teacher reassigned successfully!');
    }
}