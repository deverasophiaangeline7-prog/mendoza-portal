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
        'login_id' => ['required', 'string'], // We will use this name in the HTML too
        'password' => ['required', 'string'],
    ];
}

public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // 1. Grab what the user typed in the box (LRN or Email)
        $loginField = $this->input('login_id');

        // 2. If they typed numbers (an LRN), translate it!
        if (is_numeric($loginField)) {
            $student = \App\Models\Student::where('lrn', $loginField)->first();
            
            // Safely swap the LRN for the Parent's email/username
            if ($student && $student->user) {
                $loginField = $student->user->username; 
            }
        }

        // 3. THE FIX: ALWAYS search the 'username' column!
        // Because in your database, even emails are saved under 'username'.
        if (! Auth::attempt(['username' => $loginField, 'password' => $this->input('password')], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login_id' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
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
