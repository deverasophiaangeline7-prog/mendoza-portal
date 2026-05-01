<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User; 
use App\Models\Announcement; 
use App\Models\AnnouncementImage; 
use App\Models\SchoolCalendar; // Ensure this is imported
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

        User::create([
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
            'description' => Auth::user()->username . " created a new account with role [" . $user->role . "] for: " . $user->username
        ]);

        return redirect()->route('dashboard')->with('success', 'User created successfully!');
    }
    public function finalize(Request $request)
    {
        // 1. Logic to archive data
        // Example: SchoolYear::where('status', 'active')->update(['status' => 'archived']);

        // 2. Logic to prepare for the next year
        // Example: Setting up SY 2026-2027

        // 3. Return a response
        return redirect()->route('account.management')
                         ->with('success', 'School Year 2025-2026 has been successfully finalized and archived.');
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

        $user->update([
            'name' => $request->name,
            'role' => $request->role,
            'status' => $request->status,
            'email' => $request->email,
            'lrn' => $request->lrn,
        ]);

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
                  // This also lets you search by the actual timestamp date
                  ->orWhere('created_at', 'like', "%{$search}%") 
                  ->orWhereHas('user', function ($subQ) use ($search) {
                      $subQ->where('username', 'like', "%{$search}%");
                  });
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);

    return view('audit-logs', compact('logs', 'search'));
}
}