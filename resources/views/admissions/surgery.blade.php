@extends('layouts.app')
@section('title', "Stage 2: Labour / Surgery — {$admission->patient_name} — Guru Hospital")
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
                    <a href="{{ route('admissions.surgery', $admission) }}" class="d-block p-2 rounded bg-primary text-white fw-bold text-decoration-none">
                        2. Labour / LSCS / Surgery (Current)
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admissions.test-reports', $admission) }}" class="d-block p-2 rounded bg-light text-dark border text-decoration-none hover-bg">
                        3. Test Reports
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
            <h1 class="h3 mb-1 fw-bold text-dark">Stage 2: Labour / LSCS / Surgery Notes</h1>
            <p class="text-muted mb-0">Record Operation Theatre notes, surgical team members, procedure details, and newborn birth records.</p>
        </div>
        <a href="{{ route('admissions.edit', $admission) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Stage 1: Admission
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admissions.surgery.update', $admission) }}" id="surgeryForm">
@csrf
@method('PUT')

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">2.1</span> Operation Theatre &amp; Procedure Schedule
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Procedure / Delivery Date &amp; Time</label>
                <input class="form-control" name="procedure_at" type="datetime-local" value="{{ old('procedure_at', optional($admission->procedure_at)->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="col-md-5">
                <label class="form-label">Procedure / Surgery Name</label>
                <input class="form-control" name="procedure_name" value="{{ old('procedure_name', $admission->procedure_name) }}" placeholder="e.g. Emergency Lower Segment Caesarean Section (LSCS)">
            </div>
            <div class="col-md-3">
                <label class="form-label">ICD-10 Code</label>
                <input class="form-control" name="icd_code" value="{{ old('icd_code', $admission->icd_code) }}" placeholder="e.g. O82.0">
            </div>
            <div class="col-12">
                <label class="form-label">Procedure Done / Operation Summary</label>
                <input class="form-control" name="procedure_done" value="{{ old('procedure_done', $admission->procedure_done) }}" placeholder="e.g. Emergency LSCS with Pfannenstiel Incision">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">2.2</span> Surgical &amp; Anaesthesia Team
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Surgeon</label>
                <input class="form-control" name="surgeon" value="{{ old('surgeon', $admission->surgeon) }}" placeholder="e.g. Dr. Ambika MS OG">
            </div>
            <div class="col-md-4">
                <label class="form-label">Assisted Surgeon</label>
                <input class="form-control" name="assisted_surgeon" value="{{ old('assisted_surgeon', $admission->assisted_surgeon) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Anaesthetist</label>
                <input class="form-control" name="anaesthetist" value="{{ old('anaesthetist', $admission->anaesthetist) }}" placeholder="e.g. Dr. M.S Karthick (SA)">
            </div>
            <div class="col-md-4">
                <label class="form-label">Assisted Anaesthetist</label>
                <input class="form-control" name="assisted_anaesthetist" value="{{ old('assisted_anaesthetist', $admission->assisted_anaesthetist) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Anaesthesia Type</label>
                <input class="form-control" name="anaesthesia_type" value="{{ old('anaesthesia_type', $admission->anaesthesia_type) }}" placeholder="e.g. Spinal Anaesthesia">
            </div>
            <div class="col-md-4">
                <label class="form-label">Staff Nurse</label>
                <input class="form-control" name="staff_nurse" value="{{ old('staff_nurse', $admission->staff_nurse) }}" placeholder="e.g. Staff Nurse Anitha">
            </div>
            <div class="col-md-4">
                <label class="form-label">OT Technician</label>
                <input class="form-control" name="ot_technician" value="{{ old('ot_technician', $admission->ot_technician) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Pediatrician</label>
                <input class="form-control" name="pediatrician_name" value="{{ old('pediatrician_name', $admission->pediatrician_name) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Assistant / Other Clinical Help</label>
                <input class="form-control" name="assisted_by" value="{{ old('assisted_by', $admission->assisted_by) }}">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">2.3</span> Operative Narrative &amp; Clinical Findings
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Detailed Operative Procedure Note</label>
                <textarea class="form-control" name="procedure_note" rows="4">{{ old('procedure_note', $admission->procedure_note) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Incision</label>
                <input class="form-control" name="incision" value="{{ old('incision', $admission->incision) }}" placeholder="e.g. Pfannenstiel incision">
            </div>
            <div class="col-md-6">
                <label class="form-label">Operative Findings</label>
                <input class="form-control" name="findings" value="{{ old('findings', $admission->findings) }}" placeholder="e.g. Live infant delivered, placenta complete">
            </div>
            <div class="col-md-3">
                <label class="form-label">Blood Loss (ml)</label>
                <input class="form-control" name="blood_loss_ml" type="number" step="0.1" value="{{ old('blood_loss_ml', $admission->blood_loss_ml) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Complications</label>
                <input class="form-control" name="complications" value="{{ old('complications', $admission->complications) }}" placeholder="e.g. Nil">
            </div>
            <div class="col-md-3">
                <label class="form-label">Specimen Sent</label>
                <input class="form-control" name="specimen" value="{{ old('specimen', $admission->specimen) }}" placeholder="e.g. Placenta in formalin">
            </div>
            <div class="col-md-3">
                <label class="form-label">Drain Placed</label>
                <input class="form-control" name="drain" value="{{ old('drain', $admission->drain) }}" placeholder="e.g. Nil">
            </div>
            <div class="col-12">
                <label class="form-label">Immediate Postoperative Orders</label>
                <textarea class="form-control" name="postoperative_orders" rows="2">{{ old('postoperative_orders', $admission->postoperative_orders) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-5">
    <a href="{{ route('admissions.edit', $admission) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Stage 1: Admission
    </a>
    <div class="d-flex gap-2">
        <button type="submit" name="next_step" value="stay" class="btn btn-outline-primary px-3 py-2 fw-semibold">
            Save Stage 2 Changes
        </button>
        <button type="submit" name="next_step" value="test-reports" class="btn btn-primary px-4 py-2 fw-semibold">
            Save &amp; Next: Stage 3 (Test Reports) <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </div>
</div>
</form>
@endsection
