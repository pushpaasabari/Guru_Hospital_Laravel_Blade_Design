@extends('layouts.app')
@section('title', 'Edit Admission Draft — Guru Hospital')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Edit Admission / Document Pack</h1>
        <p class="text-muted mb-0">Updating admission records for <strong>{{ $admission->patient_name }}</strong> (IP: {{ $admission->ip_no }})</p>
    </div>
    <a href="{{ route('documents.preview', $admission) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Document Preview
    </a>
</div>

@if($admission->isReturned() && $admission->rejection_reason)
<div class="alert alert-danger shadow-sm mb-4">
    <div class="d-flex align-items-center mb-1">
        <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i>
        <h6 class="mb-0 fw-bold">Document Pack Returned for Clinical Correction</h6>
    </div>
    <p class="mb-0 ps-4">{{ $admission->rejection_reason }}</p>
</div>
@endif

<form method="POST" action="{{ route('admissions.update', $admission) }}" id="admissionEditForm">
    @csrf
    @method('PUT')

    <!-- Section 1: Demographics -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3">
            <span class="badge bg-primary me-2">1</span> Shared Patient & Admission Demographics
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label required">Patient Full Name</label>
                    <input type="text" name="patient_name" class="form-control" value="{{ old('patient_name', $admission->patient_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">UHID / MRN</label>
                    <input type="text" name="uhid" class="form-control" value="{{ old('uhid', $admission->uhid) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Inpatient No. (IP No)</label>
                    <input type="text" name="ip_no" class="form-control" value="{{ old('ip_no', $admission->ip_no) }}" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Age</label>
                    <input type="text" name="age" class="form-control" value="{{ old('age', $admission->age) }}" placeholder="e.g. 28">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sex</label>
                    <select name="sex" class="form-select">
                        <option value="">Select</option>
                        @foreach(['Female', 'Male', 'Other', 'Not recorded'] as $gender)
                            <option value="{{ $gender }}" @selected(old('sex', $admission->sex) === $gender)>{{ $gender }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact" class="form-control" value="{{ old('contact', $admission->contact) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $admission->address) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Admission Date & Time</label>
                    <input type="datetime-local" name="admission_at" class="form-control" value="{{ old('admission_at', $admission->admission_at?->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Procedure / Delivery Date & Time</label>
                    <input type="datetime-local" name="procedure_at" class="form-control" value="{{ old('procedure_at', $admission->procedure_at?->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Discharge Date & Time</label>
                    <input type="datetime-local" name="discharge_at" class="form-control" value="{{ old('discharge_at', $admission->discharge_at?->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Treating Consultant / Doctor</label>
                    <input type="text" name="consultant" class="form-control" value="{{ old('consultant', $admission->consultant) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department / Specialty</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $admission->department) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ward / Bed Number</label>
                    <input type="text" name="ward_room_no" class="form-control" value="{{ old('ward_room_no', $admission->ward_room_no) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Discharge Summary Format</label>
                    <select name="format" class="form-select" required>
                        <option value="general" @selected(old('format', $admission->format) === 'general')>General (Standard Cash / TPA)</option>
                        <option value="cmchistn" @selected(old('format', $admission->format) === 'cmchistn')>CMCHISTN (Tamil Nadu Govt Scheme)</option>
                        <option value="private" @selected(old('format', $admission->format) === 'private')>Private Insurance / Corporate</option>
                        <option value="employee" @selected(old('format', $admission->format) === 'employee')>Employee Healthcare</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Clinical Surgery Workflow</label>
                    <select name="surgery_type" id="surgeryType" class="form-select" required>
                        <option value="delivery" @selected(old('surgery_type', $admission->surgery_type) === 'delivery')>Delivery / Obstetric Workflow</option>
                        <option value="other" @selected(old('surgery_type', $admission->surgery_type) === 'other')>Other Surgery / Procedural Workflow</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label required">Final Diagnosis</label>
                    <textarea name="diagnosis" class="form-control" rows="2" required>{{ old('diagnosis', $admission->diagnosis) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Clinical Summary -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3">
            <span class="badge bg-primary me-2">2</span> Discharge Summary Clinical Details
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Presenting Complaints / History</label>
                    <textarea name="clinical_history" class="form-control" rows="3">{{ old('clinical_history', $admission->clinical_history) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Clinical Examination & Vital Signs</label>
                    <textarea name="examination" class="form-control" rows="3">{{ old('examination', $admission->examination) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Treatment & Hospital Course</label>
                    <textarea name="treatment_course" class="form-control" rows="3">{{ old('treatment_course', $admission->treatment_course) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Condition at Discharge</label>
                    <textarea name="discharge_condition" class="form-control" rows="3">{{ old('discharge_condition', $admission->discharge_condition) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Authorized Medication Orders</label>
                    <textarea name="medication_orders" class="form-control" rows="3">{{ old('medication_orders', $admission->medication_orders) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Discharge Advice & Warning Signs (Tamil / English)</label>
                    <textarea name="discharge_advice" class="form-control" rows="3">{{ old('discharge_advice', $admission->discharge_advice) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Investigations (Lab Reports, Pathology & Diagnostics)</label>
                    <textarea name="investigations" class="form-control" rows="3">{{ old('investigations', is_array($admission->investigations) ? json_encode($admission->investigations, JSON_PRETTY_PRINT) : $admission->investigations) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Follow-up Instructions</label>
                    <textarea name="follow_up" class="form-control" rows="2">{{ old('follow_up', $admission->follow_up) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Operation Theatre Notes -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3">
            <span class="badge bg-primary me-2">3</span> Operation Theatre (OT) Notes
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Procedure / Surgery Name</label>
                    <input type="text" name="procedure_name" class="form-control" value="{{ old('procedure_name', $admission->procedure_name) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Operating Surgeon</label>
                    <input type="text" name="surgeon" class="form-control" value="{{ old('surgeon', $admission->surgeon) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Consultant Pediatrician</label>
                    <input type="text" name="pediatrician_name" class="form-control" value="{{ old('pediatrician_name', $admission->pediatrician_name) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Anesthetist</label>
                    <input type="text" name="anaesthetist" class="form-control" value="{{ old('anaesthetist', $admission->anaesthetist) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Surgical Assistant</label>
                    <input type="text" name="assistant" class="form-control" value="{{ old('assistant', $admission->assistant) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Assisted By S/N</label>
                    <input type="text" name="assisted_by" class="form-control" value="{{ old('assisted_by', $admission->assisted_by) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type of Anesthesia</label>
                    <input type="text" name="anaesthesia_type" class="form-control" value="{{ old('anaesthesia_type', $admission->anaesthesia_type) }}" placeholder="e.g. Spinal, General, Local">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Blood Loss (mL)</label>
                    <input type="number" step="1" min="0" name="blood_loss_ml" class="form-control" value="{{ old('blood_loss_ml', $admission->blood_loss_ml) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Operative Findings</label>
                    <textarea name="findings" class="form-control" rows="3">{{ old('findings', $admission->findings) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Operative Procedure Steps</label>
                    <textarea name="procedure_note" class="form-control" rows="3">{{ old('procedure_note', $admission->procedure_note) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Incision Details</label>
                    <input type="text" name="incision" class="form-control" value="{{ old('incision', $admission->incision) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Specimen Sent for Biopsy</label>
                    <input type="text" name="specimen" class="form-control" value="{{ old('specimen', $admission->specimen) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Drain Details</label>
                    <input type="text" name="drain" class="form-control" value="{{ old('drain', $admission->drain) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Immediate Post-operative Orders</label>
                    <textarea name="postoperative_orders" class="form-control" rows="2">{{ old('postoperative_orders', $admission->postoperative_orders) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Dynamic Newborns (Delivery Workflow Only) -->
    <div class="card mb-4" id="deliveryFields">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-danger me-2">4</span> Birth & Newborn Details (Delivery Workflow)
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addBaby">
                <i class="bi bi-plus-lg"></i> Add Another Newborn (Twins/Triplets)
            </button>
        </div>
        <div class="card-body">
            <p class="small text-muted mb-3">Record each newborn independently. For multiple births, ensure each baby has a distinct entry.</p>

            <div id="newbornRows">
                @php
                    $existingBabies = old('newborns', $admission->newborns->toArray());
                    if (empty($existingBabies)) {
                        $existingBabies = [[]];
                    }
                @endphp

                @foreach($existingBabies as $i => $baby)
                <div class="border rounded p-3 mb-3 newborn-row bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-primary"><i class="bi bi-person-fill"></i> Newborn <span class="baby-number">{{ $i + 1 }}</span></strong>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-baby">Remove</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Sex</label>
                            <select class="form-select form-select-sm" name="newborns[{{ $i }}][sex]">
                                <option value="">Select</option>
                                @foreach(['Female', 'Male', 'Other', 'Not recorded'] as $s)
                                    <option value="{{ $s }}" @selected(($baby['sex'] ?? '') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Birth Date/Time</label>
                            <input class="form-control form-control-sm" type="datetime-local" name="newborns[{{ $i }}][birth_at]" value="{{ isset($baby['birth_at']) ? (is_string($baby['birth_at']) ? substr(str_replace(' ', 'T', $baby['birth_at']), 0, 16) : $baby['birth_at']?->format('Y-m-d\TH:i')) : '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Birth Weight (kg)</label>
                            <input class="form-control form-control-sm" type="number" step="0.01" min="0.1" max="8" name="newborns[{{ $i }}][birth_weight_kg]" value="{{ $baby['birth_weight_kg'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Apgar 1 min</label>
                            <input class="form-control form-control-sm" type="number" min="0" max="10" name="newborns[{{ $i }}][apgar_1_min]" value="{{ $baby['apgar_1_min'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Apgar 5 min</label>
                            <input class="form-control form-control-sm" type="number" min="0" max="10" name="newborns[{{ $i }}][apgar_5_min]" value="{{ $baby['apgar_5_min'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Neonatal Resuscitation / Care</label>
                            <textarea class="form-control form-control-sm" rows="2" name="newborns[{{ $i }}][neonatal_care]">{{ $baby['neonatal_care'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Birth Notes & Condition</label>
                            <textarea class="form-control form-control-sm" rows="2" name="newborns[{{ $i }}][notes]">{{ $baby['notes'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Section 5: MRD Fields -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3">
            <span class="badge bg-secondary me-2">5</span> Medical Records Department (MRD) Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">MRD Record File No.</label>
                    <input type="text" name="mrd_no" class="form-control" value="{{ old('mrd_no', $admission->mrd_no) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Mode / Scheme</label>
                    <input type="text" name="payment_mode" class="form-control" value="{{ old('payment_mode', $admission->payment_mode) }}" placeholder="e.g. CMCHISTN, Star Health, Cash">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ward Handover Nurse</label>
                    <input type="text" name="handover_name" class="form-control" value="{{ old('handover_name', $admission->handover_name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Floor In-Charge Signature Name</label>
                    <input type="text" name="floor_incharge_name" class="form-control" value="{{ old('floor_incharge_name', $admission->floor_incharge_name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">MRD In-Charge Name</label>
                    <input type="text" name="mrd_incharge_name" class="form-control" value="{{ old('mrd_incharge_name', $admission->mrd_incharge_name) }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-3 mb-5">
        <button type="submit" class="btn btn-primary btn-lg px-4">
            <i class="bi bi-save me-1"></i> Update & Review Document Pack
        </button>
        <a href="{{ route('documents.preview', $admission) }}" class="btn btn-outline-secondary btn-lg">
            Cancel
        </a>
    </div>
</form>

<template id="newbornTemplate">
    <div class="border rounded p-3 mb-3 newborn-row bg-light">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-primary"><i class="bi bi-person-fill"></i> Newborn <span class="baby-number"></span></strong>
            <button type="button" class="btn btn-sm btn-outline-danger remove-baby">Remove</button>
        </div>
        <div class="row g-2">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Sex</label>
                <select class="form-select form-select-sm" name="newborns[__INDEX__][sex]">
                    <option value="">Select</option>
                    <option>Female</option>
                    <option>Male</option>
                    <option>Other</option>
                    <option>Not recorded</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Birth Date/Time</label>
                <input class="form-control form-control-sm" type="datetime-local" name="newborns[__INDEX__][birth_at]">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Birth Weight (kg)</label>
                <input class="form-control form-control-sm" type="number" step="0.01" min="0.1" max="8" name="newborns[__INDEX__][birth_weight_kg]">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Apgar 1 min</label>
                <input class="form-control form-control-sm" type="number" min="0" max="10" name="newborns[__INDEX__][apgar_1_min]">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Apgar 5 min</label>
                <input class="form-control form-control-sm" type="number" min="0" max="10" name="newborns[__INDEX__][apgar_5_min]">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Neonatal Resuscitation / Care</label>
                <textarea class="form-control form-control-sm" rows="2" name="newborns[__INDEX__][neonatal_care]"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Birth Notes & Condition</label>
                <textarea class="form-control form-control-sm" rows="2" name="newborns[__INDEX__][notes]"></textarea>
            </div>
        </div>
    </div>
</template>

@section('scripts')
<script>
(() => {
    const type = document.getElementById('surgeryType');
    const fields = document.getElementById('deliveryFields');
    function toggleDelivery() {
        fields.style.display = (type.value === 'delivery') ? 'block' : 'none';
    }
    type.addEventListener('change', toggleDelivery);
    toggleDelivery();

    const list = document.getElementById('newbornRows');
    const tmpl = document.getElementById('newbornTemplate');
    function renumber() {
        [...list.querySelectorAll('.newborn-row')].forEach((r, i) => {
            r.querySelector('.baby-number').textContent = i + 1;
        });
    }

    document.getElementById('addBaby').addEventListener('click', () => {
        const idx = Date.now();
        const wrapper = document.createElement('div');
        wrapper.innerHTML = tmpl.innerHTML.replaceAll('__INDEX__', idx);
        list.appendChild(wrapper.firstElementChild);
        renumber();
    });

    list.addEventListener('click', e => {
        if (e.target.classList.contains('remove-baby')) {
            if (list.querySelectorAll('.newborn-row').length > 1) {
                e.target.closest('.newborn-row').remove();
                renumber();
            } else {
                alert('At least one newborn record is required for delivery workflows.');
            }
        }
    });
})();
</script>
@endsection
@endsection

