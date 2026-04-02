<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; 
use App\Models\Announcement; 
use App\Models\AnnouncementImage; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Shows different dashboard based on the logged-in user's role
    public function index()
    {
        $role = auth()->user()->role;

        // Fetch active announcement images for the banner slider
        $announcementImages = AnnouncementImage::where('status', 'active')
                                ->latest()
                                ->get();

        // Logic for Admin
        if ($role === 'admin') {
            $users = User::where('status', 'active')->get();
            return view('dashboard', compact('users', 'announcementImages'));
        }

        // Logic for Teacher and Parent
        if ($role === 'teacher' || $role === 'parent') {
            $announcements = Announcement::latest()->take(5)->get();
            return view($role . '.dashboard', compact('announcements', 'announcementImages'));
        }

        return redirect('/');
    } // <--- The ONLY brace that should be here

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

        return redirect()->route('dashboard')->with('success', 'User created successfully!');
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
} 