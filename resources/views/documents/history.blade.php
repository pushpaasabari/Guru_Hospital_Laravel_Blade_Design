@extends('layouts.app')
@section('title', 'Document History & Revisions — ' . $admission->patient_name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">Audit & Revision Trail</h1>
        <p class="text-muted mb-0">Complete lifecycle history for <strong>{{ $admission->patient_name }}</strong> (UHID: {{ $admission->uhid }}, IP: {{ $admission->ip_no }})</p>
    </div>
    <a href="{{ route('documents.preview', $admission) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Document Pack
    </a>
</div>

<div class="row g-4">
    <!-- Left Column: Approval Lifecycle Events -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold" style="letter-spacing: -0.01em;"><i class="bi bi-clock-history text-primary me-2"></i> Document Lifecycle Transitions</h5>
            </div>
            <div class="card-body">
                @if($admission->approvals->isEmpty())
                    <p class="text-muted text-center py-5">No approval transitions recorded yet.</p>
                @else
                    <div class="timeline">
                        @foreach($admission->approvals as $approval)
                        <div class="border-start border-2 border-dark ps-3 pb-3 mb-2" style="border-color: #e2e8f0 !important;">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-secondary text-uppercase">{{ str_replace('_', ' ', $approval->action) }}</span>
                                <span class="text-muted small">{{ $approval->created_at?->format('d M Y, h:i A') }}</span>
                            </div>
                            <div class="mt-1 small">
                                <strong>{{ $approval->performer?->name ?? 'Staff User' }}</strong>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size:0.65rem;">{{ $approval->user_role }}</span>
                            </div>
                            @if($approval->notes_or_correction_reason)
                            <div class="alert alert-light border small mt-2 py-1 px-2 mb-0">
                                <strong>Note:</strong> {{ $approval->notes_or_correction_reason }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Historical Revisions -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold" style="letter-spacing: -0.01em;"><i class="bi bi-file-earmark-lock text-success me-2"></i> Immutable Approved Revisions</h5>
            </div>
            <div class="card-body">
                @if($admission->revisions->isEmpty())
                    <p class="text-muted text-center py-5">No archived revisions exist yet. Revisions are created when approved documents are modified or officially issued.</p>
                @else
                    @foreach($admission->revisions as $rev)
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Revision #{{ $rev->revision_number }}</span>
                            <span class="text-muted small">{{ $rev->created_at?->format('d/m/Y h:i A') }}</span>
                        </div>
                        <div class="card-body p-3 small">
                            <div><strong>Approved By:</strong> {{ $rev->approver?->name ?? 'Doctor' }}</div>
                            <div><strong>Approved At:</strong> {{ $rev->approved_at?->format('d/m/Y h:i A') }}</div>
                            @if($rev->pdf_storage_path)
                            <div class="text-muted mt-1">
                                <i class="bi bi-file-earmark-check text-success"></i> PDF Snapshot Archived in Private Storage
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
