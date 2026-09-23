<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User; 
use App\Models\Announcement; 
use App\Models\AnnouncementImage; 
use App\Models\SchoolCalendar; 
use App\Models\Grade;
use App\Models\BehaviorReport;
use App\Models\NkpEvaluation;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserController extends Controller
{
    // Shows different dashboard based on the logged-in user's role
    public function index()
    {
        $role = auth()->user()->role;

        $announcementImages = AnnouncementImage::where('status', 'active')
                                ->latest()
                                ->get();

        $dbEvents = SchoolCalendar::all();
        $eventsData = [];

        foreach ($dbEvents as $event) {
            $formattedDate = Carbon::parse($event->start_date)->format('Y-m-d');
            
            $eventsData[$formattedDate] = [
                'name' => $event->event_title,
                'ps'   => $event->description,
                'time' => $event->time,
            ];
        }

        if ($role === 'admin') {
            $users = User::where('status', 'active')->get();
            $activeYear = SchoolYear::where('status', 'active')->first(); 
            
            $hasAnyGrades = false;
            if ($activeYear) {
                $gradesExist = DB::table('grades')->where('school_year_id', $activeYear->id)->exists();
                $nkpExist = DB::table('nkp_evaluations')->where('school_year_id', $activeYear->id)->exists();
                $hasAnyGrades = $gradesExist || $nkpExist;
            }
            
            return view('dashboard', compact('users', 'announcementImages', 'eventsData', 'activeYear'));
        }

        if ($role === 'teacher' || $role === 'parent') {
            $announcements = Announcement::latest()->take(5)->get();
            return view($role . '.dashboard', compact('announcements', 'announcementImages', 'eventsData'));
        }

        return redirect('/');
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,teacher,parent',
            'password' => 'required|min:8|confirmed',
            'email' => 'nullable|email|unique:users,email',
            'lrn' => 'nullable|string|unique:users,lrn',
        ]);

        if ($request->filled('email') && $request->filled('lrn') && $request->email === $request->lrn) {
            return back()->withErrors(['lrn' => 'Teacher ID/LRN and Email cannot be the same.'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'lrn' => $request->lrn,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Account Created',
            'description' => Auth::user()->username . " created a new account with role [" . $user->role . "] for: " . $user->name
        ]);

        return redirect()->route('dashboard')->with('success', 'User created successfully!');
    }

    public function finalize(Request $request)
    {
        $request->validate([
            'admin_password' => 'required|string'
        ]);

        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->withErrors(['admin_password' => 'Incorrect Admin Password. Finalization aborted.']);
        }

        $currentYear = \App\Models\SchoolYear::where('status', 'active')->first();
        if (!$currentYear) return back()->with('error', 'No active school year found.');

        // ==========================================
        // NEW: STRICT TERM 3 TIME-LOCK
        // ==========================================
        if (!$currentYear->term3_end || \Carbon\Carbon::now()->lessThan(\Carbon\Carbon::parse($currentYear->term3_end)->endOfDay())) {
            $endDate = $currentYear->term3_end ? \Carbon\Carbon::parse($currentYear->term3_end)->format('M d, Y') : 'UNSET';
            return back()->with('error', "FINALIZATION BLOCKED: You cannot finalize the school year until Term 3 has officially ended (Expected: {$endDate}).");
        }
        // ==========================================

        $studentsWithAnyGrades = \App\Models\Student::whereNotNull('section_id')->has('grades')->count();
        $studentsWithAnyEvals = DB::table('nkp_evaluations')->count();

        if ($studentsWithAnyGrades === 0 && $studentsWithAnyEvals === 0) {
            return back()->with('error', 'FINALIZATION BLOCKED: This school year has no grades or evaluations recorded yet! You cannot close a school year that just started.');
        }

        $elementaryLevels = ['1', 'GRADE 1', '2', 'GRADE 2', '3', 'GRADE 3', '4', 'GRADE 4', '5', 'GRADE 5', '6', 'GRADE 6'];
        $nkpLevels = ['NURSERY', 'KINDERGARTEN', 'KINDER', 'PREPARATORY'];

        $activeStudents = \App\Models\Student::whereNotNull('section_id')
            ->where('section_id', '!=', '')
            ->with('grades')
            ->get();

        $incompleteElementaryCount = 0;
        $incompleteNkpCount = 0;

        foreach ($activeStudents as $student) {
            $grade = strtoupper(trim($student->grade_level));
            
            if (in_array($grade, $elementaryLevels)) {
                $expectedSubjects = DB::table('subject_assignments')
                    ->where('section_id', $student->section_id)
                    ->count();

                $completedSubjects = $student->grades->filter(function ($gradeRecord) {
                    return !is_null($gradeRecord->term1) && 
                           !is_null($gradeRecord->term2) && 
                           !is_null($gradeRecord->term3);
                })->count();

                if ($expectedSubjects > 0 && $completedSubjects < $expectedSubjects) {
                    $incompleteElementaryCount++;
                }
            }

            if (in_array($grade, $nkpLevels)) {
                $evaluation = DB::table('nkp_evaluations')
                    ->where('student_id', $student->student_id ?? $student->id)
                    ->first();

                if (!$evaluation || is_null($evaluation->term1) || is_null($evaluation->term2) || is_null($evaluation->term3)) {
                    $incompleteNkpCount++;
                }
            }
        }

        if ($incompleteElementaryCount > 0 || $incompleteNkpCount > 0) {
            $errorMessage = "FINALIZATION BLOCKED: ";
            if ($incompleteElementaryCount > 0) $errorMessage .= "{$incompleteElementaryCount} Elementary student(s) missing grades for all 3 terms. ";
            if ($incompleteNkpCount > 0) $errorMessage .= "{$incompleteNkpCount} NKP student(s) missing evaluations for all 3 terms. ";
            return back()->with('error', trim($errorMessage) . " Please complete these records before closing the year.");
        }

        DB::beginTransaction();
        try {
            $years = explode('-', $currentYear->school_year);
            $nextYearString = ((int)$years[0] + 1) . '-' . ((int)$years[1] + 1);

            $currentYear->update(['status' => 'archived']);
            \App\Models\SchoolYear::create(['school_year' => $nextYearString, 'status' => 'active']);

            \App\Models\AuditLog::query()->delete(); 
            \App\Models\Attendance::query()->delete(); 
            \App\Models\SchoolCalendar::query()->delete();
            DB::table('subject_assignments')->delete(); 

            $students = \App\Models\Student::with('section')->get();

            foreach ($students as $student) {
                if ($student->section) {
                    \App\Models\StudentHistory::create([
                        'student_id' => $student->student_id,
                        'school_year_id' => $currentYear->id,
                        'grade_level' => strtoupper(trim($student->grade_level)),
                        'section_name' => strtoupper($student->section->grade_level . ' - ' . $student->section->section_name)
                    ]);
                }

                if (in_array($student->promotion_status, ['promoted', 'pending']) && $student->next_grade_level) {
                    $student->grade_level = $student->next_grade_level;
                } elseif ($student->grade_level == '6' && in_array($student->promotion_status, ['promoted', 'pending'])) {
                    $student->user->status = 'archived'; 
                    $student->user->save();
                }

                if ($student->user->status !== 'archived') {
                    $newSection = \App\Models\Section::where('grade_level', $student->grade_level)->first();
                    $student->section_id = $newSection ? $newSection->section_id : null; 
                } else {
                    $student->section_id = null; 
                }

                $student->promotion_status = 'none';
                $student->next_grade_level = null;
                $student->save();
            }

            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Year Finalized',
                'description' => Auth::user()->username . " finalized {$currentYear->school_year}. All 3 terms verified, sections and subjects reset."
            ]);

            DB::commit(); 
            
            return back()->with('success', "School Year finalized successfully. Welcome to SY {$nextYearString}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,teacher,parent',
            'status' => 'required|in:active,archived',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'lrn' => 'nullable|string|unique:users,lrn,' . $user->id,
        ]);

        $oldStatus = $user->status;

        $user->update([
            'name' => $request->name,
            'role' => $request->role,
            'status' => $request->status,
            'email' => $request->email,
            'lrn' => $request->lrn,
        ]);

        if ($oldStatus !== $request->status) {
            $actionName = $request->status === 'archived' ? 'Archive User' : 'Restore User';
            
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $actionName,
                'description' => Auth::user()->username . " changed the status of {$user->name} to {$request->status}."
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'User updated successfully!');
    }

    public function logs(Request $request)
    {
        $search = $request->query('search');

        $logs = \App\Models\AuditLog::with('user')
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      
                      // Format 1: Matches "May 05, 2026" (Full month name)
                      ->orWhereRaw("DATE_FORMAT(created_at, '%M %d, %Y') LIKE ?", ["%{$search}%"])
                      
                      // Format 2: Matches "May 05, 2026" or "Aug 05, 2026" (Short month name)
                      ->orWhereRaw("DATE_FORMAT(created_at, '%b %d, %Y') LIKE ?", ["%{$search}%"])
                      
                      // Format 3: Matches "05-05-2026" (Numbers with dashes)
                      ->orWhereRaw("DATE_FORMAT(created_at, '%m-%d-%Y') LIKE ?", ["%{$search}%"]) 
                      
                      ->orWhereHas('user', function ($subQ) use ($search) {
                          $subQ->where('username', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('audit-logs', compact('logs', 'search'));
    }

    public function updateStudent(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'required|string',
        ]);

        $student = Student::findOrFail($id);
        
        $student->update([
            'first_name' => strtoupper($request->first_name),
            'last_name' => strtoupper($request->last_name),
            'lrn' => $request->lrn,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Edit Student',
            'description' => auth()->user()->username . ' updated student record for LRN: ' . $request->lrn
        ]);

        return redirect()->back()->with('success', 'Student updated successfully!');
    }

    /**
     * Admin Force Password Reset 
     * Allows Admin to reset any user's password using their LRN or Email
     */
    public function resetUserPassword(Request $request)
    {
        // 1. Validate the incoming request
        $request->validate([
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed'],
        ]);

        // 2. Find the user by their LRN or Email (stored in the username column)
        $user = User::where('username', $request->login_id)->first();

        // 3. If they typed an LRN/Email that doesn't exist, throw an error
        if (!$user) {
            return back()->with('error', 'User not found in the system. Please check the LRN or Email.');
        }

        // 4. THE NEW FIX: Check if the new password perfectly matches their current password!
        if (Hash::check($request->password, $user->password)) {
            return back()->with('error', 'The new password cannot be the exact same as their current password!');
        }

        // 5. Securely hash the new password and save it
        $user->password = Hash::make($request->password);
        $user->save();

        // 6. Log the action
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Admin Password Reset',
            'description' => Auth::user()->username . ' forcibly reset the password for user: ' . $user->username
        ]);

        // 7. Redirect back with your green success toast!
        return back()->with('success', 'Password successfully reset for ' . $user->username);
    }

    public function updateTerms(Request $request)
    {
        // Validate the dates
        $validated = $request->validate([
            'term1_start' => 'required|date',
            'term1_end'   => 'required|date|after_or_equal:term1_start',
            
            'term2_start' => 'required|date|after_or_equal:term1_end',
            'term2_end'   => 'required|date|after_or_equal:term2_start',
            
            'term3_start' => 'required|date|after_or_equal:term2_end',
            'term3_end'   => 'required|date|after_or_equal:term3_start',
        ]);

        // Fetch the currently active school year 
        // (Note: your web.php uses 'status' => 'active')
        $activeYear = SchoolYear::where('status', 'active')->first();

        if (!$activeYear) {
            return back()->with('error', 'No active school year found to update.');
        }

        // Update the record in the database
        $activeYear->update([
            'term1_start' => $validated['term1_start'],
            'term1_end'   => $validated['term1_end'],
            'term2_start' => $validated['term2_start'],
            'term2_end'   => $validated['term2_end'],
            'term3_start' => $validated['term3_start'],
            'term3_end'   => $validated['term3_end'],
        ]);

        return back()->with('success', 'Term Schedule securely updated.');
    }
}