<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureWorker
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

        // 2 = laborer, 3 = apprentice
        if (! in_array((int) $user->user_type->value, [2, 3], true)) {
            return response()->json([
                'status'  => false,
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}
