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
            @if($admission->surgery_type === 'delivery')
            <div class="col-md-4">
                <label class="form-label required">
                    PICME Number (RCH ID)
                    <span class="badge bg-danger-subtle text-danger ms-1" style="font-size: 0.68rem;">DELIVERY / OBSTETRIC</span>
                </label>
                <input class="form-control" name="picme_no" value="{{ old('picme_no', $admission->picme_no) }}" placeholder="e.g. 331024567890 (12-digit PICME / RCH ID)">
            </div>
            @endif
            <div class="{{ $admission->surgery_type === 'delivery' ? 'col-md-4' : 'col-md-5' }}">
                <label class="form-label">Procedure / Surgery Name</label>
                <input class="form-control" name="procedure_name" value="{{ old('procedure_name', $admission->procedure_name) }}" placeholder="e.g. Emergency Lower Segment Caesarean Section (LSCS)">
            </div>
            <div class="{{ $admission->surgery_type === 'delivery' ? 'col-md-4' : 'col-md-3' }}">
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
                <input class="form-control" style="text-transform: uppercase;" data-uppercase-input name="surgeon" value="{{ old('surgeon', $admission->surgeon) }}" placeholder="e.g. DR. AMBIKA MS OG">
            </div>
            <div class="col-md-4">
                <label class="form-label">Assisted Surgeon</label>
                <input class="form-control" style="text-transform: uppercase;" data-uppercase-input name="assisted_surgeon" value="{{ old('assisted_surgeon', $admission->assisted_surgeon) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Anaesthetist</label>
                <input class="form-control" style="text-transform: uppercase;" data-uppercase-input name="anaesthetist" value="{{ old('anaesthetist', $admission->anaesthetist) }}" placeholder="e.g. DR. M.S KARTHICK (SA)">
            </div>
            <div class="col-md-4">
                <label class="form-label">Assisted Anaesthetist</label>
                <input class="form-control" style="text-transform: uppercase;" data-uppercase-input name="assisted_anaesthetist" value="{{ old('assisted_anaesthetist', $admission->assisted_anaesthetist) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Anaesthesia Type</label>
                <input class="form-control" name="anaesthesia_type" value="{{ old('anaesthesia_type', $admission->anaesthesia_type) }}" placeholder="e.g. Spinal Anaesthesia">
            </div>
            <div class="col-md-4">
                <label class="form-label">Staff Nurse</label>
                <input class="form-control" style="text-transform: uppercase;" data-uppercase-input name="staff_nurse" value="{{ old('staff_nurse', $admission->staff_nurse) }}" placeholder="e.g. STAFF NURSE ANITHA">
            </div>
            <div class="col-md-4">
                <label class="form-label">OT Technician</label>
                <input class="form-control" style="text-transform: uppercase;" data-uppercase-input name="ot_technician" value="{{ old('ot_technician', $admission->ot_technician) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Pediatrician</label>
                <input class="form-control" style="text-transform: uppercase;" data-uppercase-input name="pediatrician_name" value="{{ old('pediatrician_name', $admission->pediatrician_name) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Assistant / Other Clinical Help</label>
                <input class="form-control" style="text-transform: uppercase;" data-uppercase-input name="assisted_by" value="{{ old('assisted_by', $admission->assisted_by) }}">
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
            <div class="col-12 mt-2 pt-2 border-top">
                <div class="card bg-light-subtle border">
                    <div class="card-body p-3">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" id="stentSwitch" onchange="toggleStentNotice(this.checked)" {{ preg_match('/stent/i', ($admission->procedure_name ?? '') . ' ' . ($admission->procedure_done ?? '') . ' ' . ($admission->findings ?? '') . ' ' . ($admission->drain ?? '')) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark" for="stentSwitch">
                                <i class="bi bi-shield-plus text-primary me-1"></i> Patient has STENT (e.g. DJ Stent inserted)
                            </label>
                        </div>
                        <div id="stentNoticeAlert" class="alert alert-warning py-2 px-3 mb-0 border-0 rounded-2 mt-2 {{ preg_match('/stent/i', ($admission->procedure_name ?? '') . ' ' . ($admission->procedure_done ?? '') . ' ' . ($admission->findings ?? '') . ' ' . ($admission->drain ?? '')) ? '' : 'd-none' }}">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                                <div class="small">
                                    <strong>Stent Placement Notice:</strong> When a patient has a stent, the mandatory Tamil stent removal instruction:
                                    <div class="font-monospace fw-bold text-dark mt-1" style="font-size: 0.85rem;">
                                        &ldquo;வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு (Date) அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.&rdquo;
                                    </div>
                                    will automatically appear on the patient&rsquo;s Discharge Summary.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Immediate Postoperative Orders (Separate Rows) --}}
            <div class="col-12 mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold text-dark mb-0">
                        <i class="bi bi-list-check text-primary me-1"></i> Immediate Postoperative Orders (Row by Row)
                    </label>
                    <button type="button" onclick="addPostOpRow()" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Add Order Row
                    </button>
                </div>

                {{-- Quick Presets --}}
                <div className="mb-2" style="margin-bottom: 8px;">
                    <span class="small text-muted me-1">Quick add:</span>
                    <button type="button" class="btn btn-xs btn-light border text-secondary" style="font-size: 0.76rem; padding: 2px 8px;" onclick="addPostOpRow('NPO for 6 hours')">+ NPO for 6 hours</button>
                    <button type="button" class="btn btn-xs btn-light border text-secondary" style="font-size: 0.76rem; padding: 2px 8px;" onclick="addPostOpRow('IV Fluids: RL @ 100 ml/hr')">+ IV Fluids RL</button>
                    <button type="button" class="btn btn-xs btn-light border text-secondary" style="font-size: 0.76rem; padding: 2px 8px;" onclick="addPostOpRow('Monitor vitals q1h x 4h, then q4h')">+ Monitor vitals</button>
                    <button type="button" class="btn btn-xs btn-light border text-secondary" style="font-size: 0.76rem; padding: 2px 8px;" onclick="addPostOpRow('Inj. Tramadol 50mg IV SOS (severe pain)')">+ Inj. Tramadol</button>
                    <button type="button" class="btn btn-xs btn-light border text-secondary" style="font-size: 0.76rem; padding: 2px 8px;" onclick="addPostOpRow('Inj. Ondansetron 4mg IV SOS (vomiting)')">+ Inj. Ondansetron</button>
                    <button type="button" class="btn btn-xs btn-light border text-secondary" style="font-size: 0.76rem; padding: 2px 8px;" onclick="addPostOpRow('Catheter care & monitor hourly urine output')">+ Catheter care</button>
                    <button type="button" class="btn btn-xs btn-light border text-secondary" style="font-size: 0.76rem; padding: 2px 8px;" onclick="addPostOpRow('Check dressing site for soakage / bleeding')">+ Check dressing</button>
                </div>

                {{-- Rows Container --}}
                <div class="bg-light p-3 rounded-3 border">
                    <div id="postOpRowsContainer"></div>
                    <div class="mt-2 text-end">
                        <button type="button" onclick="addPostOpRow()" class="btn btn-sm btn-primary fw-semibold">
                            <i class="bi bi-plus-lg me-1"></i> Add Another Order Row
                        </button>
                    </div>
                </div>

                <input type="hidden" name="postoperative_orders" id="postoperativeOrdersField" value="{{ old('postoperative_orders', $admission->postoperative_orders) }}">
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

