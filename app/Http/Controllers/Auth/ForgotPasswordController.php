<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Public Password Recovery (Teachers & Parents ONLY).
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'lrn' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/i'],
        ], [
            'lrn.regex' => 'Please enter a valid @gmail.com email address.',
        ]);

        $email = trim($request->lrn);

        $user = User::where('email', $email)
                    ->orWhere('username', $email)
                    ->first();

        // Block Admin & Superadmin recovery from the public page (generic error to prevent enumeration)
        if (!$user || in_array($user->role, ['admin', 'superadmin'], true)) {
            if ($user && in_array($user->role, ['admin', 'superadmin'], true)) {
                AuditLog::create([
                    'user_id' => $user->user_id,
                    'action' => 'Blocked Public Password Reset',
                    'description' => "Blocked password reset attempt for {$user->username} on the public recovery page from IP {$request->ip()}.",
                ]);
            }

            return back()->withInput()->withErrors([
                'lrn' => 'No account found with that email address.',
            ]);
        }

        if (empty($user->email)) {
            return back()->withInput()->withErrors([
                'lrn' => 'This account does not have a registered email address.',
            ]);
        }

        $status = Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withInput()->withErrors(['lrn' => __($status)]);
    }

    /**
     * Dedicated Admin Password Recovery Form.
     */
    public function showAdminLinkRequestForm()
    {
        return view('auth.admin-forgot-password');
    }

    /**
     * Admin Password Recovery (Admin & Superadmin ONLY).
     */
    public function sendAdminResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/i'],
        ], [
            'email.regex' => 'Please enter a valid @gmail.com email address.',
        ]);

        $email = trim($request->email);

        $user = User::where('email', $email)
                    ->orWhere('username', $email)
                    ->first();

        if (!$user || !in_array($user->role, ['admin', 'superadmin'], true)) {
            return back()->withInput()->withErrors([
                'email' => 'Unable to process password reset for this account.',
            ]);
        }

        if (empty($user->email)) {
            return back()->withInput()->withErrors([
                'email' => 'This account does not have a registered email address.',
            ]);
        }

        AuditLog::create([
            'user_id' => $user->user_id,
            'action' => 'Admin Password Reset Requested',
            'description' => "{$user->username} requested an admin password reset link from IP {$request->ip()}.",
        ]);

        $status = Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withInput()->withErrors(['email' => __($status)]);
    }
}