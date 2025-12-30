<?php

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // A) Laravel auth failures (no valid user)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::warning('Unauthorized', 401);
            }

            return null; // keep default redirect for web routes
        });

        // B) JWT token expired
        $exceptions->render(function (TokenExpiredException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::warning('Token expired', 401);
            }

            return null;
        });

        // C) Invalid / malformed / wrong JWT token
        $exceptions->render(function (TokenInvalidException|JWTException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::warning('Unauthorized', 401);
            }

            return null;
        });

        // Custom 404 JSON for API routes
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::warning('Route not found', 404);
                // Or any message you prefer, e.g.:
                // return ApiResponse::error('Invalid API endpoint', 404);
            }

            return null; // fall back to default for web routes
        });
    })->create();
