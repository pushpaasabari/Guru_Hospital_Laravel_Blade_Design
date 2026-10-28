@extends('layouts.app')
@section('title', 'Inpatient Admissions — Guru Hospital')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">Inpatient Admissions Archive</h1>
        <p class="text-muted mb-0">Search and manage patient admission records, discharge summaries, and MRD dossiers.</p>
    </div>
    @if(auth()->user()->canEditAdmissions())
    <a href="{{ route('admissions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> New Admission
    </a>
    @endif
</div>

<div class="card mb-4 border-0">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admissions.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search Patient, UHID, IP No..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="draft" @selected(request('status')==='draft')>Draft</option>
                    <option value="ready_for_review" @selected(request('status')==='ready_for_review')>Ready for Review</option>
                    <option value="returned_for_correction" @selected(request('status')==='returned_for_correction')>Returned for Correction</option>
                    <option value="approved" @selected(request('status')==='approved')>Approved</option>
                    <option value="generated" @selected(request('status')==='generated')>Generated</option>
                    <option value="superseded" @selected(request('status')==='superseded')>Superseded</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter Records</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admissions.index') }}" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Patient Name & Demographics</th>
                    <th>UHID & IP No</th>
                    <th>Workflow</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admissions as $admission)
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $admission->patient_name }}</div>
                        <div class="text-muted small">{{ $admission->age ? $admission->age . ' yrs' : 'Age N/A' }} / {{ $admission->sex ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="small"><span class="text-muted">UHID:</span> {{ $admission->uhid }}</div>
                        <div class="small fw-semibold text-primary"><span class="text-muted">IP:</span> {{ $admission->ip_no }}</div>
                    </td>
                    <td>
                        <span class="badge bg-secondary text-uppercase">{{ $admission->format }}</span>
                        <div class="small text-muted mt-1">{{ $admission->isDelivery() ? 'Delivery / Obstetric' : 'Other Surgery' }}</div>
                    </td>
                    <td>
                        <div class="small">Adm: {{ $admission->admission_at?->format('d/m/Y') }}</div>
                        <div class="small text-muted">Dis: {{ $admission->discharge_at?->format('d/m/Y') }}</div>
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
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('documents.preview', $admission) }}" class="btn btn-outline-secondary" title="Preview Pack">Preview</a>
                            @if($admission->canBeEdited() && auth()->user()->canEditAdmissions())
                            <a href="{{ route('admissions.edit', $admission) }}" class="btn btn-outline-secondary" title="Edit">Edit</a>
                            @endif
                            <a href="{{ route('documents.history', $admission) }}" class="btn btn-outline-secondary" title="History">History</a>
                            @if(in_array($admission->status, ['approved', 'generated']) && auth()->user()->canGeneratePdfs())
                            <a href="{{ route('documents.pdf', $admission) }}" target="_blank" class="btn btn-primary" title="PDF">PDF</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                        No records found matching the filter criteria.
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
