<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Log user login events
        if ($request->routeIs('login') && $request->isMethod('POST') && Auth::check()) {
            AuditService::logLogin(Auth::user());
        }

        return $next($request);
    }
}