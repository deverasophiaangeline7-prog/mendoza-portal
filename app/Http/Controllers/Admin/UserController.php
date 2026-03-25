<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Show the "Add New User" form
    public function create()
    {
        return view('admin.users.create');
    }

    // Save the new user to the database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:teacher,parent',
            'password' => 'required|min:8|confirmed',
            // If it's a teacher, they might use email. If parent, they use LRN.
            'email' => 'nullable|email|unique:users,email',
            'lrn' => 'nullable|string|unique:users,lrn',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'lrn' => $request->lrn,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('dashboard')->with('success', 'User created successfully!');
    }
}