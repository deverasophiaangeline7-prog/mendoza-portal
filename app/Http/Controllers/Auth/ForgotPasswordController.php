<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    // This shows your Forgot Password page
    public function showLinkRequestForm() {
        return view('auth.forgot-password');
    }

    // This handles the "Submit" button

    public function sendResetLink(Request $request) {
    $request->validate(['lrn' => 'required']);

    // 1. Find the user by LRN or Email
    $user = \App\Models\User::where('username', $request->lrn)
                ->first();

    if (!$user) {
        return back()->withErrors(['lrn' => 'No account found with that LRN or Email.']);
    }

    // 2. Now that we've synced the DB, the broker will find the email correctly
    $status = Password::broker()->sendResetLink([
        'email' => $user->username // Use the email column now
    ]);

    return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['lrn' => __($status)]);
}
}