<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureHirer
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('api');

        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // 0 = contractor, 1 = subcontractor
        if (! in_array((int) $user->user_type->value, [0, 1], true)) {
            return response()->json([
                'status'  => false,
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}