<script>
let postOpOrders = [];

function initPostOpOrders() {
    const raw = document.getElementById('postoperativeOrdersField').value || '';
    const lines = raw.split(/\r?\n/).map(s => s.replace(/^[-*•\d+.\s]+/, '').trim()).filter(Boolean);
    if (lines.length > 0) {
        postOpOrders = lines;
    } else {
        postOpOrders = [''];
    }
    renderPostOpRows();
}

function renderPostOpRows() {
    const container = document.getElementById('postOpRowsContainer');
    if (!container) return;
    container.innerHTML = '';

    postOpOrders.forEach((order, idx) => {
        const div = document.createElement('div');
        div.className = 'input-group mb-2 shadow-xs';
        div.innerHTML = `
            <span class="input-group-text bg-white fw-bold text-secondary" style="width: 48px; justify-content: center;">#${idx + 1}</span>
            <input type="text" class="form-control" value="${escapeHtml(order)}" placeholder="Order #${idx + 1} (e.g. NPO x 6 hours, IV fluids RL @ 100ml/hr)" oninput="updatePostOpRow(${idx}, this.value)">
            <button type="button" class="btn btn-outline-secondary" onclick="movePostOpRow(${idx}, -1)" ${idx === 0 ? 'disabled' : ''}><i class="bi bi-arrow-up"></i></button>
            <button type="button" class="btn btn-outline-secondary" onclick="movePostOpRow(${idx}, 1)" ${idx === postOpOrders.length - 1 ? 'disabled' : ''}><i class="bi bi-arrow-down"></i></button>
            <button type="button" class="btn btn-outline-danger" onclick="removePostOpRow(${idx})"><i class="bi bi-trash"></i></button>
        `;
        container.appendChild(div);
    });

    syncPostOpField();
}

function addPostOpRow(val = '') {
    postOpOrders.push(val);
    renderPostOpRows();
}

function updatePostOpRow(idx, val) {
    postOpOrders[idx] = val;
    syncPostOpField();
}

function removePostOpRow(idx) {
    postOpOrders.splice(idx, 1);
    if (postOpOrders.length === 0) postOpOrders.push('');
    renderPostOpRows();
}

function movePostOpRow(idx, delta) {
    const target = idx + delta;
    if (target < 0 || target >= postOpOrders.length) return;
    const temp = postOpOrders[idx];
    postOpOrders[idx] = postOpOrders[target];
    postOpOrders[target] = temp;
    renderPostOpRows();
}

function syncPostOpField() {
    const valid = postOpOrders.map(s => s.trim()).filter(Boolean);
    const serialized = valid.map((s, i) => `${i + 1}. ${s}`).join('\n');
    document.getElementById('postoperativeOrdersField').value = serialized;
}

function toggleStentNotice(checked) {
    const alert = document.getElementById('stentNoticeAlert');
    if (alert) alert.classList.toggle('d-none', !checked);
}

function escapeHtml(str) {
    return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', initPostOpOrders);
</script>
@endsection

