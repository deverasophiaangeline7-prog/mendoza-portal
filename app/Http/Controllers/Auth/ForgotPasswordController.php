<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm() {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request) {
        $request->validate(['lrn' => 'required']);

        // Find user by LRN or Email
        $user = User::where('username', $request->lrn)
                    ->orWhere('email', $request->lrn)
                    ->first();

        if (!$user) {
            return back()->withErrors(['lrn' => 'No account found with that LRN or Email.']);
        }

        // Ensure the user account has a valid destination email
        if (empty($user->email)) {
            return back()->withErrors(['lrn' => 'This account does not have a registered email address.']);
        }

        // Send password reset link to $user->email
        $status = Password::broker()->sendResetLink([
            'email' => $user->email
        ]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['lrn' => __($status)]);
    }
}