@extends('layouts.app')
@section('title', 'System Audit Trail — Guru Hospital')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark">Tamper-Resistant System Audit Trail</h1>
        <p class="text-muted mb-0">Immutable tracking of user authentication, patient creation, clinical edits, approvals, and PDF exports.</p>
    </div>
    <span class="badge bg-secondary py-2 px-3">
        <i class="bi bi-shield-lock-fill me-1"></i> NABH / HIPAA Compliant Audit Log
    </span>
</div>

<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('audit.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="action" class="form-control form-control-sm" placeholder="Action (e.g. admission.created, document.approved)" value="{{ request('action') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="user_name" class="form-control form-control-sm" placeholder="Staff Name" value="{{ request('user_name') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Timestamp</th>
                    <th>Staff User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Entity</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                <tr>
                    <td class="text-nowrap small text-muted">
                        {{ $log->created_at?->format('d/m/Y H:i:s') }}
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $log->user_name }}</div>
                        <span class="badge bg-light text-dark border text-uppercase" style="font-size:0.65rem;">{{ $log->user_role }}</span>
                    </td>
                    <td>
                        <span class="badge bg-secondary font-monospace">{{ $log->action }}</span>
                    </td>
                    <td>
                        <span class="small">{{ $log->description }}</span>
                    </td>
                    <td>
                        @if($log->auditable_type)
                            <span class="badge bg-light text-primary border">{{ $log->auditable_type }} #{{ $log->auditable_id }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="small text-muted font-monospace">
                        {{ $log->ip_address ?? '127.0.0.1' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">No audit events found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($auditLogs->hasPages())
    <div class="card-footer bg-white py-2">
        {{ $auditLogs->links() }}
    </div>
    @endif
</div>
@endsection

