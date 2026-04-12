<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Assuming you use the User model for all accounts
use Illuminate\Support\Facades\Hash;

class ParentAccountController extends Controller
{
    /**
     * Store a newly created parent account in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'lrn'       => 'required|string|unique:users,lrn',
        'last_name' => 'required|string|max:255',
        'first_name'=> 'required|string|max:255',
        'username'  => 'required|string|unique:users,username',
        'password'  => 'required|string|min:8|confirmed', // Added confirmed for safety
        'gender'    => 'required|in:Male,Female',
        'birthdate' => 'required|date',
        'advisory'  => 'required|string', // Use 'advisory' to match migration
    ]);

    User::create([
        'lrn'      => $request->lrn,
        // Since migration only has 'name', combine them here:
        'name'     => $request->first_name . ' ' . $request->last_name, 
        'username' => $request->username,
        'password' => Hash::make($request->password),
        'gender'   => $request->gender,
        // advisory matches your migration column:
        'advisory' => $request->advisory, 
        'role'     => 'parent',
    ]);

    return redirect()->route('account.management')
    ->with('success', 'Parent account for ' . $request->first_name . ' has been created!');
}
}