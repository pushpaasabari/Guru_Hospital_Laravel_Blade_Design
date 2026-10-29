<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $exception, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code' => 'not_found',
                        'message' => 'The requested resource was not found.',
                    ],
                ], 404);
            }
        });

        $exceptions->render(function (Throwable $exception, $request) {
            if (app()->bound('sentry') && config('app.env') === 'production') {
                // Sentry integration would go here
            }

            if ($request->expectsJson()) {
                $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;

                return response()->json([
                    'error' => [
                        'code' => config('app.debug') ? get_class($exception) : 'internal_error',
                        'message' => config('app.debug') ? $exception->getMessage() : 'An unexpected error occurred.',
                    ],
                ], $status);
            }
        });

        $exceptions->report(function (Throwable $exception) {
            if (! config('app.debug')) {
                logger()->error('Exception caught', [
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]);
            }
        });
    })->create();
