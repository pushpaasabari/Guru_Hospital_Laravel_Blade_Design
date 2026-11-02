@extends('layouts.app')
@section('title', "Stage 4: Discharge Summary — {$admission->patient_name} — Guru Hospital")
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
                    <a href="{{ route('admissions.test-reports', $admission) }}" class="d-block p-2 rounded bg-light text-dark border text-decoration-none hover-bg">
                        3. Test Reports
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admissions.discharge', $admission) }}" class="d-block p-2 rounded bg-primary text-white fw-bold text-decoration-none">
                        4. Discharge Summary (Current)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">Stage 4: Discharge Summary &amp; MRD Records</h1>
            <p class="text-muted mb-0">Finalize discharge timing, condition at discharge, clinical history, medications, advice, and MRD signoffs.</p>
        </div>
        <a href="{{ route('admissions.test-reports', $admission) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Stage 3: Test Reports
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admissions.discharge.update', $admission) }}" id="dischargeForm">
@csrf
@method('PUT')

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">4.1</span> Discharge Details &amp; Patient Condition
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label required">Discharge Date &amp; Time</label>
                <input class="form-control" name="discharge_at" type="datetime-local" value="{{ old('discharge_at', optional($admission->discharge_at)->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Condition at Discharge</label>
                <input class="form-control" name="discharge_condition" value="{{ old('discharge_condition', $admission->discharge_condition) }}" placeholder="e.g. Patient is hemodynamically stable, afebrile, surgical wound healthy and dry.">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">4.2</span> Clinical History &amp; Course in Hospital
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Presenting Complaints / Clinical History</label>
                <textarea class="form-control" name="clinical_history" rows="4">{{ old('clinical_history', $admission->clinical_history) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Clinical Examination</label>
                <textarea class="form-control" name="examination" rows="4">{{ old('examination', $admission->examination) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Course in Hospital &amp; Treatment Given</label>
                <textarea class="form-control" name="treatment_course" rows="4">{{ old('treatment_course', $admission->treatment_course) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">4.3</span> Medication Orders &amp; Discharge Advice
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Discharge Medication Orders</label>
                <textarea class="form-control font-monospace" name="medication_orders" rows="4">{{ old('medication_orders', $admission->medication_orders) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Discharge Advice &amp; Instructions</label>
                <textarea class="form-control" name="discharge_advice" rows="3">{{ old('discharge_advice', $admission->discharge_advice) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Follow-up Advice &amp; Review Date</label>
                <textarea class="form-control" name="follow_up" rows="3">{{ old('follow_up', $admission->follow_up) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">4.4</span> Medical Record Department (MRD) &amp; Ward Handover
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">MRD File Number <span class="badge bg-light text-muted border ms-1" style="font-size: 0.68rem;">Auto: MRD-{UHID}</span></label>
                <input class="form-control" name="mrd_no" value="{{ old('mrd_no', $admission->mrd_no ?: ($admission->uhid ? (str_starts_with($admission->uhid, 'MRD-') ? $admission->uhid : 'MRD-' . $admission->uhid) : '')) }}" placeholder="e.g. MRD-{UHID}">
                <div class="form-text text-muted small">Default format: <span class="font-monospace">MRD-{UHID}</span></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment / Insurance Scheme</label>
                <input class="form-control" name="payment_mode" value="{{ old('payment_mode', $admission->payment_mode) }}" placeholder="e.g. CMCHISTN / Cash">
            </div>
            <div class="col-md-4">
                <label class="form-label">Handed Over By (Ward Nurse)</label>
                <input class="form-control" name="handover_name" value="{{ old('handover_name', $admission->handover_name) }}" placeholder="e.g. Staff Nurse Anitha">
            </div>
            <div class="col-md-6">
                <label class="form-label">Floor In-Charge Sister</label>
                <input class="form-control" name="floor_incharge_name" value="{{ old('floor_incharge_name', $admission->floor_incharge_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">MRD In-Charge</label>
                <input class="form-control" name="mrd_incharge_name" value="{{ old('mrd_incharge_name', $admission->mrd_incharge_name) }}">
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-5">
    <a href="{{ route('admissions.test-reports', $admission) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Stage 3: Test Reports
    </a>
    <div class="d-flex gap-2">
        <button type="submit" name="next_step" value="stay" class="btn btn-outline-primary px-3 py-2 fw-semibold">
            Save Stage 4 Changes
        </button>
        <button type="submit" name="next_step" value="preview" class="btn btn-success px-4 py-2 fw-bold shadow-sm">
            Save &amp; Preview Document Pack <i class="bi bi-file-earmark-check ms-1"></i>
        </button>
    </div>
</div>
</form>
@endsection

