<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\AuditLog;
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
    ->orderBy('grade_level', 'asc')
    ->get();

    return view('create-parent-account', compact('sections'));
    }

    /**
     * Store a newly created parent account and student record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username'    => 'required|unique:users,username',
            'password'    => 'required|min:6|confirmed',
            'lrn'         => 'required|numeric|digits:12|unique:students,lrn',
            'first_name'  => 'required',
            'last_name'   => 'required',
            'section_id'  => 'required',
            'gender'      => 'required',
            'birthdate'   => 'required|date',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

            $path = null;
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
            }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'parent',
            'profile_photo_path' => $path,
        ]);

        Student::create([
            'user_id'     => $user->user_id,
            'lrn'         => $request->lrn,
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name'   => $request->last_name,
            'ext_name'    => $request->ext_name,
            'gender'      => $request->gender,
            'birth_date'  => $request->birthdate,
            'grade_level' => $request->grade_level,
            'section_id'  => $request->section_id,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create Parent',
            'description' => Auth::user()->username . ' created a new Parent/Student account for LRN: ' . $request->lrn
        ]);

        return redirect()->route('account.management')->with('success', 'Parent account and Student record created successfully!');
    }

    /**
     * List all sections and students for the parent accounts management page.
     */
    public function index()
    {
        $sections = Section::orderByRaw("
            CASE 
                WHEN grade_level = 'Nursery' THEN 1
                WHEN grade_level = 'Kindergarten' THEN 2
                WHEN grade_level = 'Preparatory' THEN 3
                ELSE 4 
            END ASC
        ")
        ->orderBy('grade_level', 'asc')
        ->get();

        $students = Student::with('user', 'section')->get();
        
        return view('parent-list', compact('sections', 'students'));
    }

    /**
     * Store a newly created section from the modal form with case normalization.
     */
    public function storeSection(Request $request)
    {
        $request->validate([
            'grade_level'  => 'required|string|max:255',
            'section_name' => 'required|string|max:255',
        ]);

        // This permanently forces "ST. MARY" or "st. mary" to "St. Mary"
        $sectionName = ucwords(strtolower(trim($request->section_name)));

        Section::create([
            'grade_level'  => $request->grade_level,
            'section_name' => $sectionName,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create Section',
            'description' => Auth::user()->username . ' created a new section: ' . $request->grade_level . ' - ' . $sectionName
        ]);

        return redirect()->back()->with('success', 'Section created successfully!');
    }

    /**
     * Delete an existing section using section_id.
     */
    public function destroySection(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,section_id',
        ]);

        $section = Section::where('section_id', $request->section_id)->firstOrFail();
        $section->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete Section',
            'description' => Auth::user()->username . ' deleted section ID: ' . $request->section_id
        ]);

        return redirect()->back()->with('success', 'Section deleted successfully!');
    }

    public function showGrade($grade)
    {
        // If clicking a dynamic section ID from the database
        if (is_numeric($grade)) {
            $section = Section::where('section_id', $grade)->firstOrFail();
            $students = Student::where('section_id', $section->section_id)
                               ->whereHas('user', function($query) {
                                   $query->where('status', 'active');
                               })
                               ->with('section', 'user')
                               ->orderBy('last_name', 'asc')
                               ->get();

            return view('sections', [
                'students' => $students,
                'grade'    => strtoupper($section->grade_level),
                'section'  => $section,
                'males'    => $students->where('gender', 'Male'),
                'females'  => $students->where('gender', 'Female')
            ]);
        }

        $lookup = [
            'nursery'     => 'Nursery',
            'kinder'      => 'Kindergarten',
            'preparatory' => 'Preparatory',
            'grade-1'     => '1', 'grade-2' => '2', 'grade-3' => '3',
            'grade-4'     => '4', 'grade-5' => '5', 'grade-6' => '6',
        ];

        $dbValue = $lookup[$grade] ?? $grade;

        $students = Student::where('grade_level', $dbValue)
                           ->whereHas('user', function($query) {
                               $query->where('status', 'active');
                           })
                           ->with('section', 'user')
                           ->orderBy('last_name', 'asc')
                           ->get();

        return view('sections', [
            'students' => $students,
            'grade'    => strtoupper(str_replace('-', ' ', $grade)),
            'section'  => $students->first()->section ?? null,
            'males'    => $students->where('gender', 'Male'),
            'females'  => $students->where('gender', 'Female')
        ]);
    }

    public function studentInfo()
    {
        $student = Auth::user()->student()->with('section')->first();

        return view('student-view', compact('student'));
    }

    public function showStudentProfile()
    {
        $student = Auth::user()->student()->with('section')->first();

        if (!$student) {
            abort(403, 'No student profile linked to this account.');
        }

        return view('student-view', compact('student'));
    }

    public function archive($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'archived'; 
        $user->save();
        
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Archive Parent',
            'description' => Auth::user()->username . ' archived Parent account ID: ' . $id
        ]);

        return redirect()->back()->with('success', 'Parent account archived successfully!');
    }

    public function restore($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active'; 
        $user->save();
        
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Restore Parent',
            'description' => Auth::user()->username . ' restored Parent account ID: ' . $id
        ]);

        return redirect()->back()->with('success', 'Parent account restored successfully!');
    }

    public function archivedIndex()
    {
        $archivedStudents = Student::whereHas('user', function($query) {
                                $query->where('status', 'archived');
                            })
                            ->with('section', 'user')
                            ->orderBy('last_name', 'asc')
                            ->get();
                            
        return view('parent-archived-list', compact('archivedStudents')); 
    }
}