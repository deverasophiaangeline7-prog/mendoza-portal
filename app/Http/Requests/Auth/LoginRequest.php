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
use Illuminate\Support\Facades\RateLimiter;
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
        int $maxAttempts = 5,
        int $decaySeconds = 60,
        string $portal = 'public'
    ): void {
        if ($portal === 'admin') {
            $this->ensureAdminNotLockedOut();
        } else {
            $this->ensureIsNotRateLimited($maxAttempts, $portal);
        }

        $loginId = trim((string) $this->input('login_id'));
        $password = (string) $this->input('password');

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
                $this->handleAdminFailure($loginId);
            }

            $throttleKey = $this->throttleKey($portal);
            RateLimiter::hit($throttleKey, $decaySeconds);

            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed'),
            ]);
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
                $this->handleAdminFailure($loginId);
            }

            $throttleKey = $this->throttleKey($portal);
            RateLimiter::hit($throttleKey, $decaySeconds);

            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed'),
            ]);
        }

        // 5. Success — log in and clear counters
        Auth::login($user, $this->boolean('remember'));

        if ($portal === 'admin') {
            Cache::forget($this->adminLockoutKey());
        } else {
            RateLimiter::clear($this->throttleKey($portal));
        }
    }

    /**
     * Progressive Admin Lockout Key (per IP so switching usernames can't bypass it).
     */
    public function adminLockoutKey(): string
    {
        return 'admin_lockout|' . $this->ip();
    }

    /**
     * Check if Admin Login is currently inside a Stage 1 (3-min) or Stage 2 (10-min) lockout.
     */
    protected function ensureAdminNotLockedOut(): void
    {
        $state = Cache::get($this->adminLockoutKey());

        if (!$state || empty($state['locked_until'])) {
            return;
        }

        $remaining = (int) $state['locked_until'] - now()->timestamp;

        if ($remaining > 0) {
            event(new Lockout($this));

            throw ValidationException::withMessages([
                'login_id' => "Too many failed login attempts. Please try again in {$remaining} seconds.",
            ]);
        }
    }

    /**
     * Record a failed Admin attempt:
     * - 3 failed attempts -> 3-minute lockout (180s)
     * - 2 more failed attempts after that (5th fail) -> 10-minute lockout (600s)
     */
    protected function handleAdminFailure(string $loginId): void
    {
        $key = $this->adminLockoutKey();
        $state = Cache::get($key, [
            'attempts' => 0,
            'locked_until' => null,
            'stage' => 0,
        ]);

        $state['attempts'] = (int) ($state['attempts'] ?? 0) + 1;

        // Stage 1: 3rd failed attempt -> 3-minute lock (180 seconds)
        if ($state['attempts'] === 3) {
            $lockSeconds = 180;
            $state['locked_until'] = now()->timestamp + $lockSeconds;
            $state['stage'] = 1;
            Cache::put($key, $state, 3600);

            event(new Lockout($this));
            $this->logSecurityEvent(
                null,
                'Admin Login Lockout (3 Mins)',
                "Admin login locked for 3 minutes after 3 failed attempts for '{$loginId}' from IP {$this->ip()}."
            );

            throw ValidationException::withMessages([
                'login_id' => 'Too many failed attempts. Admin login is locked for 3 minutes.',
            ]);
        }

        // Stage 2: 5th failed attempt (2 more after 1st lock) -> 10-minute lock (600 seconds)
        if ($state['attempts'] >= 5) {
            $lockSeconds = 600;
            $state['locked_until'] = now()->timestamp + $lockSeconds;
            $state['stage'] = 2;
            Cache::put($key, $state, 3600);

            event(new Lockout($this));
            $this->logSecurityEvent(
                null,
                'Admin Login Lockout (10 Mins)',
                "Admin login locked for 10 minutes after repeated failed attempts for '{$loginId}' from IP {$this->ip()}."
            );

            throw ValidationException::withMessages([
                'login_id' => 'Too many failed attempts. Admin login is locked for 10 minutes.',
            ]);
        }

        // Not locked yet — save updated attempt count (expires after 1 hour of inactivity)
        $state['locked_until'] = null;
        Cache::put($key, $state, 3600);

        if ($state['attempts'] < 3) {
            $remaining = 3 - $state['attempts'];
            $word = $remaining === 1 ? 'attempt' : 'attempts';
            $message = trans('auth.failed') . " ({$remaining} {$word} remaining before 3-minute lockout.)";
        } else {
            // Attempt 4 (1st fail after Stage 1 lockout)
            $message = trans('auth.failed') . ' (1 attempt remaining before 10-minute lockout.)';
        }

        throw ValidationException::withMessages([
            'login_id' => $message,
        ]);
    }

    /**
     * Standard rate limiting for Public Parent/Teacher Login.
     */
    public function ensureIsNotRateLimited(int $maxAttempts = 5, string $portal = 'public'): void
    {
        $key = $this->throttleKey($portal);

        if (!RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'login_id' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(string $portal = 'public'): string
    {
        return Str::transliterate($portal . '|' . Str::lower($this->string('login_id')) . '|' . $this->ip());
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