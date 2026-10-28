@extends('layouts.app')
@section('title', 'Clinical Workflow Dashboard — Guru Hospital')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark">Clinical Document Workflow Dashboard</h1>
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
        <div class="card h-100 border-start border-4 border-secondary">
            <div class="card-body p-3">
                <span class="text-muted small fw-semibold text-uppercase">Drafts</span>
                <h3 class="fw-bold mb-0 mt-1 text-secondary">{{ $counts['draft'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card h-100 border-start border-4 border-info">
            <div class="card-body p-3">
                <span class="text-muted small fw-semibold text-uppercase">Ready for Review</span>
                <h3 class="fw-bold mb-0 mt-1 text-info">{{ $counts['ready_for_review'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card h-100 border-start border-4 border-danger">
            <div class="card-body p-3">
                <span class="text-muted small fw-semibold text-uppercase">Returned</span>
                <h3 class="fw-bold mb-0 mt-1 text-danger">{{ $counts['returned_for_correction'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card h-100 border-start border-4 border-success">
            <div class="card-body p-3">
                <span class="text-muted small fw-semibold text-uppercase">Approved</span>
                <h3 class="fw-bold mb-0 mt-1 text-success">{{ $counts['approved'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card h-100 border-start border-4 border-primary">
            <div class="card-body p-3">
                <span class="text-muted small fw-semibold text-uppercase">Generated</span>
                <h3 class="fw-bold mb-0 mt-1 text-primary">{{ $counts['generated'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card h-100 border-start border-4 border-dark">
            <div class="card-body p-3">
                <span class="text-muted small fw-semibold text-uppercase">Total Cases</span>
                <h3 class="fw-bold mb-0 mt-1 text-dark">{{ $counts['total'] }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('dashboard') }}" class="row g-2">
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
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">Recent Inpatient Admissions</h5>
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
                                <span class="badge bg-pink text-danger bg-opacity-10 border border-danger small">
                                    <i class="bi bi-gender-female"></i> Delivery ({{ $admission->newborns->count() }} baby)
                                </span>
                            @else
                                <span class="badge bg-info text-primary bg-opacity-10 border border-primary small">
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
                                'draft' => 'bg-secondary',
                                'ready_for_review' => 'bg-info text-dark',
                                'returned_for_correction' => 'bg-danger',
                                'approved' => 'bg-success',
                                'generated' => 'bg-primary',
                                default => 'bg-warning text-dark'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} text-uppercase" style="font-size:0.7rem;">
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
                            <a href="{{ route('documents.preview', $admission) }}" class="btn btn-outline-primary" title="Review Document Pack">
                                <i class="bi bi-eye"></i> Preview
                            </a>
                            @if($admission->canBeEdited() && auth()->user()->canEditAdmissions())
                            <a href="{{ route('admissions.edit', $admission) }}" class="btn btn-outline-secondary" title="Edit Clinical Details">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            <a href="{{ route('documents.history', $admission) }}" class="btn btn-outline-info" title="Audit & Revision History">
                                <i class="bi bi-clock-history"></i>
                            </a>
                            @if(in_array($admission->status, ['approved', 'generated']) && auth()->user()->canGeneratePdfs())
                            <a href="{{ route('documents.pdf', $admission) }}" target="_blank" class="btn btn-success" title="Download Official PDF">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                        No admissions found matching the selected criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admissions->hasPages())
    <div class="card-footer bg-white py-2">
        {{ $admissions->links() }}
    </div>
    @endif
</div>
@endsection

