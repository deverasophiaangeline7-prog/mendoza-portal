<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->is_first_login) {
            
            // Only allow them to see the dashboard (for the popup), submit the password form, or log out
            $allowedRoutes = ['dashboard', 'user.password.update', 'logout'];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                // If they try to type in any other URL, force them back to the dashboard
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}