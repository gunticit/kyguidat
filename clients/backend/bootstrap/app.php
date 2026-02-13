<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Return JSON 401 for unauthenticated API requests
        // instead of redirecting to a login route
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                abort(response()->json(['message' => 'Unauthenticated.'], 401));
            }
            return '/';
        });

        // Global middleware stack
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Trust reverse proxy (Nginx) for HTTPS
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        // Sanitize all input (XSS prevention)
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\SanitizeInput::class,
        ]);

        // API routes use token-based auth (Sanctum API tokens), not session-based
        // So we don't need EnsureFrontendRequestsAreStateful middleware
        // This avoids CSRF token issues for API calls
    
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Append session middleware to api group for social auth routes
        $middleware->appendToGroup('api.session', [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
