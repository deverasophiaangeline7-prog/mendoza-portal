<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        $now = now()->timestamp;
        $lockoutSeconds = 0;
        $lockoutStage = 0;

        $states = [];
        $sessionState = $request->session()->get('public_lockout_state');
        if (is_array($sessionState)) {
            $states[] = $sessionState;
        }

        $oldLoginId = trim((string) $request->old('login_id', ''));
        if ($oldLoginId !== '') {
            try {
                $fileState = Cache::store('file')->get('public_lockout|' . Str::lower($oldLoginId));
                if (is_array($fileState)) {
                    $states[] = $fileState;
                }
            } catch (\Throwable $e) {
                // Ignore cache read error
            }
        }

        foreach ($states as $state) {
            if (!empty($state['locked_until'])) {
                $remaining = (int) $state['locked_until'] - $now;
                if ($remaining > $lockoutSeconds) {
                    $lockoutSeconds = $remaining;
                    $lockoutStage = (int) ($state['stage'] ?? 1);
                }
            }
        }

        return view('auth.login', compact('lockoutSeconds', 'lockoutStage'));
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(['teacher', 'parent'], 'public');
        $request->session()->regenerate();

        Auth::logoutOtherDevices($request->password);

        // Normal Parent/Teacher login — no IP logged for everyday user privacy
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'User Login',
            'description' => auth()->user()->username . ' successfully logged into the system.',
        ]);

        return redirect()->route('dashboard');
    }

    public function createAdmin(Request $request): View
    {
        $now = now()->timestamp;
        $lockoutSeconds = 0;
        $lockoutStage = 0;

        $fileState = null;
        try {
            $fileState = Cache::store('file')->get('admin_lockout|' . $request->ip());
        } catch (\Throwable $e) {
            $fileState = null;
        }

        $sessionState = $request->session()->get('admin_lockout_state');

        foreach ([$fileState, $sessionState] as $state) {
            if (is_array($state) && !empty($state['locked_until'])) {
                $remaining = (int) $state['locked_until'] - $now;
                if ($remaining > $lockoutSeconds) {
                    $lockoutSeconds = $remaining;
                    $lockoutStage = (int) ($state['stage'] ?? 1);
                }
            }
        }

        return view('auth.admin-login', compact('lockoutSeconds', 'lockoutStage'));
    }

    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(['admin', 'superadmin'], 'admin');
        $request->session()->regenerate();

        Auth::logoutOtherDevices($request->password);

        // Admin login — IP logged for security auditing
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