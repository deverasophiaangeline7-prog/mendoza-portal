<?php

namespace App\Http\Requests\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(
        array $allowedRoles = ['teacher', 'parent'],
        string $portal = 'public'
    ): void {
        $loginId = trim((string) $this->input('login_id'));
        $password = (string) $this->input('password');

        if ($portal === 'admin') {
            $this->ensureNotLockedOut($this->adminLockoutKey(), 'admin_lockout_state', 'admin');
        } else {
            $this->ensureNotLockedOut($this->publicLockoutKey($loginId), 'public_lockout_state', 'public');
        }

        // 1. Find user by username or email
        $user = User::where('username', $loginId)
            ->orWhere('email', $loginId)
            ->first();

        // 2. Allow Student LRN lookup ONLY on the public Parent/Teacher login
        if (!$user && $portal === 'public') {
            $student = DB::table('students')
                ->where('lrn', $loginId)
                ->first();

            if ($student) {
                $user = User::where('user_id', $student->user_id)->first();
            }
        }

        // 3. Verify credentials
        if (!$user || !Hash::check($password, $user->password)) {
            if ($portal === 'admin') {
                $this->logSecurityEvent(
                    $user ? $user->user_id : null,
                    'Failed Admin Login',
                    "Failed admin login attempt for '{$loginId}' from IP {$this->ip()}."
                );
                $this->handleProgressiveFailure($this->adminLockoutKey(), 'admin_lockout_state', 'admin', $loginId);
            }

            $this->handleProgressiveFailure($this->publicLockoutKey($loginId), 'public_lockout_state', 'public', $loginId);
        }

        // 4. Enforce strict role isolation BEFORE logging in
        if (!in_array($user->role, $allowedRoles, true)) {
            $action = $portal === 'admin'
                ? 'Unauthorized Admin Login Attempt'
                : 'Blocked Cross-Login Attempt';

            $entryLabel = $portal === 'admin' ? 'Admin Login' : 'Parent/Teacher Login';

            $this->logSecurityEvent(
                $user->user_id,
                $action,
                "{$user->username} (" . ucfirst($user->role) . ") attempted to log in through the {$entryLabel} from IP {$this->ip()}."
            );

            if ($portal === 'admin') {
                $this->handleProgressiveFailure($this->adminLockoutKey(), 'admin_lockout_state', 'admin', $loginId);
            }

            $this->handleProgressiveFailure($this->publicLockoutKey($loginId), 'public_lockout_state', 'public', $loginId);
        }

        // 5. Success — log in and clear lockout counters
        Auth::login($user, $this->boolean('remember'));

        if ($portal === 'admin') {
            $this->clearLockoutState($this->adminLockoutKey(), 'admin_lockout_state');
        } else {
            $this->clearLockoutState($this->publicLockoutKey($loginId), 'public_lockout_state');
        }
    }

    /**
     * Admin locks by IP (prevents username rotation); Public locks by LRN/Email (protects shared school Wi-Fi).
     */
    public function adminLockoutKey(): string
    {
        return 'admin_lockout|' . $this->ip();
    }

    public function publicLockoutKey(string $loginId): string
    {
        return 'public_lockout|' . Str::lower(trim($loginId));
    }

    protected function getLockoutState(string $cacheKey, string $sessionKey): array
    {
        $default = ['attempts' => 0, 'locked_until' => null, 'stage' => 0, 'updated_at' => 0];
        $now = now()->timestamp;

        $fileState = null;
        try {
            $fileState = Cache::store('file')->get($cacheKey);
        } catch (\Throwable $e) {
            $fileState = null;
        }

        $sessionState = $this->session()->get($sessionKey);

        $candidates = [];
        foreach ([$fileState, $sessionState] as $st) {
            if (is_array($st) && ($now - (int) ($st['updated_at'] ?? $now)) <= 3600) {
                $candidates[] = $st;
            }
        }

        if (empty($candidates)) {
            return $default;
        }

        usort($candidates, function ($a, $b) {
            $attDiff = ((int) ($b['attempts'] ?? 0)) <=> ((int) ($a['attempts'] ?? 0));
            if ($attDiff !== 0) {
                return $attDiff;
            }
            return ((int) ($b['locked_until'] ?? 0)) <=> ((int) ($a['locked_until'] ?? 0));
        });

        return array_merge($default, $candidates[0]);
    }

    protected function saveLockoutState(string $cacheKey, string $sessionKey, array $state): void
    {
        $state['updated_at'] = now()->timestamp;
        $this->session()->put($sessionKey, $state);

        try {
            Cache::store('file')->put($cacheKey, $state, 3600);
        } catch (\Throwable $e) {
            // Session fallback already saved above
        }
    }

    protected function clearLockoutState(string $cacheKey, string $sessionKey): void
    {
        $this->session()->forget($sessionKey);

        try {
            Cache::store('file')->forget($cacheKey);
        } catch (\Throwable $e) {
            // Ignore cache clear failure
        }
    }

    protected function ensureNotLockedOut(string $cacheKey, string $sessionKey, string $portal): void
    {
        $state = $this->getLockoutState($cacheKey, $sessionKey);

        if (empty($state['locked_until'])) {
            return;
        }

        $remaining = (int) $state['locked_until'] - now()->timestamp;

        if ($remaining > 0) {
            // Sync to session so countdown banner renders immediately even in a fresh/incognito tab
            $this->session()->put($sessionKey, $state);
            event(new Lockout($this));

            throw ValidationException::withMessages([
                'login_id' => "Too many failed login attempts. Please try again in {$remaining} seconds.",
            ]);
        }
    }

    /**
     * Progressive Lockout for both portals:
     * - 3 failed attempts -> 3-minute lockout (180s)
     * - 2 more failed attempts (5th fail) -> 10-minute lockout (600s)
     */
    protected function handleProgressiveFailure(string $cacheKey, string $sessionKey, string $portal, string $loginId): void
    {
        $state = $this->getLockoutState($cacheKey, $sessionKey);
        $state['attempts'] = (int) ($state['attempts'] ?? 0) + 1;
        $label = $portal === 'admin' ? 'Admin' : 'Parent/Teacher';

        // Stage 1: 3rd failed attempt -> 3-minute lock
        if ($state['attempts'] === 3) {
            $state['locked_until'] = now()->timestamp + 180;
            $state['stage'] = 1;
            $this->saveLockoutState($cacheKey, $sessionKey, $state);

            event(new Lockout($this));
            $this->logSecurityEvent(
                null,
                "{$label} Login Lockout (3 Mins)",
                "{$label} login locked for 3 minutes after 3 failed attempts for '{$loginId}' from IP {$this->ip()}."
            );

            throw ValidationException::withMessages([
                'login_id' => "Too many failed attempts. {$label} login is locked for 3 minutes.",
            ]);
        }

        // Stage 2: 5th failed attempt -> 10-minute lock
        if ($state['attempts'] >= 5) {
            $state['locked_until'] = now()->timestamp + 600;
            $state['stage'] = 2;
            $this->saveLockoutState($cacheKey, $sessionKey, $state);

            event(new Lockout($this));
            $this->logSecurityEvent(
                null,
                "{$label} Login Lockout (10 Mins)",
                "{$label} login locked for 10 minutes after repeated failed attempts for '{$loginId}' from IP {$this->ip()}."
            );

            throw ValidationException::withMessages([
                'login_id' => "Too many failed attempts. {$label} login is locked for 10 minutes.",
            ]);
        }

        // Not locked yet
        $state['locked_until'] = null;
        $this->saveLockoutState($cacheKey, $sessionKey, $state);

        if ($state['attempts'] < 3) {
            $remaining = 3 - $state['attempts'];
            $word = $remaining === 1 ? 'attempt' : 'attempts';
            $message = trans('auth.failed') . " ({$remaining} {$word} remaining before 3-minute lockout.)";
        } else {
            $message = trans('auth.failed') . ' (1 attempt remaining before 10-minute lockout.)';
        }

        throw ValidationException::withMessages([
            'login_id' => $message,
        ]);
    }

    protected function logSecurityEvent(?int $userId, string $action, string $description): void
    {
        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
            ]);
        } catch (\Throwable $e) {
            Log::warning("[{$action}] {$description}");
        }
    }
}