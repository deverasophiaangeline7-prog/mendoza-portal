<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->is_first_login) {
            
            // Allow access ONLY to profile updates and logout
            if (!$request->routeIs('profile.edit', 'profile.update', 'user.password.update', 'profile.photo.update', 'logout')) {
                
                return redirect()->route('profile.edit')->with('error', 'Security Check: You must change your default password before accessing the system.');
            }
        }

        return $next($request);
    }
}
