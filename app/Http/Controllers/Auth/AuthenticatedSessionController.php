<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

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

    public function createAdmin(Request $request): View
    {
        $state = Cache::get('admin_lockout|' . $request->ip());
        $lockoutSeconds = 0;
        $lockoutStage = 0;

        if ($state && !empty($state['locked_until'])) {
            $remaining = (int) $state['locked_until'] - now()->timestamp;
            if ($remaining > 0) {
                $lockoutSeconds = $remaining;
                $lockoutStage = (int) ($state['stage'] ?? 1);
            }
        }

        return view('auth.admin-login', compact('lockoutSeconds', 'lockoutStage'));
    }

    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(['admin', 'superadmin'], 3, 180, 'admin');
        $request->session()->regenerate();

        Auth::logoutOtherDevices($request->password);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Admin Login',
            'description' => auth()->user()->username . ' (' . ucfirst(auth()->user()->role) . ') signed in via Admin Login from IP ' . $request->ip() . '.',
        ]);

        return redirect()->route('dashboard');
    }

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