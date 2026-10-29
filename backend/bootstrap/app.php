<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Laravel's default guest redirect is `fn () => route('login')`, which
        // throws RouteNotFoundException (this app has no `login` route) while
        // the AuthenticationException is still being built - i.e. before the
        // exception handler can turn it into a 401. Never resolve a named web
        // route for API requests; they must fail as 401 JSON instead.
        $middleware->redirectGuestsTo(function (?Request $request) {
            if ($request === null || $request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API routes must always be answered with JSON, even when the client
        // did not send an "Accept: application/json" header (browsers/fetch
        // often omit it). Without this, unauthenticated API requests fall back
        // to a redirect to the (non-existent) "login" route, which throws
        // RouteNotFoundException and surfaces as a 500 / CORS network error.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e): bool => $request->is('api/*') || $request->expectsJson()
        );

        // Belt and braces: render unauthenticated API requests as a plain
        // 401 JSON payload instead of ever calling route('login').
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage() ?: 'Unauthenticated.'], 401);
            }
        });
    })->create();
