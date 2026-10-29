<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
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

        $query = AuditLog::with('user')->latest('id');

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

        return Inertia::render('Audit/Index', compact('auditLogs'));
    }
}

