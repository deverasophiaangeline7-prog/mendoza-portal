<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LogUserActivity
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $expiresAt = now()->addMinutes(5); 
            Cache::put('user-is-online-' . Auth::user()->user_id, true, $expiresAt);
        }
        return $next($request);
    }
}