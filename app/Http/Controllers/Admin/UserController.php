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

        // 1. Fetch active announcement images for the banner slider
        $announcementImages = AnnouncementImage::where('status', 'active')
                                ->latest()
                                ->get();

        // 2. Fetch and format calendar events for the dashboard
        // We fetch all events to populate the red highlights and details box
        $dbEvents = SchoolCalendar::all();
        $eventsData = [];

        foreach ($dbEvents as $event) {
            // We use Carbon to ensure the date format is ALWAYS YYYY-MM-DD
            // This matches the Alpine.js padding logic (e.g., 2026-04-09)
            $formattedDate = Carbon::parse($event->start_date)->format('Y-m-d');
            
            $eventsData[$formattedDate] = [
                'name' => $event->event_title,
                'ps'   => $event->description,
                'time' => $event->time, // The fix for the missing time!
            ];
        }

        // Logic for Admin
        if ($role === 'admin') {
            $users = User::where('status', 'active')->get();
            // Passing 'eventsData' so the admin can manage and view the calendar
            return view('dashboard', compact('users', 'announcementImages', 'eventsData'));
        }

        // Logic for Teacher and Parent
        if ($role === 'teacher' || $role === 'parent') {
            $announcements = Announcement::latest()->take(5)->get();
            // Passing 'eventsData' ensures teachers/parents see the same highlights and details
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
        // 1. VALIDATE PASSWORD
        $request->validate([
            'admin_password' => 'required|string'
        ]);

        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->withErrors(['admin_password' => 'Incorrect Admin Password. Finalization aborted.']);
        }

        DB::beginTransaction();

        try {
            // --- 2. HANDLE THE SCHOOL YEAR TRANSITION ---
            $currentYear = \App\Models\SchoolYear::where('status', 'active')->first();
            if (!$currentYear) throw new \Exception("No active school year found.");

            $years = explode('-', $currentYear->school_year);
            $nextYearString = ((int)$years[0] + 1) . '-' . ((int)$years[1] + 1);

            $currentYear->update(['status' => 'archived']);
            \App\Models\SchoolYear::create([
                'school_year' => $nextYearString,
                'status' => 'active'
            ]);

            // --- 3. THE "MIXED" WIPE (Crucial Step!) ---
            \App\Models\AuditLog::query()->delete(); 
            \App\Models\Attendance::query()->delete(); 
            \App\Models\SchoolCalendar::query()->delete();
            
            // Notice: We are NOT truncating Grades, BehaviorReports, or NkpEvaluations!
            // They stay in the database safely linked to their old school_year_id.

            // --- 4. PROMOTE STUDENTS ---
            // Changed from ::all() to ::with('section')->get() so we can fetch the section names
            $students = \App\Models\Student::with('section')->get();

            foreach ($students as $student) {
                
                // 1. TAKE A SNAPSHOT OF THEIR SECTION BEFORE WIPING IT
                if ($student->section) {
                    \App\Models\StudentHistory::create([
                        'student_id' => $student->student_id,
                        'school_year_id' => $currentYear->id,
                        'section_name' => strtoupper($student->section->grade_level . ' - ' . $student->section->section_name)
                    ]);
                }

                // 2. THE PROMOTION SHIFT
                if (in_array($student->promotion_status, ['promoted', 'pending']) && $student->next_grade_level) {
                    $student->grade_level = $student->next_grade_level;
                } elseif ($student->grade_level == '6' && in_array($student->promotion_status, ['promoted', 'pending'])) {
                    $student->user->status = 'archived'; 
                    $student->user->save();
                }

                // 3. THE AUTO-ASSIGN LOGIC (Replaces the 'null' wipe)
                if ($student->user->status !== 'archived') {
                    // Find the first available section for their new grade level
                    $newSection = \App\Models\Section::where('grade_level', $student->grade_level)->first();
                    
                    // Assign them to it! (If no section exists yet, it safely falls back to null)
                    // Make sure 'id' matches the primary key of your sections table (e.g., 'id' or 'section_id')
                    $student->section_id = $newSection ? $newSection->section_id : null; 
                } else {
                    $student->section_id = null; // Graduates don't need a room
                }

                // 4. RESET STATUSES FOR THE NEW YEAR
                $student->promotion_status = 'none';
                $student->next_grade_level = null;
                $student->save();
            }

            // --- 5. LOG IT ---
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Year Finalized',
                'description' => Auth::user()->username . " finalized {$currentYear->school_year}. Attendance wiped, Grades preserved."
            ]);

            DB::commit();

            return redirect()->route('account.management')
                ->with('success', "Data handled successfully. Welcome to SY {$nextYearString}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
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