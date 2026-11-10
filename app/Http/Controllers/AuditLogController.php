<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$user->canViewAuditLogs()) {
            abort(403, 'Unauthorized access to hospital audit logs.');
        }

        $query = AuditLog::query()
            ->select([
                'id',
                'user_name',
                'user_role',
                'action',
                'auditable_type',
                'auditable_id',
                'description',
                'ip_address',
                'created_at',
            ])
            ->latest('id');

        if ($category = $request->input('category')) {
            match ($category) {
                'ui_clicks' => $query->where('action', 'like', 'ui.%'),
                'page_views' => $query->where('action', 'page.viewed'),
                'clinical' => $query->where('action', 'not like', 'ui.%')
                    ->where('action', '!=', 'page.viewed'),
                default => null,
            };
        }

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($userName = $request->input('user_name')) {
            $query->where('user_name', 'like', "%{$userName}%");
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $auditLogs = $query->paginate(30)->withQueryString();
        $filters = $request->only(['category', 'action', 'user_name', 'date_from', 'date_to']);

        return Inertia::render('Audit/Index', compact('auditLogs', 'filters'));
    }

    /**
     * Batch-store granular frontend UI interaction events (every button/link/control click)
     * without delaying user interactions.
     */
    public function storeEvents(Request $request): HttpResponse
    {
        $validated = $request->validate([
            'events' => ['required', 'array', 'min:1', 'max:50'],
            'events.*.action' => ['nullable', 'string', 'max:100'],
            'events.*.description' => ['nullable', 'string', 'max:500'],
            'events.*.path' => ['nullable', 'string', 'max:255'],
            'events.*.element' => ['nullable', 'string', 'max:60'],
            'events.*.label' => ['nullable', 'string', 'max:200'],
            'events.*.href' => ['nullable', 'string', 'max:255'],
            'events.*.auditable_type' => ['nullable', 'string', 'max:100'],
            'events.*.auditable_id' => ['nullable', 'integer', 'min:1'],
            'events.*.metadata' => ['nullable', 'array'],
        ]);

        $user = $request->user();
        $ip = $request->ip();
        $userAgent = substr((string) $request->userAgent(), 0, 500);
        $now = now();

        $rows = [];
        foreach ($validated['events'] as $event) {
            $action = $event['action'] ?? 'ui.click';
            if (!str_starts_with($action, 'ui.')) {
                $action = 'ui.' . ltrim($action, '.');
            }

            $path = $event['path'] ?? '/';
            $auditableType = $event['auditable_type'] ?? null;
            $auditableId = $event['auditable_id'] ?? null;

            if (!$auditableType && preg_match('#^/(?:admissions|documents)/(\d+)(?:/|$)#', $path, $matches)) {
                $auditableType = 'Admission';
                $auditableId = (int) $matches[1];
            }

            $element = trim((string) ($event['element'] ?? 'control'));
            $label = trim((string) ($event['label'] ?? $element));
            $description = $event['description'] ?? trim("Clicked {$element} '{$label}' on {$path}");

            $payloadAfter = array_filter([
                'path' => $path,
                'element' => $element,
                'label' => $label,
                'href' => $event['href'] ?? null,
                'metadata' => $event['metadata'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');

            $rows[] = [
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'Guest',
                'user_role' => $user?->role ?? 'guest',
                'action' => substr($action, 0, 100),
                'auditable_type' => $auditableType,
                'auditable_id' => $auditableId,
                'description' => substr($description, 0, 500),
                'payload_before' => null,
                'payload_after' => !empty($payloadAfter) ? json_encode($payloadAfter) : null,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'created_at' => $now,
            ];
        }

        if (!empty($rows)) {
            AuditLog::insert($rows);
        }

        return response()->noContent();
    }
}

