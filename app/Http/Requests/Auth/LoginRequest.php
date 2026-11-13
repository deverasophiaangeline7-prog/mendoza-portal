<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string'], 
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginId = $this->input('login_id');
        $password = $this->input('password');

        // 1. Look for a user by username or email
        $user = \App\Models\User::where('username', $loginId)
            ->orWhere('email', $loginId)
            ->first();

        // 2. If no user found, check if the input is a Student's LRN
        if (!$user) {
            $student = \Illuminate\Support\Facades\DB::table('students')
                ->where('lrn', $loginId)
                ->first();
                
            if ($student) {
                // Find the parent (User) linked to this student
                // Using 'user_id' based on your foreign key structure
                $user = \App\Models\User::where('user_id', $student->user_id)->first();
            }
        }

        // 3. Check if the user exists and their password is correct
        if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            // Manually log the user in
            \Illuminate\Support\Facades\Auth::login($user, $this->boolean('remember'));
            \Illuminate\Support\Facades\RateLimiter::clear($this->throttleKey());
            return;
        }

        // 4. If authentication fails, trigger rate limiting and show error
        \Illuminate\Support\Facades\RateLimiter::hit($this->throttleKey());

        throw \Illuminate\Validation\ValidationException::withMessages([
            'login_id' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login_id')).'|'.$this->ip());
    }
}