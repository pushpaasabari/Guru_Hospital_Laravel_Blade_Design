@extends('layouts.app')
@section('title', 'Review Document Pack — ' . $admission->patient_name . ' (' . $admission->ip_no . ')')

@section('content')
<!-- Control & Action Bar (Hidden on Print) -->
<div class="card mb-4 no-print shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h3 class="h4 fw-bold mb-0 text-dark" style="letter-spacing: -0.02em;">{{ $admission->patient_name }}</h3>
                    <span class="badge bg-light text-dark border">IP: {{ $admission->ip_no }}</span>
                    <span class="badge bg-light text-muted border">UHID: {{ $admission->uhid }}</span>
                    @php
                        $badgeClass = match($admission->status) {
                            'draft' => 'badge-draft',
                            'ready_for_review' => 'badge-ready_for_review',
                            'returned_for_correction' => 'badge-returned_for_correction',
                            'approved' => 'badge-approved',
                            'generated' => 'badge-generated',
                            default => 'badge-superseded'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} text-uppercase">{{ str_replace('_', ' ', $admission->status) }}</span>
                </div>
                <div class="text-muted small">
                    Format: <strong class="text-uppercase text-dark">{{ $admission->format }}</strong> &bull;
                    Workflow: <strong class="text-dark">{{ $admission->isDelivery() ? 'Delivery / Obstetric (' . $admission->newborns->count() . ' baby)' : 'Other Surgery' }}</strong> &bull;
                    Consultant: <strong class="text-dark">{{ $admission->consultant ?? 'Not recorded' }}</strong>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @if($admission->canBeEdited() && auth()->user()->canEditAdmissions())
                    <a href="{{ route('admissions.edit', $admission) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i> Edit Data
                    </a>
                @endif

                @if($admission->isDraft() || $admission->isReturned())
                    <form method="POST" action="{{ route('documents.submit-review', $admission) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info text-white fw-semibold">
                            <i class="bi bi-send-check me-1"></i> Submit for Review
                        </button>
                    </form>
                @endif

                @if($admission->isReadyForReview() && auth()->user()->canApproveDocuments())
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#returnModal">
                        <i class="bi bi-arrow-return-left me-1"></i> Return for Correction
                    </button>
                    <form method="POST" action="{{ route('documents.approve', $admission) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Clinically Approve Pack
                        </button>
                    </form>
                @endif

                @if(in_array($admission->status, ['approved', 'generated']))
                    <a href="{{ route('documents.pdf', $admission) }}" target="_blank" class="btn btn-primary fw-semibold">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Download Official PDF Pack
                    </a>

                    @if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#revisionModal">
                            <i class="bi bi-pencil-square me-1"></i> Create Revision
                        </button>
                    @endif
                @endif

                <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>

        @if($admission->isReturned() && $admission->rejection_reason)
            <div class="alert alert-danger mt-3 mb-0 py-2">
                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Correction Reason:</strong> {{ $admission->rejection_reason }}
            </div>
        @elseif($admission->isReadyForReview())
            <div class="alert alert-info mt-3 mb-0 py-2">
                <i class="bi bi-info-circle-fill me-1"></i> This document pack has been submitted for clinical review. Authorized clinicians can approve or return it for correction.
            </div>
        @elseif($admission->isDraft())
            <div class="alert alert-warning mt-3 mb-0 py-2">
                <i class="bi bi-shield-exclamation me-1"></i> <strong>Draft Notice:</strong> Review the three documents below. Clinical approval is required before official generation.
            </div>
        @elseif($admission->isApproved())
            <div class="alert alert-success mt-3 mb-0 py-2">
                <i class="bi bi-shield-check me-1"></i> <strong>Approved:</strong> Clinically approved by Dr. {{ $admission->approver?->name ?? 'Staff Doctor' }} on {{ $admission->approved_at?->format('d M Y, H:i') }}.
            </div>
        @endif
    </div>
</div>

<!-- Document 1: Discharge Summary -->
<div class="document-sheet mb-4">
    <div class="text-end text-muted small mb-2 no-print" style="font-size:10.5px; letter-spacing:0.05em; font-weight:600;">DOCUMENT 1 OF 3: DISCHARGE SUMMARY ({{ strtoupper($admission->format) }})</div>
    @php
        $formatView = 'documents.discharge-summaries.' . $admission->format . '.' . ($admission->surgery_type === 'delivery' ? 'delivery' : 'other');
    @endphp
    @if(view()->exists($formatView))
        @include($formatView, ['admission' => $admission])
    @else
        @include('documents.discharge-summaries.general.' . ($admission->surgery_type === 'delivery' ? 'delivery' : 'other'), ['admission' => $admission])
    @endif
</div>

<!-- Document 2: Operation Theatre Notes -->
<div class="document-sheet mb-4 page-break">
    <div class="text-end text-muted small mb-2 no-print" style="font-size:10.5px; letter-spacing:0.05em; font-weight:600;">DOCUMENT 2 OF 3: OPERATION THEATRE NOTES</div>
    @include('documents.operation-theatre.notes', ['admission' => $admission])
</div>

<!-- Document 3: Medical Records Department (MRD) Form -->
<div class="document-sheet mb-4 page-break">
    <div class="text-end text-muted small mb-2 no-print" style="font-size:10.5px; letter-spacing:0.05em; font-weight:600;">DOCUMENT 3 OF 3: MEDICAL RECORDS DEPARTMENT (MRD) FORM</div>
    @include('documents.mrd.form', ['admission' => $admission])
</div>

<!-- Return for Correction Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('documents.return', $admission) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="letter-spacing: -0.01em;">Return Pack for Clinical Correction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Please explain the required corrections or missing details. The record will return to draft status for editing.</p>
                <div class="mb-3">
                    <label class="form-label required">Correction Reason / Discrepancy Note</label>
                    <textarea name="correction_reason" class="form-control" rows="4" placeholder="e.g. Newborn weight mismatch; clarify anesthesia type; update medication orders." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm & Return</button>
            </div>
        </form>
    </div>
</div>

<!-- Create Revision Modal -->
<div class="modal fade" id="revisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admissions.revision', $admission) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="letter-spacing: -0.01em;">Create New Clinical Revision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">This admission is currently approved/generated. Creating a new revision will preserve an immutable historical snapshot and reset the admission to draft for correction.</p>
                <div class="mb-3">
                    <label class="form-label required">Reason for Revision</label>
                    <textarea name="revision_reason" class="form-control" rows="3" placeholder="e.g. Retrospective pathology findings added; dosage adjustment." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Revision & Unlock</button>
            </div>
        </form>
    </div>
</div>
@endsection
