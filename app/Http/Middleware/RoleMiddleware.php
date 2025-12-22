<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user) {
            abort(403, 'Access denied. Authentication required.');
        }
        
        // If no roles specified, just check if user is authenticated
        if (empty($roles)) {
            return $next($request);
        }
        
        // Check if user has any of the required roles
        foreach ($roles as $role) {
            // Check simple role field first (for compatibility)
            if ($user->role === $role) {
                return $next($request);
            }
            
            // Then check role relationships if method exists
            if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
                return $next($request);
            }
        }
        
        abort(403, 'Access denied. Insufficient permissions.');
    }
}
