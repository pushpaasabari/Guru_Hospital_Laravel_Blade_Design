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
    <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <span class="badge bg-primary me-2">3.1</span> Diagnostic Investigations &amp; Lab Results (Enter Values Only)
        </div>
        <button type="button" onclick="setAllViralNegative()" class="btn btn-sm btn-outline-success fw-semibold">
            <i class="bi bi-shield-check me-1"></i> Set All Viral Markers Negative
        </button>
    </div>
    <div class="card-body">
        {{-- Row 1: Hematology & Blood Group --}}
        <div class="p-3 bg-light rounded-3 mb-3 border">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-droplet-half text-danger me-1"></i> Hematology &amp; Blood Grouping</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Haemoglobin (Hb)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 85px;">Hb :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_hb" placeholder="Enter value only (e.g. 12.9 mgs %)" oninput="syncLabFields()">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Blood Group &amp; Rh (BG)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 85px;">BG :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_bg" placeholder="e.g. O positive" list="bgOptionsList" oninput="syncLabFields()">
                        <datalist id="bgOptionsList">
                            <option value="O positive">
                            <option value="A positive">
                            <option value="B positive">
                            <option value="AB positive">
                            <option value="O negative">
                            <option value="A negative">
                            <option value="B negative">
                            <option value="AB negative">
                        </datalist>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Viral Markers --}}
        <div class="p-3 bg-light rounded-3 mb-3 border">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-virus text-primary me-1"></i> Viral Screening</h6>
                <button type="button" onclick="setAllViralNegative()" class="btn btn-xs btn-outline-success" style="font-size: 0.78rem;">Quick: All Negative</button>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">HCV</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 70px;">HCV :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_hcv" placeholder="Negative" oninput="syncLabFields()">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">HIV</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 70px;">HIV :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_hiv" placeholder="Negative" oninput="syncLabFields()">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">VDRL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 70px;">VDRL :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_vdrl" placeholder="Negative" oninput="syncLabFields()">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">HBsAg</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 70px;">HBsAg :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_hbsag" placeholder="Negative" oninput="syncLabFields()">
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Biochemistry & Renal Function --}}
        <div class="p-3 bg-light rounded-3 mb-3 border">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-activity text-success me-1"></i> Biochemistry &amp; Renal Function</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Blood Urea</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 75px;">Urea :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_urea" placeholder="e.g. 9.7 g/dl" oninput="syncLabFields()">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Serum Creatinine</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 75px;">Creat :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_creatinine" placeholder="e.g. 0.86 mg/dl" oninput="syncLabFields()">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">Serum Uric Acid</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 75px;">Uric :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_uric_acid" placeholder="e.g. 4.3 mg/dl" oninput="syncLabFields()">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small text-uppercase">RBS</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold" style="width: 75px;">RBS :</span>
                        <input type="text" class="form-control lab-val-input" id="lab_rbs" placeholder="e.g. 100.8 mg/dl" oninput="syncLabFields()">
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden synchronized textarea --}}
        <textarea
            class="form-control font-monospace d-none"
            name="investigations"
            id="investigationsTextarea"
            rows="6"
        >{{ old('investigations', $admission->investigations) }}</textarea>
    </div>
</div>

{{-- Live Print Preview --}}
<div class="card mb-4 border-0 shadow-sm border-start border-4 border-primary">
    <div class="card-header bg-white fw-bold py-2 small text-uppercase text-secondary">
        <i class="bi bi-printer me-1"></i> Live Preview (INVESTIGATIONS Table)
    </div>
    <div class="card-body bg-white p-3">
        <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.86rem;">
            <thead class="table-dark">
                <tr><th>HB</th><th>BLOOD GROUP</th><th>HCV</th><th>HIV</th><th>VDRL</th></tr>
            </thead>
            <tbody>
                <tr class="fw-bold bg-light"><td id="prev_hb">—</td><td id="prev_bg">—</td><td id="prev_hcv">—</td><td id="prev_hiv">—</td><td id="prev_vdrl">—</td></tr>
            </tbody>
            <thead class="table-dark">
                <tr><th>HBSAG</th><th>UREA</th><th>CREATININE</th><th>URIC ACID</th><th>RBS</th></tr>
            </thead>
            <tbody>
                <tr class="fw-bold bg-light"><td id="prev_hbsag">—</td><td id="prev_urea">—</td><td id="prev_creatinine">—</td><td id="prev_uric_acid">—</td><td id="prev_rbs">—</td></tr>
            </tbody>
        </table>
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

