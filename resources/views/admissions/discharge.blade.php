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
    <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <span class="badge bg-primary me-2">4.3</span> Discharge Medication Orders (Dropdown &amp; Timing)
        </div>
        <div class="d-flex gap-1 flex-wrap">
            <span class="small text-muted me-1 align-self-center">Prescription Packs:</span>
            <button type="button" class="btn btn-xs btn-outline-primary" style="font-size: 0.78rem;" onclick="applyMedPackBlade('anitha')">+ Cefixime Pack</button>
            <button type="button" class="btn btn-xs btn-outline-secondary" style="font-size: 0.78rem;" onclick="applyMedPackBlade('delivery')">+ Delivery Routine</button>
            <button type="button" class="btn btn-xs btn-outline-secondary" style="font-size: 0.78rem;" onclick="applyMedPackBlade('ursl')">+ URSL Surgery Pack</button>
        </div>
    </div>
    <div class="card-body">
        {{-- Medication Repeater Table --}}
        <div class="table-responsive mb-3">
            <table class="table table-bordered align-middle mb-2">
                <thead class="table-light">
                    <tr style="font-size: 0.86rem;">
                        <th style="width: 4%;">#</th>
                        <th style="width: 40%;">Medication (Dropdown / Type Name)</th>
                        <th style="width: 18%;">Frequency</th>
                        <th style="width: 16%;">Duration</th>
                        <th style="width: 16%;">Timing</th>
                        <th style="width: 6%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="medRowsTableBody"></tbody>
            </table>
            <div class="text-end">
                <button type="button" onclick="addMedRowBlade()" class="btn btn-sm btn-primary fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i> Add Medication Row
                </button>
            </div>
        </div>

        {{-- Live 4-Column Table Preview --}}
        <div class="p-3 bg-light rounded-3 mb-3 border">
            <div class="fw-bold text-dark small text-uppercase mb-2">
                <i class="bi bi-eye me-1"></i> Document Print Preview: 4-Column Medication Table
            </div>
            <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 0.84rem;">
                <thead class="table-dark">
                    <tr><th style="width: 44%;">Medicine</th><th style="width: 18%;">Frequency</th><th style="width: 18%;">Duration</th><th style="width: 20%;">Timing</th></tr>
                </thead>
                <tbody id="prevMedsTableBody"></tbody>
            </table>
        </div>

        {{-- Hidden synchronized textarea --}}
        <textarea class="form-control font-monospace d-none" name="medication_orders" id="medicationOrdersField" rows="4">{{ old('medication_orders', $admission->medication_orders) }}</textarea>

        {{-- Stent Removal Notice Section --}}
        <div class="card mb-3 border-warning bg-warning-subtle">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="stentBladeSwitch" onchange="toggleStentBoxBlade(this.checked)" {{ preg_match('/stent/i', ($admission->procedure_name ?? '') . ' ' . ($admission->procedure_done ?? '') . ' ' . ($admission->discharge_advice ?? '') . ' ' . ($admission->follow_up ?? '')) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark" for="stentBladeSwitch">
                            <i class="bi bi-shield-plus text-primary me-1"></i> Patient has STENT (Internal Stent in situ)
                        </label>
                    </div>
                    <button type="button" onclick="insertTamilStentNoticeBlade()" class="btn btn-sm btn-warning text-dark fw-bold shadow-xs">
                        <i class="bi bi-pencil-square me-1"></i> Insert Tamil Stent Instruction into Advice
                    </button>
                </div>

                <div id="stentDetailsBladeBox" class="{{ preg_match('/stent/i', ($admission->procedure_name ?? '') . ' ' . ($admission->procedure_done ?? '') . ' ' . ($admission->discharge_advice ?? '') . ' ' . ($admission->follow_up ?? '')) ? '' : 'd-none' }}">
                    <div class="p-2.5 bg-white rounded border mb-2">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <label class="form-label fw-bold small text-dark mb-0">Stent Removal Due Date:</label>
                            </div>
                            <div class="col-auto">
                                <input type="text" id="stentDateBladeInput" class="form-control form-control-sm" style="width: 140px;" value="" placeholder="e.g. 21.08.2026" oninput="updateTamilStentNoticePreview()">
                            </div>
                            <div class="col-auto">
                                <span class="text-muted small">(Date to appear in Tamil Stent Notice)</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-2.5 bg-light rounded border" style="font-family: 'Nirmala UI', 'Nirmala', Arial, sans-serif; font-size: 0.92rem; line-height: 1.5;">
                        <strong class="text-primary d-block mb-1">
                            <i class="bi bi-info-circle-fill me-1"></i> அதிகாரப்பூர்வ ஸ்டெண்ட் அகற்றல் குறிப்பு (Official Stent Notice):
                        </strong>
                        <span id="tamilNoticePreviewSpan" class="text-dark fw-semibold">
                            &ldquo;வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு (Date) அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.&rdquo;
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Advice and Follow-Up --}}
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Discharge Advice &amp; Instructions</label>
                <textarea class="form-control" name="discharge_advice" id="dischargeAdviceField" rows="3">{{ old('discharge_advice', $admission->discharge_advice) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Follow-up Advice &amp; Review Date</label>
                <textarea class="form-control" name="follow_up" id="followUpField" rows="3">{{ old('follow_up', $admission->follow_up) }}</textarea>
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

<script>
const BLADE_COMMON_MEDS = [
    'Tab. Cefixime + Sulbactam',
    'Tab. Cefixime 200mg',
    'Tab. Acendol-P (Aceclofenac + Paracetamol)',
    'Cap. Cyra-D (Rabeprazole + Domperidone)',
    'Tab. Clavam 625mg (Amoxicillin + Clavulanate)',
    'Tab. Zerodol-P',
    'Tab. Zerodol-SP',
    'Tab. Paracetamol 650mg',
    'Tab. Pantoprazole 40mg',
    'Tab. Pantocid-D',
    'Tab. Metrogyl 400mg',
    'Tab. Alonac-SP',
    'Tab. Niftas 100mg',
    'Tab. Silodosin 8mg',
    'Tab. Iron & Folic Acid',
    'Tab. Shelcal 500mg (Calcium)',
    'Syp. Cremaffin',
    'Inj. Pipzo 4.5g',
    'Inj. Metrogyl 100mg',
    'Inj. Pan 40'
];

const BLADE_FREQUENCIES = ['1-0-1', '1-0-0', '0-0-1', '1-1-1', '0-1-0', '1-0-1-0', '1-1-1-1', 'SOS', 'Stat'];
const BLADE_DURATIONS = ['7 Days', '5 Days', '3 Days', '10 Days', '14 Days', '1 Month', '6 Nos', '9 Nos', 'SOS'];
const BLADE_TIMINGS = ['After food', 'Before food', 'With food', 'At bedtime', 'Empty stomach', 'As directed'];

let bladeMedRows = [];

function initBladeMedications() {
    const raw = document.getElementById('medicationOrdersField').value || '';
    bladeMedRows = parseBladeMedications(raw);
    renderBladeMedRows();

    // Check stent date from follow-up or discharge advice
    const followUp = document.getElementById('followUpField').value || '';
    const advice = document.getElementById('dischargeAdviceField').value || '';
    const dm = (followUp + ' ' + advice).match(/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/);
    if (dm) {
        document.getElementById('stentDateBladeInput').value = dm[1];
        updateTamilStentNoticePreview();
    }
}

function parseBladeMedications(raw) {
    if (!raw.trim()) {
        return [
            { medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' }
        ];
    }

    const lines = raw.split(/\r?\n/).map(l => l.trim().replace(/^[\*•\-\d\.]+\s*/, '')).filter(Boolean);
    const rows = [];

    lines.forEach(line => {
        let freq = '1-0-1';
        let dur = '7 Days';
        let timing = 'After food';

        const fm = line.match(/\(([\d\-]+)\)/) || line.match(/\b(\d-\d-\d(?:-\d)?|SOS|Stat)\b/i);
        if (fm) { freq = fm[1]; line = line.replace(fm[0], ' '); }

        const tm = line.match(/(After\s+food|Before\s+food|With\s+food|At\s+bedtime|Bedtime|Empty\s+stomach)/i);
        if (tm) { timing = tm[1]; line = line.replace(tm[0], ' '); }

        const dm = line.match(/(?:x\s*|for\s*)?(\d+\s*(?:days|weeks|months|day|nos))/i);
        if (dm) { dur = dm[1]; line = line.replace(dm[0], ' '); }

        const med = line.replace(/[-–x\(\)]+/g, ' ').replace(/\s+/g, ' ').trim();
        if (med) {
            rows.push({ medicine: med, frequency: freq, duration: dur, timing: timing });
        }
    });

    return rows.length > 0 ? rows : [{ medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' }];
}

function renderBladeMedRows() {
    const tbody = document.getElementById('medRowsTableBody');
    const prevTbody = document.getElementById('prevMedsTableBody');
    if (!tbody || !prevTbody) return;

    tbody.innerHTML = '';
    prevTbody.innerHTML = '';

    bladeMedRows.forEach((row, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="fw-bold text-secondary text-center">${idx + 1}</td>
            <td>
                <input type="text" class="form-control form-control-sm" value="${escapeHtml(row.medicine)}" placeholder="Select or type medicine..." list="bladeMedList_${idx}" oninput="updateBladeMedRow(${idx}, 'medicine', this.value)">
                <datalist id="bladeMedList_${idx}">
                    ${BLADE_COMMON_MEDS.map(m => `<option value="${m}">`).join('')}
                </datalist>
            </td>
            <td>
                <select class="form-select form-select-sm" onchange="updateBladeMedRow(${idx}, 'frequency', this.value)">
                    ${BLADE_FREQUENCIES.map(f => `<option value="${f}" ${row.frequency === f ? 'selected' : ''}>${f}</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" value="${escapeHtml(row.duration)}" placeholder="7 Days" list="bladeDurList_${idx}" oninput="updateBladeMedRow(${idx}, 'duration', this.value)">
                <datalist id="bladeDurList_${idx}">
                    ${BLADE_DURATIONS.map(d => `<option value="${d}">`).join('')}
                </datalist>
            </td>
            <td>
                <select class="form-select form-select-sm" onchange="updateBladeMedRow(${idx}, 'timing', this.value)">
                    ${BLADE_TIMINGS.map(t => `<option value="${t}" ${row.timing.toLowerCase() === t.toLowerCase() ? 'selected' : ''}>${t}</option>`).join('')}
                </select>
            </td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" onclick="moveBladeMedRow(${idx}, -1)" ${idx === 0 ? 'disabled' : ''}><i class="bi bi-arrow-up"></i></button>
                    <button type="button" class="btn btn-outline-danger" onclick="removeBladeMedRow(${idx})" ${bladeMedRows.length <= 1 && !row.medicine ? 'disabled' : ''}><i class="bi bi-trash"></i></button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);

        if (row.medicine.trim()) {
            const prevTr = document.createElement('tr');
            prevTr.innerHTML = `
                <td class="fw-semibold">${escapeHtml(row.medicine)}</td>
                <td>${escapeHtml(row.frequency)}</td>
                <td>${escapeHtml(row.duration)}</td>
                <td>${escapeHtml(row.timing)}</td>
            `;
            prevTbody.appendChild(prevTr);
        }
    });

    syncBladeMedField();
}

function updateBladeMedRow(idx, field, val) {
    bladeMedRows[idx][field] = val;
    syncBladeMedField();
}

function addMedRowBlade(obj = null) {
    bladeMedRows.push(obj || { medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' });
    renderBladeMedRows();
}

function removeBladeMedRow(idx) {
    bladeMedRows.splice(idx, 1);
    if (bladeMedRows.length === 0) bladeMedRows.push({ medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' });
    renderBladeMedRows();
}

function moveBladeMedRow(idx, delta) {
    const target = idx + delta;
    if (target < 0 || target >= bladeMedRows.length) return;
    const temp = bladeMedRows[idx];
    bladeMedRows[idx] = bladeMedRows[target];
    bladeMedRows[target] = temp;
    renderBladeMedRows();
}

function syncBladeMedField() {
    const valid = bladeMedRows.filter(r => r.medicine.trim());
    const serialized = valid.map(r => `* ${r.medicine.trim()} (${r.frequency || '1-0-1'}) x ${r.duration || '7 Days'} - ${r.timing || 'After food'}`).join('\n');
    document.getElementById('medicationOrdersField').value = serialized;
}

function applyMedPackBlade(pack) {
    if (pack === 'anitha') {
        bladeMedRows = [
            { medicine: 'Tab. Cefixime + Sulbactam', frequency: '1-0-1', duration: '7 Days', timing: 'After food' },
            { medicine: 'Tab. Acendol-P', frequency: '1-0-1', duration: '7 Days', timing: 'After food' },
            { medicine: 'Cap. Cyra-D', frequency: '1-0-1', duration: '7 Days', timing: 'Before food' }
        ];
    } else if (pack === 'delivery') {
        bladeMedRows = [
            { medicine: 'Tab. Clavam 625mg', frequency: '1-0-1', duration: '5 Days', timing: 'After food' },
            { medicine: 'Tab. Paracetamol 650mg', frequency: '1-1-1', duration: '3 Days', timing: 'After food' },
            { medicine: 'Cap. Cyra-D', frequency: '1-0-1', duration: '5 Days', timing: 'Before food' },
            { medicine: 'Tab. Iron & Folic Acid', frequency: '1-0-0', duration: '30 Days', timing: 'After food' }
        ];
    } else if (pack === 'ursl') {
        bladeMedRows = [
            { medicine: 'Tab. Niftas 100mg', frequency: '1-0-1', duration: '6 Nos', timing: 'After food' },
            { medicine: 'Tab. Paracetamol 650mg', frequency: '1-1-1', duration: '9 Nos', timing: 'After food' },
            { medicine: 'Cap. Cyra-D', frequency: '1-0-1', duration: '6 Nos', timing: 'Before food' },
            { medicine: 'Tab. Zerodol-P', frequency: '1-0-1', duration: '6 Nos', timing: 'After food' }
        ];
    }
    renderBladeMedRows();
}

function toggleStentBoxBlade(checked) {
    const box = document.getElementById('stentDetailsBladeBox');
    if (box) box.classList.toggle('d-none', !checked);
}

function updateTamilStentNoticePreview() {
    const d = document.getElementById('stentDateBladeInput').value.trim() || '(Date)';
    const text = `வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு ${d} அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.`;
    document.getElementById('tamilNoticePreviewSpan').innerText = `“${text}”`;
}

function insertTamilStentNoticeBlade() {
    const d = document.getElementById('stentDateBladeInput').value.trim() || '(Date)';
    const text = `வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு ${d} அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.`;
    const adviceEl = document.getElementById('dischargeAdviceField');
    if (adviceEl && !adviceEl.value.includes('வயிற்றுக்குள்')) {
        adviceEl.value = adviceEl.value ? `${adviceEl.value.trim()}\n${text}` : text;
    }
    const followUpEl = document.getElementById('followUpField');
    if (followUpEl && !followUpEl.value.includes('stent removal')) {
        followUpEl.value = followUpEl.value ? `${followUpEl.value.trim()}\n${d} for stent removal` : `${d} for stent removal`;
    }
}

function escapeHtml(str) {
    return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', initBladeMedications);
</script>
@endsection

