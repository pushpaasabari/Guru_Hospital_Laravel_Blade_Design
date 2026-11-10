@extends('layouts.app')
@section('title', 'Stage 1: Patient Admission — Guru Hospital')
@section('content')
<div class="mb-4">
    <!-- Stage Workflow Navigation -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body p-2">
            <div class="row g-2 text-center">
                <div class="col-md-3">
                    <div class="p-2 rounded bg-primary text-white fw-bold">
                        1. Admission (Current)
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded bg-light text-muted border">
                        2. Labour / LSCS / Surgery
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded bg-light text-muted border">
                        3. Test Reports
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded bg-light text-muted border">
                        4. Discharge Summary
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">Stage 1: Patient Admission &amp; Case Setup</h1>
            <p class="text-muted mb-0">Enter patient demographics, admission details, format, and provisional diagnosis.</p>
        </div>
        <a href="{{ route('admissions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Archive
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admissions.store') }}" id="admissionForm">
@csrf

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">1.1</span> Patient Demographics
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label required">Patient Full Name</label>
                <input class="form-control" name="patient_name" type="text" value="{{ old('patient_name') }}" required placeholder="e.g. Mrs. Anitha">
            </div>
            <div class="col-md-4">
                <label class="form-label required">UHID / MRN</label>
                <input class="form-control" name="uhid" type="text" value="{{ old('uhid') }}" required placeholder="e.g. 2023/3931">
            </div>
            <div class="col-md-4">
                <label class="form-label required">IP No.</label>
                <input class="form-control" name="ip_no" type="text" value="{{ old('ip_no') }}" required placeholder="e.g. 2026/567">
            </div>

            <div class="col-md-2">
                <label class="form-label">Age</label>
                <input class="form-control" name="age" type="text" value="{{ old('age') }}" placeholder="e.g. 23">
            </div>
            <div class="col-md-2">
                <label class="form-label required">Sex</label>
                <select name="sex" class="form-select" id="patientSex" required>
                    <option value="">Select</option>
                    @foreach(['Female','Male','Other','Not recorded'] as $v)
                        <option value="{{ $v }}" @selected(old('sex')===$v)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact / Phone</label>
                <input class="form-control" name="contact" type="text" value="{{ old('contact') }}" placeholder="e.g. 7373667465">
            </div>
            <div class="col-md-4">
                <label class="form-label required">Surgery / Clinical Workflow</label>
                <select name="surgery_type" id="surgeryType" class="form-select" required>
                    <option value="">Select</option>
                    <option value="delivery" id="deliveryOption" @selected(old('surgery_type')==='delivery')>Delivery / Obstetric</option>
                    <option value="other" @selected(old('surgery_type')==='other')>Other Surgery / Non-Obstetric</option>
                    <option value="medical" @selected(old('surgery_type')==='medical')>Medical Case (Non-Surgical)</option>
                </select>
                <div id="sexWarning" class="form-text text-muted small" style="display:none;">
                    <i class="bi bi-info-circle me-1"></i> Delivery / Obstetric is only for <strong>Female</strong> patients.
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label required">Address Line 1</label>
                <input class="form-control" name="address_line1" type="text" value="{{ old('address_line1', old('address')) }}" required placeholder="e.g. Door No. / Street / Building">
            </div>
            <div class="col-md-4">
                <label class="form-label">Address Line 2</label>
                <input class="form-control" name="address_line2" type="text" value="{{ old('address_line2') }}" placeholder="e.g. Area / Locality / Village">
            </div>
            <div class="col-md-4">
                <label class="form-label">Address Line 3</label>
                <input class="form-control" name="address_line3" type="text" value="{{ old('address_line3') }}" placeholder="e.g. City / Taluk / Pincode">
            </div>

            <div class="col-md-4" id="picmeFieldWrapper" style="display: none;">
                <label class="form-label required">
                    PICME Number (RCH ID)
                    <span class="badge bg-danger-subtle text-danger ms-1" style="font-size: 0.68rem;">DELIVERY / OBSTETRIC</span>
                </label>
                <input class="form-control" name="picme_no" id="picmeInput" type="text" value="{{ old('picme_no') }}" placeholder="e.g. 331024567890 (12-digit PICME / RCH ID)">
                <div class="form-text text-muted small">Mandatory for all delivery/obstetric patients in Tamil Nadu.</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">1.2</span> Admission &amp; Ward Assignment
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label required">Admission Date &amp; Time</label>
                <input class="form-control" name="admission_at" type="datetime-local" value="{{ old('admission_at') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Consultant Doctor</label>
                <input class="form-control" name="consultant" type="text" value="{{ old('consultant') }}" placeholder="e.g. Dr. Ambika MS OG">
            </div>
            <div class="col-md-4">
                <label class="form-label">Department / Specialty</label>
                <input class="form-control" name="department" type="text" value="{{ old('department') }}" placeholder="e.g. Obstetrics & Gynaecology">
            </div>
            <div class="col-md-4">
                <label class="form-label">Ward / Bed Number</label>
                <input class="form-control" name="ward_room_no" type="text" value="{{ old('ward_room_no') }}" placeholder="e.g. Room 102">
            </div>
            <div class="col-md-4">
                <label class="form-label">MRD File Number <span class="badge bg-light text-muted border ms-1" style="font-size: 0.68rem;">Auto: MRD-{UHID}</span></label>
                <input class="form-control" name="mrd_no" id="mrdNoInput" type="text" value="{{ old('mrd_no') }}" placeholder="e.g. MRD-{UHID}">
                <div class="form-text text-muted small">Default format: <span class="font-monospace">MRD-{UHID}</span></div>
            </div>
            @include('admissions.partials.payment-insurance-fields', ['admission' => null, 'syncFormat' => true, 'uid' => 'create_pi'])
        </div>
    </div>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
        <span class="badge bg-primary me-2">1.3</span> Format &amp; Admission Diagnosis
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label required">Discharge Summary Format</label>
                <select name="format" class="form-select" required>
                    @foreach(['general'=>'General (Standard Cash / TPA)','cmchistn'=>'CMCHISTN Government Scheme','private'=>'Private Insurance','employee'=>'Hospital Employee'] as $v=>$label)
                        <option value="{{ $v }}" @selected(old('format','general')===$v)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label required">Provisional / Admission Diagnosis</label>
                <textarea name="diagnosis" class="form-control" rows="2" required placeholder="e.g. G3P1L1A1 / TWINS / Preterm / PROM">{{ old('diagnosis') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-5">
    <a href="{{ route('admissions.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
        Save &amp; Proceed to Stage 2: Labour / Surgery <i class="bi bi-arrow-right ms-1"></i>
    </button>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sexSelect = document.getElementById('patientSex');
    const surgerySelect = document.getElementById('surgeryType');
    const deliveryOpt = document.getElementById('deliveryOption');
    const warning = document.getElementById('sexWarning');

    const picmeWrapper = document.getElementById('picmeFieldWrapper');

    function syncSurgeryWorkflow() {
        const isFemale = (sexSelect.value || '').trim().toLowerCase() === 'female';
        if (isFemale) {
            deliveryOpt.removeAttribute('disabled');
            warning.style.display = 'none';
        } else {
            deliveryOpt.setAttribute('disabled', 'disabled');
            warning.style.display = 'block';
            if (surgerySelect.value === 'delivery') {
                surgerySelect.value = '';
            }
        }

        if (isFemale && surgerySelect.value === 'delivery') {
            picmeWrapper.style.display = 'block';
        } else {
            picmeWrapper.style.display = 'none';
        }
    }

    sexSelect.addEventListener('change', syncSurgeryWorkflow);
    surgerySelect.addEventListener('change', syncSurgeryWorkflow);
    syncSurgeryWorkflow();

    const uhidInput = document.querySelector('[name="uhid"]');
    const mrdInput = document.getElementById('mrdNoInput');
    if (uhidInput && mrdInput) {
        uhidInput.addEventListener('input', function() {
            if (!mrdInput.value || mrdInput.dataset.autoFilled === 'true') {
                mrdInput.value = uhidInput.value.trim() ? 'MRD-' + uhidInput.value.trim() : '';
                mrdInput.dataset.autoFilled = 'true';
            }
        });
        mrdInput.addEventListener('input', function() {
            mrdInput.dataset.autoFilled = 'false';
        });
    }
});
</script>
@endsection
