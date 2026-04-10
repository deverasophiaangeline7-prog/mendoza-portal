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
        // 1. Validation: Ensure all required fields are filled correctly
        $request->validate([
            'lrn'           => 'required|string|unique:users,lrn', // Often LRN is used as ID
            'last_name'     => 'required|string|max:255',
            'first_name'    => 'required|string|max:255',
            'username'      => 'required|string|unique:users,username',
            'password'      => 'required|string|min:8',
            'gender'        => 'required|in:Male,Female',
            'birthdate'     => 'required|date',
            'grade_section' => 'required|string',
        ]);

        // 2. Create the Account
        // Note: You may need to add these columns to your 'users' table 
        // or a separate 'parents' table via a migration.
        User::create([
            'lrn'           => $request->lrn,
            'name'          => $request->first_name . ' ' . $request->last_name,
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'middle_name'   => $request->no_middle ? null : $request->middle_name,
            'ext_name'      => $request->ext_name,
            'username'      => $request->username,
            'password'      => Hash::make($request->password), // Always hash passwords!
            'gender'        => $request->gender,
            'birthdate'     => $request->birthdate,
            'grade_section' => $request->grade_section,
            'role'          => 'parent', // Identifying the account type
        ]);

        // 3. Redirect with a success message
        return redirect()
            ->route('account.management')
            ->with('success', 'Parent account for ' . $request->first_name . ' has been created!');
    }
}