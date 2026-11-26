<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\LogHttpActivityMiddleware::class,
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
            if (
                $exception instanceof ValidationException
                || $exception instanceof AuthenticationException
                || $exception instanceof AuthorizationException
            ) {
                return null;
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

$desktopEnvPath = getenv('LARAVEL_ENV_PATH') ?: ($_SERVER['LARAVEL_ENV_PATH'] ?? ($_ENV['LARAVEL_ENV_PATH'] ?? null));
if (is_string($desktopEnvPath) && $desktopEnvPath !== '') {
    $app->useEnvironmentPath($desktopEnvPath);
}

$desktopStoragePath = getenv('LARAVEL_STORAGE_PATH') ?: ($_SERVER['LARAVEL_STORAGE_PATH'] ?? ($_ENV['LARAVEL_STORAGE_PATH'] ?? null));
if (is_string($desktopStoragePath) && $desktopStoragePath !== '') {
    $app->useStoragePath($desktopStoragePath);
}

foreach (['APP_SERVICES_CACHE', 'APP_PACKAGES_CACHE', 'APP_CONFIG_CACHE', 'APP_ROUTES_CACHE', 'APP_EVENTS_CACHE'] as $cacheEnvKey) {
    $cachePath = getenv($cacheEnvKey) ?: ($_SERVER[$cacheEnvKey] ?? ($_ENV[$cacheEnvKey] ?? null));
    if (is_string($cachePath) && preg_match('/^[A-Za-z]:[\\\\\\/]/', $cachePath)) {
        $drive = substr($cachePath, 0, 2);
        $app->addAbsoluteCachePathPrefix(strtoupper($drive));
        $app->addAbsoluteCachePathPrefix(strtolower($drive));
    }
}

return $app;
