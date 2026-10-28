<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $action,
        ?string $auditableType = null,
        ?int $auditableId = null,
        ?string $description = null,
        ?array $payloadBefore = null,
        ?array $payloadAfter = null
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'user_role' => $user?->role ?? 'system',
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'description' => $description,
            'payload_before' => $payloadBefore,
            'payload_after' => $payloadAfter,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }
}

