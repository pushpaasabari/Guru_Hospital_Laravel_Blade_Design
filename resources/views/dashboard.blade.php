@extends('layouts.app')
@section('title', 'Clinical Workflow Dashboard — Guru Hospital')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">Clinical Document Workflow Dashboard</h1>
        <p class="text-muted mb-0">Overview of inpatient discharge summaries, OT records, and MRD archives</p>
    </div>
    @if(auth()->user()->canEditAdmissions())
    <a href="{{ route('admissions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> New Admission / Document Pack
    </a>
    @endif
</div>

<!-- Workflow KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="fg-kpi-card">
            <div class="fg-kpi-top">
                <span class="fg-kpi-label">Drafts</span>
                <span class="fg-kpi-dot" style="background: #94a3b8;"></span>
            </div>
            <div class="fg-kpi-number text-secondary">{{ $counts['draft'] }}</div>
            <p class="fg-kpi-desc">In progress</p>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="fg-kpi-card">
            <div class="fg-kpi-top">
                <span class="fg-kpi-label">Review</span>
                <span class="fg-kpi-dot" style="background: #0284c7;"></span>
            </div>
            <div class="fg-kpi-number text-info">{{ $counts['ready_for_review'] }}</div>
            <p class="fg-kpi-desc">Awaiting doctor</p>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="fg-kpi-card">
            <div class="fg-kpi-top">
                <span class="fg-kpi-label">Returned</span>
                <span class="fg-kpi-dot" style="background: #dc2626;"></span>
            </div>
            <div class="fg-kpi-number text-danger">{{ $counts['returned_for_correction'] }}</div>
            <p class="fg-kpi-desc">Needs correction</p>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="fg-kpi-card">
            <div class="fg-kpi-top">
                <span class="fg-kpi-label">Approved</span>
                <span class="fg-kpi-dot" style="background: #059669;"></span>
            </div>
            <div class="fg-kpi-number text-success">{{ $counts['approved'] }}</div>
            <p class="fg-kpi-desc">Clinically signed</p>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="fg-kpi-card">
            <div class="fg-kpi-top">
                <span class="fg-kpi-label">Generated</span>
                <span class="fg-kpi-dot" style="background: #7c3aed;"></span>
            </div>
            <div class="fg-kpi-number text-primary">{{ $counts['generated'] }}</div>
            <p class="fg-kpi-desc">Official PDF pack</p>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="fg-kpi-card">
            <div class="fg-kpi-top">
                <span class="fg-kpi-label">Total Cases</span>
                <span class="fg-kpi-dot" style="background: #080808;"></span>
            </div>
            <div class="fg-kpi-number text-dark">{{ $counts['total'] }}</div>
            <p class="fg-kpi-desc">All admissions</p>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4 border-0">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search Patient, UHID, IP..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="draft" @selected(request('status')==='draft')>Draft</option>
                    <option value="ready_for_review" @selected(request('status')==='ready_for_review')>Ready for Review</option>
                    <option value="returned_for_correction" @selected(request('status')==='returned_for_correction')>Returned</option>
                    <option value="approved" @selected(request('status')==='approved')>Approved</option>
                    <option value="generated" @selected(request('status')==='generated')>Generated</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="format" class="form-select form-select-sm">
                    <option value="">All Formats</option>
                    <option value="general" @selected(request('format')==='general')>General</option>
                    <option value="cmchistn" @selected(request('format')==='cmchistn')>CMCHISTN</option>
                    <option value="private" @selected(request('format')==='private')>Private Insurance</option>
                    <option value="employee" @selected(request('format')==='employee')>Employee</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="surgery_type" class="form-select form-select-sm">
                    <option value="">All Workflows</option>
                    <option value="delivery" @selected(request('surgery_type')==='delivery')>Delivery / Obstetric</option>
                    <option value="other_surgery" @selected(request('surgery_type')==='other_surgery')>Other Surgery</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-filter"></i> Apply Filters
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Admissions Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold" style="letter-spacing: -0.01em;">Recent Inpatient Admissions</h5>
        <span class="text-muted small">Showing {{ $admissions->count() }} of {{ $admissions->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Patient Name</th>
                    <th>Identifiers</th>
                    <th>Workflow & Format</th>
                    <th>Admission / Discharge</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admissions as $admission)
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $admission->patient_name }}</div>
                        <div class="text-muted small">{{ $admission->age ? $admission->age . ' yrs' : 'Age N/A' }} &bull; {{ $admission->sex ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="small"><span class="text-muted">UHID:</span> <span class="fw-semibold">{{ $admission->uhid }}</span></div>
                        <div class="small"><span class="text-muted">IP No:</span> <span class="badge bg-light text-dark border">{{ $admission->ip_no }}</span></div>
                    </td>
                    <td>
                        <div>
                            @if($admission->isDelivery())
                                <span class="badge" style="background:#fdf2f8; color:#be185d; border:1px solid #fbcfe8;">
                                    <i class="bi bi-gender-female"></i> Delivery ({{ $admission->newborns->count() }} baby)
                                </span>
                            @else
                                <span class="badge" style="background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd;">
                                    <i class="bi bi-heart-pulse"></i> Surgery
                                </span>
                            @endif
                        </div>
                        <span class="badge bg-secondary text-uppercase mt-1" style="font-size:0.65rem;">{{ $admission->format }}</span>
                    </td>
                    <td>
                        <div class="small text-muted">{{ $admission->admission_at?->format('d M Y, H:i') }}</div>
                        <div class="small text-muted">to {{ $admission->discharge_at?->format('d M Y, H:i') }}</div>
                    </td>
                    <td>
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
                        <span class="badge {{ $badgeClass }} text-uppercase">
                            {{ str_replace('_', ' ', $admission->status) }}
                        </span>
                        @if($admission->isReturned() && $admission->rejection_reason)
                            <div class="text-danger small mt-1" style="max-width: 200px; font-size: 0.75rem;" title="{{ $admission->rejection_reason }}">
                                <i class="bi bi-info-circle"></i> {{ Str::limit($admission->rejection_reason, 40) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('documents.preview', $admission) }}" class="btn btn-outline-secondary" title="Review Document Pack">
                                <i class="bi bi-eye"></i> Preview
                            </a>
                            @if($admission->canBeEdited() && auth()->user()->canEditAdmissions())
                            <a href="{{ route('admissions.edit', $admission) }}" class="btn btn-outline-secondary" title="Edit Clinical Details">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            <a href="{{ route('documents.history', $admission) }}" class="btn btn-outline-secondary" title="Audit & Revision History">
                                <i class="bi bi-clock-history"></i>
                            </a>
                            @if(in_array($admission->status, ['approved', 'generated']) && auth()->user()->canGeneratePdfs())
                            <a href="{{ route('documents.pdf', $admission) }}" target="_blank" class="btn btn-primary" title="Download Official PDF">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                        No admissions found matching the selected criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admissions->hasPages())
    <div class="card-footer bg-white py-3 border-top">
        {{ $admissions->links() }}
    </div>
    @endif
</div>
@endsection
