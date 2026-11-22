<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the public login view (Teachers & Parents).
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle public login (Teachers & Parents ONLY).
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(['teacher', 'parent'], 5, 60, 'public');
        $request->session()->regenerate();

        Auth::logoutOtherDevices($request->password);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'User Login',
            'description' => auth()->user()->username . ' successfully logged into the system from IP ' . $request->ip() . '.',
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Display the dedicated Admin login view (Admin & Superadmin).
     */
    public function createAdmin(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Handle Admin login (Admin & Superadmin ONLY, stricter 3-attempt / 5-min lockout).
     */
    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(['admin', 'superadmin'], 3, 300, 'admin');
        $request->session()->regenerate();

        Auth::logoutOtherDevices($request->password);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Admin Login',
            'description' => auth()->user()->username . ' (' . ucfirst(auth()->user()->role) . ') signed in via the Admin Portal from IP ' . $request->ip() . '.',
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $isAdmin = false;

        if (Auth::check()) {
            $isAdmin = in_array(auth()->user()->role, ['admin', 'superadmin'], true);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $isAdmin ? 'Admin Logout' : 'User Logout',
                'description' => auth()->user()->username . ' logged out of the system.',
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $isAdmin ? redirect()->route('admin.login') : redirect('/');
    }
}