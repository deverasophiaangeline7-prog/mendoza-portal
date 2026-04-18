<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
{
    $request->validate([
        'username' => ['required', 'string', 'max:255', 'unique:users'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed'],
        'role' => ['required'],
        // Add validations for your profile fields
        'first_name' => ['required', 'string'],
        'last_name' => ['required', 'string'],
    ]);

    // 1. Create the User (Login Credentials)
    $user = User::create([
        'username' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
    ]);

    // 2. Based on the role, create the profile
    if ($user->role === 'student') {
        Student::create([
            'user_id' => $user->user_id,
            'lrn' => $request->lrn,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'grade_level' => $request->grade_level,
            // ... other student fields
        ]);
    } elseif ($user->role === 'teacher') {
        Teacher::create([
            'user_id' => $user->user_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'specialization' => $request->specialization,
        ]);
    }

    event(new Registered($user));
    Auth::login($user);

    return redirect(RouteServiceProvider::HOME);
}
}
