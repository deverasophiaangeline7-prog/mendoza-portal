<?php

namespace App\Http\Requests\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        $this->ensureIsNotRateLimited($maxAttempts, $portal);

        $loginId = trim((string) $this->input('login_id'));
        $password = (string) $this->input('password');
        $throttleKey = $this->throttleKey($portal);

        // 1. Find user by username or email
        $user = User::where('username', $loginId)
            ->orWhere('email', $loginId)
            ->first();

        // 2. Allow Student LRN lookup ONLY on the public Parent/Teacher portal
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
            RateLimiter::hit($throttleKey, $decaySeconds);

            if ($portal === 'admin') {
                $this->logSecurityEvent(
                    $user ? $user->user_id : null,
                    'Failed Admin Login',
                    "Failed admin portal login attempt for '{$loginId}' from IP {$this->ip()}."
                );
            }

            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed'),
            ]);
        }

        // 4. Enforce strict portal-to-role isolation BEFORE logging in
        if (!in_array($user->role, $allowedRoles, true)) {
            RateLimiter::hit($throttleKey, $decaySeconds);

            $action = $portal === 'admin'
                ? 'Unauthorized Admin Portal Attempt'
                : 'Blocked Cross-Portal Login';

            $portalLabel = $portal === 'admin' ? 'Admin Portal' : 'Public Parent/Teacher Portal';

            $this->logSecurityEvent(
                $user->user_id,
                $action,
                "{$user->username} (" . ucfirst($user->role) . ") attempted to log in through the {$portalLabel} from IP {$this->ip()}."
            );

            // Always return generic auth error to prevent account enumeration
            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed'),
            ]);
        }

        // 5. Log the user in and clear rate limit
        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($throttleKey);
    }

    public function ensureIsNotRateLimited(int $maxAttempts = 5, string $portal = 'public'): void
    {
        $key = $this->throttleKey($portal);

        if (!RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($key);

        if ($portal === 'admin') {
            $this->logSecurityEvent(
                null,
                'Admin Login Lockout',
                "Admin login temporarily locked out for '{$this->input('login_id')}' from IP {$this->ip()}."
            );
        }

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