<script>
function parseExistingInvestigations() {
    const raw = document.getElementById('investigationsTextarea').value || '';
    const clean = raw.replace(/\t+/g, '\n').replace(/\|/g, '\n');
    const lines = clean.split(/\r?\n/).map(l => l.trim()).filter(Boolean);

    lines.forEach(line => {
        const m = line.match(/^([^:=]+)[:=](.+)$/);
        if (m) {
            const rawK = m[1].trim();
            const val = m[2].trim();
            const k = rawK.toLowerCase().replace(/[^a-z0-9]/g, '');

            if (k === 'hb' || k === 'haemoglobin') setField('lab_hb', val);
            else if (k === 'bg' || k === 'bloodgroup' || k === 'rh') setField('lab_bg', val);
            else if (k === 'hcv') setField('lab_hcv', val);
            else if (k === 'hiv') setField('lab_hiv', val);
            else if (k === 'vdrl') setField('lab_vdrl', val);
            else if (k === 'hbsag' || k === 'hbs') setField('lab_hbsag', val);
            else if (k === 'urea') setField('lab_urea', val);
            else if (k === 'creatine' || k === 'creatinine') setField('lab_creatinine', val);
            else if (k === 'uricacid') setField('lab_uric_acid', val);
            else if (k === 'rbs' || k === 'bloodsugar') setField('lab_rbs', val);
        }
    });

    syncLabFields();
}

function setField(id, val) {
    const el = document.getElementById(id);
    if (el) el.value = val;
}

function getVal(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : '';
}

function syncLabFields() {
    const hb = getVal('lab_hb');
    const bg = getVal('lab_bg');
    const hcv = getVal('lab_hcv');
    const hiv = getVal('lab_hiv');
    const vdrl = getVal('lab_vdrl');
    const hbsag = getVal('lab_hbsag');
    const urea = getVal('lab_urea');
    const creatinine = getVal('lab_creatinine');
    const uric = getVal('lab_uric_acid');
    const rbs = getVal('lab_rbs');

    // Update preview
    document.getElementById('prev_hb').innerText = hb || '—';
    document.getElementById('prev_bg').innerText = bg || '—';
    document.getElementById('prev_hcv').innerText = hcv || '—';
    document.getElementById('prev_hiv').innerText = hiv || '—';
    document.getElementById('prev_vdrl').innerText = vdrl || '—';
    document.getElementById('prev_hbsag').innerText = hbsag || '—';
    document.getElementById('prev_urea').innerText = urea || '—';
    document.getElementById('prev_creatinine').innerText = creatinine || '—';
    document.getElementById('prev_uric_acid').innerText = uric || '—';
    document.getElementById('prev_rbs').innerText = rbs || '—';

    // Serialize to hidden textarea
    const lines = [];
    if (hb) lines.push(`Hb : ${hb}`);
    if (bg) lines.push(`BG : ${bg}`);
    if (hcv) lines.push(`HCV : ${hcv}`);
    if (hiv) lines.push(`HIV : ${hiv}`);
    if (vdrl) lines.push(`VDRL : ${vdrl}`);
    if (hbsag) lines.push(`HBsAG : ${hbsag}`);
    if (urea) lines.push(`UREA : ${urea}`);
    if (creatinine) lines.push(`CREATININE : ${creatinine}`);
    if (uric) lines.push(`URIC ACID : ${uric}`);
    if (rbs) lines.push(`RBS : ${rbs}`);

    document.getElementById('investigationsTextarea').value = lines.join('\n');
}

function setAllViralNegative() {
    setField('lab_hiv', 'Negative');
    setField('lab_hcv', 'Negative');
    setField('lab_vdrl', 'Negative');
    setField('lab_hbsag', 'Negative');
    syncLabFields();
}

document.addEventListener('DOMContentLoaded', parseExistingInvestigations);
</script>
@endsection

