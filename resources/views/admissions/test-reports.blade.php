@extends('layouts.app')
@section('title', "Stage 3: Test Reports — {$admission->patient_name} — Guru Hospital")
@section('content')
<div class="mb-4">
    <!-- Patient Banner -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold mb-1 text-dark">{{ $admission->patient_name }}</h5>
                <div class="text-muted small">
                    <strong>UHID:</strong> {{ $admission->uhid }} &bull;
                    <strong>IP No:</strong> {{ $admission->ip_no }} &bull;
                    <span class="badge bg-secondary">{{ strtoupper($admission->format) }}</span>
                    <span class="badge bg-info text-dark">{{ strtoupper($admission->surgery_type) }}</span>
                </div>
            </div>
            <div>
                <a href="{{ route('documents.preview', $admission) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-file-earmark-text me-1"></i> Preview Pack
                </a>
            </div>
        </div>
    </div>

    <!-- Stage Workflow Navigation -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body p-2">
            <div class="row g-2 text-center">
                <div class="col-md-3">
                    <a href="{{ route('admissions.edit', $admission) }}" class="d-block p-2 rounded bg-light text-dark border text-decoration-none hover-bg">
                        1. Admission
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admissions.surgery', $admission) }}" class="d-block p-2 rounded bg-light text-dark border text-decoration-none hover-bg">
                        2. Labour / LSCS / Surgery
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admissions.test-reports', $admission) }}" class="d-block p-2 rounded bg-primary text-white fw-bold text-decoration-none">
                        3. Test Reports (Current)
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admissions.discharge', $admission) }}" class="d-block p-2 rounded bg-light text-dark border text-decoration-none hover-bg">
                        4. Discharge Summary
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">Stage 3: Laboratory &amp; Diagnostic Test Reports</h1>
            <p class="text-muted mb-0">Record clinical pathology, biochemistry, hematology, blood grouping, and diagnostic imaging reports.</p>
        </div>
        <a href="{{ route('admissions.surgery', $admission) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Stage 2: Surgery
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admissions.test-reports.update', $admission) }}" id="testReportsForm">
@csrf
@method('PUT')

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">3.1</span> Diagnostic Investigations &amp; Lab Results
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-semibold text-dark">Investigations (Hematology, Biochemistry, Urine, Scans, X-Ray, etc.)</label>
            <textarea
                class="form-control font-monospace"
                name="investigations"
                id="investigationsTextarea"
                rows="12"
                placeholder="Enter or paste lab reports, test values, and diagnostic impressions here..."
                style="font-size: 0.92rem; line-height: 1.6;"
            >{{ old('investigations', $admission->investigations) }}</textarea>
            <div class="form-text text-muted mt-2">
                <i class="bi bi-info-circle me-1"></i> These findings appear in the &ldquo;INVESTIGATIONS&rdquo; section of the patient&rsquo;s Discharge Summary.
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-5">
    <a href="{{ route('admissions.surgery', $admission) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Stage 2: Labour / Surgery
    </a>
    <div class="d-flex gap-2">
        <button type="submit" name="next_step" value="stay" class="btn btn-outline-primary px-3 py-2 fw-semibold">
            Save Stage 3 Changes
        </button>
        <button type="submit" name="next_step" value="discharge" class="btn btn-primary px-4 py-2 fw-semibold">
            Save &amp; Next: Stage 4 (Discharge) <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>
</form>
@endsection

