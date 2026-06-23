<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user has admin role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please sign in to access the admin panel.');
        }

        if (!auth()->user()->is_admin) {
            auth()->logout();
            return redirect()->route('admin.login')
                ->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
