<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $role = auth()->user()->role;
        $loginType = $request->input('login_type');

        // 1. If someone used the Admin form but is NOT an admin or superadmin, block them
        if ($loginType === 'admin' && !in_array($role, ['admin', 'superadmin'])) {
            
            // 🚨 LOG: Unauthorized access to Admin portal
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Unauthorized Login Attempt',
                'description' => auth()->user()->username . ' attempted to access the Admin login portal without permission.'
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withInput()->withErrors([
                'login_id' => 'Access denied. This login is for authorized personnel only.',
            ]);
        }

        // 2. If someone used the Parent/Teacher form but IS an admin/superadmin, block them too
        if ($loginType !== 'admin' && in_array($role, ['admin', 'superadmin'])) {
            
            // 🚨 LOG: Admin using the wrong portal
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Invalid Portal Use',
                'description' => auth()->user()->username . ' (' . ucfirst($role) . ') attempted to log in through the Parent/Teacher portal.'
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withInput()->withErrors([
                'login_id' => 'Please use the Admin Login form.',
            ]);
        }

        // ✅ LOG: Successful Login
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'User Login',
            'description' => auth()->user()->username . ' successfully logged into the system.'
        ]);

        // 3. Route the user to their dashboard
        return match($role) {
            'superadmin' => redirect()->route('dashboard'),
            'admin'   => redirect()->route('dashboard'),
            'teacher' => redirect()->route('dashboard'),
            'parent'  => redirect()->route('dashboard'),
            default   => redirect('/'),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}