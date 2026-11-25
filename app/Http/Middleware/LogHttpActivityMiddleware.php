<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogHttpActivityMiddleware
{
    private const EXCLUDED_PATHS = [
        'audit-logs/events',
        'up',
    ];

    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'google_client_secret',
        'google_refresh_token',
        'google_service_account_json',
        'code',
        '_token',
        '_method',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkipBefore($request)) {
            return $next($request);
        }

        $userBefore = Auth::user() ?? $request->user();
        $response = $next($request);
        $user = Auth::user() ?? $request->user() ?? $userBefore;

        try {
            $this->recordHttpEvent($request, $response, $user);
        } catch (Throwable) {
            // Do not interrupt clinical workflow responses if logging fails on unmigrated setups.
        }

        return $response;
    }

    private function shouldSkipBefore(Request $request): bool
    {
        $path = trim($request->path(), '/');
        if (in_array($path, self::EXCLUDED_PATHS, true)) {
            return true;
        }

        $purpose = strtolower((string) ($request->header('Purpose') ?: $request->header('Sec-Purpose', '')));
        if (str_contains($purpose, 'prefetch')) {
            return true;
        }

        return false;
    }

    private function recordHttpEvent(Request $request, Response $response, mixed $user): void
    {
        $method = strtoupper($request->method());
        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        $route = $request->route();
        $routeName = $route?->getName();
        [$auditableType, $auditableId] = $this->resolveAuditable($route?->parameters() ?? []);

        $action = $method === 'GET' ? 'page.viewed' : 'http.'.strtolower($method);
        $status = $response->getStatusCode();

        $description = $routeName
            ? "{$method} {$path} ({$routeName}) [{$status}]"
            : "{$method} {$path} [{$status}]";

        $meta = [
            'method' => $method,
            'path' => $path,
            'route' => $routeName,
            'status' => $status,
            'inertia' => $request->headers->has('X-Inertia'),
        ];

        $query = $request->query();
        if (!empty($query)) {
            $meta['query'] = Arr::except($query, self::SENSITIVE_KEYS);
        }

        if ($method !== 'GET') {
            $input = Arr::except($request->input(), self::SENSITIVE_KEYS);
            $meta['input_keys'] = array_keys($input);
        }

        AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Guest',
            'user_role' => $user?->role ?? 'guest',
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'description' => $description,
            'payload_after' => $meta,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array{0: ?string, 1: ?int}
     */
    private function resolveAuditable(array $parameters): array
    {
        foreach ($parameters as $param) {
            if ($param instanceof Model) {
                return [class_basename($param), is_numeric($param->getKey()) ? (int) $param->getKey() : null];
            }
        }

        return [null, null];
    }
}

