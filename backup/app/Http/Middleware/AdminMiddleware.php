<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user has admin role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 🔥 CORRECTION : On vérifie si l'utilisateur est connecté sur le guard 'admin'
        if (!Auth::guard('admin')->check()) {
            
            // Si la requête attend du JSON (Appels API / AJAX)
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to access this area.'], 403);
            }

            // Sinon, on redirige vers la page de connexion admin avec un message d'erreur
            return redirect()->route('admin.login')->withErrors([
                'email' => 'You do not have permission to access this area. Please log in with an admin account.'
            ]);
        }

        return $next($request);
    }
}
