<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles): mixed
    {
        // Split the roles by comma (e.g. "admin,superadmin" becomes ['admin', 'superadmin'])
        $allowedRoles = explode(',', $roles);

        if (!auth()->check() || !in_array(auth()->user()->role, $allowedRoles)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}