import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import AdmissionStageNav from '../../Components/AdmissionStageNav';

interface AdmissionData {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  age?: string | null;
  sex?: string | null;
  format: string;
  surgery_type: string;
  diagnosis: string;
  investigations?: string | null;
  status: string;
}

interface Props {
  admission: AdmissionData;
}

interface LabValues {
  hb: string;
  bg: string;
  hcv: string;
  hiv: string;
  vdrl: string;
  hbsag: string;
  urea: string;
  creatinine: string;
  uric_acid: string;
  rbs: string;
  ecg: string;
}

interface ExtraTest {
  name: string;
  value: string;
}

const parseInvestigations = (raw: string): { core: LabValues; extra: ExtraTest[] } => {
  const core: LabValues = {
    hb: '',
    bg: '',
    hcv: '',
    hiv: '',
    vdrl: '',
    hbsag: '',
    urea: '',
    creatinine: '',
    uric_acid: '',
    rbs: '',
    ecg: '',
  };
  const extra: ExtraTest[] = [];

  if (!raw || !raw.trim()) {
    return { core, extra };
  }

  const cleanRaw = raw.replace(/\t+/g, '\n').replace(/\|/g, '\n');
  const lines = cleanRaw.split(/\r?\n/).map((l) => l.trim()).filter(Boolean);

  for (const line of lines) {
    const m = line.match(/^([^:=]+)[:=](.+)$/);
    if (m) {
      const rawK = m[1].trim();
      const val = m[2].trim();
      const k = rawK.toLowerCase().replace(/[^a-z0-9]/g, '');

      if (k === 'hb' || k === 'haemoglobin') core.hb = val;
      else if (k === 'bg' || k === 'bloodgroup' || k === 'bloodgrouping' || k === 'rh') core.bg = val;
      else if (k === 'hcv') core.hcv = val;
      else if (k === 'hiv') core.hiv = val;
      else if (k === 'vdrl') core.vdrl = val;
      else if (k === 'hbsag' || k === 'hbs') core.hbsag = val;
      else if (k === 'urea' || k === 'bloodurea') core.urea = val;
      else if (k === 'creatine' || k === 'creatinine' || k === 'serumcreatinine') core.creatinine = val;
      else if (k === 'uricacid' || k === 'serumuricacid') core.uric_acid = val;
      else if (k === 'rbs' || k === 'bloodsugar' || k === 'randombloodsugar') core.rbs = val;
      else if (k === 'ecg' || k === 'ekg' || k === 'electrocardiogram') core.ecg = val;
      else {
        extra.push({ name: rawK, value: val });
      }
    } else {
      if (!line.toLowerCase().startsWith('investigations')) {
        extra.push({ name: 'Note', value: line });
      }
    }
  }

  return { core, extra };
};

const serializeInvestigations = (core: LabValues, extra: ExtraTest[]): string => {
  const lines: string[] = [];
  if (core.hb.trim()) lines.push(`Hb : ${core.hb.trim()}`);
  if (core.bg.trim()) lines.push(`BG : ${core.bg.trim()}`);
  if (core.hcv.trim()) lines.push(`HCV : ${core.hcv.trim()}`);
  if (core.hiv.trim()) lines.push(`HIV : ${core.hiv.trim()}`);
  if (core.vdrl.trim()) lines.push(`VDRL : ${core.vdrl.trim()}`);
  if (core.hbsag.trim()) lines.push(`HBsAG : ${core.hbsag.trim()}`);
  if (core.urea.trim()) lines.push(`UREA : ${core.urea.trim()}`);
  if (core.creatinine.trim()) lines.push(`CREATININE : ${core.creatinine.trim()}`);
  if (core.uric_acid.trim()) lines.push(`URIC ACID : ${core.uric_acid.trim()}`);
  if (core.rbs.trim()) lines.push(`RBS : ${core.rbs.trim()}`);
  if (core.ecg && core.ecg.trim()) lines.push(`ECG : ${core.ecg.trim()}`);

  for (const item of extra) {
    if (item.value.trim()) {
      if (item.name === 'Note' || !item.name.trim()) {
        lines.push(item.value.trim());
      } else {
        lines.push(`${item.name.trim()} : ${item.value.trim()}`);
      }
    }
  }

  return lines.join('\n');
};

export default function AdmissionsTestReports({ admission }: Props) {
  const initial = parseInvestigations(admission.investigations || '');
  const [coreValues, setCoreValues] = useState<LabValues>(initial.core);
  const [extraTests, setExtraTests] = useState<ExtraTest[]>(initial.extra);
  const [showRawEditor, setShowRawEditor] = useState<boolean>(false);

  const { data, setData, put, transform, processing, errors } = useForm({
    investigations: admission.investigations || serializeInvestigations(initial.core, initial.extra),
    next_step: 'surgery',
  });

  const updateCore = (key: keyof LabValues, val: string) => {
    setCoreValues((prev) => ({ ...prev, [key]: val }));
  };

  const setAllViralNegative = () => {
    setCoreValues((prev) => ({
      ...prev,
      hiv: 'Negative',
      hcv: 'Negative',
      vdrl: 'Negative',
      hbsag: 'Negative',
    }));
  };

  const addExtraTest = (name = '', value = '') => {
    setExtraTests((prev) => [...prev, { name, value }]);
  };

  const updateExtraTest = (idx: number, field: 'name' | 'value', val: string) => {
    setExtraTests((prev) => {
      const updated = [...prev];
      updated[idx] = { ...updated[idx], [field]: val };
      return updated;
    });
  };

  const removeExtraTest = (idx: number) => {
    setExtraTests((prev) => prev.filter((_, i) => i !== idx));
  };

  const toggleRawEditor = () => {
    if (!showRawEditor) {
      setData('investigations', serializeInvestigations(coreValues, extraTests));
    }
    setShowRawEditor((prev) => !prev);
  };

  const handleRawChange = (raw: string) => {
    setData('investigations', raw);
    const parsed = parseInvestigations(raw);
    setCoreValues(parsed.core);
    setExtraTests(parsed.extra);
  };

  const submitStage = (step: 'stay' | 'surgery', e: React.FormEvent) => {
    e.preventDefault();
    transform((current) => ({
      ...current,
      investigations: showRawEditor
        ? current.investigations
        : serializeInvestigations(coreValues, extraTests),
      next_step: step,
    }));
    put(`/admissions/${admission.id}/test-reports`);
  };

  const handleSaveOnly = (e: React.FormEvent) => submitStage('stay', e);

  const handleSaveAndNext = (e: React.FormEvent) => submitStage('surgery', e);

  return (
    <AppLayout>
      <Head title={`Stage 2: Lab Results — ${admission.patient_name}`} />

      {/* 4-Stage Navigation Header */}
      <AdmissionStageNav currentStage="test-reports" admission={admission} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 2: Laboratory &amp; Diagnostic Test Reports
          </h1>
          <p className="text-muted mb-0">
            Standard laboratory investigations are predefined below. Simply enter the patient&rsquo;s test values.
          </p>
        </div>
        <div className="d-flex gap-2">
          <Link href={`/admissions/${admission.id}/edit`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 1: Admission
          </Link>
          <button
            type="button"
            onClick={toggleRawEditor}
            className="btn btn-outline-secondary"
          >
            <i className={`bi bi-${showRawEditor ? 'table' : 'code-slash'} me-1`}></i>
            {showRawEditor ? 'Show Values Form' : 'Show Raw Text View'}
          </button>
        </div>
      </div>

      <form id="testReportsForm">
        {/* Core Hospital Lab Results Form (Values Only) */}
        {!showRawEditor ? (
          <div className="card mb-4 border-0 shadow-sm">
            <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div className="d-flex align-items-center gap-2">
                <span className="badge bg-primary">2.1</span>
                <span>Hospital Core Lab Investigations (Enter Values Only)</span>
              </div>
              <button
                type="button"
                onClick={setAllViralNegative}
                className="btn btn-sm btn-outline-success fw-semibold shadow-xs"
              >
                <i className="bi bi-shield-check me-1"></i> Set All Viral Markers (HIV, HCV, VDRL, HBsAg) Negative
              </button>
            </div>
            <div className="card-body">
              {/* Row 1: Hematology & Blood Group */}
              <div className="p-3 bg-light rounded-3 mb-3 border">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-droplet-half text-danger"></i>
                    <span>Hematology &amp; Blood Grouping</span>
                  </h6>
                  <span className="badge bg-white text-secondary border small fw-medium">
                    Standard Pre-Op Workup
                  </span>
                </div>
                <div className="row g-3">
                  <div className="col-md-6">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Haemoglobin (Hb)
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.72rem' }}>
                        Unit: <strong>g/dL</strong> &bull; Ref: <strong>12.0 – 16.0 g/dL</strong>
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '90px' }}>
                        Hb :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.hb}
                        onChange={(e) => updateCore('hb', e.target.value)}
                        placeholder="Enter value (e.g. 12.9 g/dL)"
                      />
                    </div>
                  </div>

                  <div className="col-md-6">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Blood Group &amp; Rh (BG)
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.72rem' }}>
                        ABO &amp; Rh Compatibility
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '90px' }}>
                        BG :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.bg}
                        onChange={(e) => updateCore('bg', e.target.value)}
                        placeholder="Enter value or select (e.g. O positive)"
                        list="bgOptions"
                      />
                      <datalist id="bgOptions">
                        <option value="O positive" />
                        <option value="A positive" />
                        <option value="B positive" />
                        <option value="AB positive" />
                        <option value="O negative" />
                        <option value="A negative" />
                        <option value="B negative" />
                        <option value="AB negative" />
                      </datalist>
                    </div>
                  </div>
                </div>
              </div>

              {/* Row 2: Viral Serology Screen */}
              <div className="p-3 bg-light rounded-3 mb-3 border">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-virus text-primary"></i>
                    <span>Viral &amp; Infectious Disease Screening</span>
                  </h6>
                  <div className="d-flex align-items-center gap-2">
                    <span className="badge bg-white text-muted border small d-none d-md-inline">
                      Ref: Non-Reactive / Negative
                    </span>
                    <button
                      type="button"
                      onClick={setAllViralNegative}
                      className="btn btn-xs btn-outline-success fw-semibold"
                      style={{ fontSize: '0.78rem' }}
                    >
                      <i className="bi bi-check2-all me-1"></i> Quick: All Negative
                    </button>
                  </div>
                </div>
                <div className="row g-3">
                  <div className="col-md-3">
                    <label className="form-label fw-semibold text-secondary small text-uppercase">HCV</label>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '70px' }}>
                        HCV :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.hcv}
                        onChange={(e) => updateCore('hcv', e.target.value)}
                        placeholder="Negative"
                        list="viralOptions"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <label className="form-label fw-semibold text-secondary small text-uppercase">HIV</label>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '70px' }}>
                        HIV :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.hiv}
                        onChange={(e) => updateCore('hiv', e.target.value)}
                        placeholder="Negative"
                        list="viralOptions"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <label className="form-label fw-semibold text-secondary small text-uppercase">VDRL</label>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '70px' }}>
                        VDRL :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.vdrl}
                        onChange={(e) => updateCore('vdrl', e.target.value)}
                        placeholder="Negative"
                        list="viralOptions"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <label className="form-label fw-semibold text-secondary small text-uppercase">HBsAg</label>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '70px' }}>
                        HBsAg :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.hbsag}
                        onChange={(e) => updateCore('hbsag', e.target.value)}
                        placeholder="Negative"
                        list="viralOptions"
                      />
                    </div>
                  </div>

                  <datalist id="viralOptions">
                    <option value="Negative" />
                    <option value="Non-Reactive" />
                    <option value="Positive" />
                  </datalist>
                </div>
              </div>

              {/* Row 3: Biochemistry & Renal Function */}
              <div className="p-3 bg-light rounded-3 mb-3 border">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-activity text-success"></i>
                    <span>Biochemistry, Renal Function &amp; Blood Sugar</span>
                  </h6>
                  <span className="badge bg-white text-secondary border small fw-medium">
                    Standard Units: mg/dL
                  </span>
                </div>
                <div className="row g-3">
                  <div className="col-md-3">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Blood Urea
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        Ref: 15–40
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '75px' }}>
                        Urea :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.urea}
                        onChange={(e) => updateCore('urea', e.target.value)}
                        placeholder="e.g. 20 mg/dL"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Serum Creatinine
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        Ref: 0.6–1.2
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '75px' }}>
                        Creat :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.creatinine}
                        onChange={(e) => updateCore('creatinine', e.target.value)}
                        placeholder="e.g. 0.86 mg/dL"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Serum Uric Acid
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        Ref: 3.5–7.2
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '75px' }}>
                        Uric :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.uric_acid}
                        onChange={(e) => updateCore('uric_acid', e.target.value)}
                        placeholder="e.g. 4.3 mg/dL"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        RBS (Random Sugar)
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        Ref: 70–140
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '75px' }}>
                        RBS :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.rbs}
                        onChange={(e) => updateCore('rbs', e.target.value)}
                        placeholder="e.g. 100.8 mg/dL"
                      />
                    </div>
                  </div>
                </div>
              </div>

              {/* Row 4: Cardiac & Pre-Operative Diagnostic Evaluation (ECG) */}
              <div className="p-3 bg-light rounded-3 mb-3 border">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-heart-pulse text-danger"></i>
                    <span>Cardiac &amp; Pre-Operative Diagnostic Evaluation (ECG)</span>
                  </h6>
                  <span className="badge bg-white text-secondary border small fw-medium">
                    Ref: Normal Sinus Rhythm (NSR)
                  </span>
                </div>
                <div className="row g-3">
                  <div className="col-md-6">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        12-Lead Electrocardiogram (ECG)
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        12-Lead Standard Tracing
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '90px' }}>
                        ECG :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.ecg}
                        onChange={(e) => updateCore('ecg', e.target.value)}
                        placeholder="e.g. Normal Sinus Rhythm or WNL"
                        list="ecgOptions"
                      />
                      <datalist id="ecgOptions">
                        <option value="Normal Sinus Rhythm" />
                        <option value="Within Normal Limits (WNL)" />
                        <option value="Normal ECG" />
                        <option value="Sinus Bradycardia" />
                        <option value="Sinus Tachycardia" />
                        <option value="Incomplete RBBB" />
                        <option value="Non-specific ST-T changes" />
                      </datalist>
                    </div>
                  </div>

                  <div className="col-md-6 d-flex flex-column justify-content-end">
                    <div className="small text-muted mb-1.5 fw-semibold">Quick Select ECG Presets:</div>
                    <div className="d-flex flex-wrap gap-2">
                      <button
                        type="button"
                        onClick={() => updateCore('ecg', 'Normal Sinus Rhythm')}
                        className={`btn btn-xs ${coreValues.ecg === 'Normal Sinus Rhythm' ? 'btn-success text-white' : 'btn-outline-secondary'}`}
                        style={{ fontSize: '0.78rem' }}
                      >
                        <i className="bi bi-check2 me-1"></i> Normal Sinus Rhythm
                      </button>
                      <button
                        type="button"
                        onClick={() => updateCore('ecg', 'Within Normal Limits (WNL)')}
                        className={`btn btn-xs ${coreValues.ecg === 'Within Normal Limits (WNL)' ? 'btn-success text-white' : 'btn-outline-secondary'}`}
                        style={{ fontSize: '0.78rem' }}
                      >
                        WNL
                      </button>
                      <button
                        type="button"
                        onClick={() => updateCore('ecg', 'Normal ECG')}
                        className={`btn btn-xs ${coreValues.ecg === 'Normal ECG' ? 'btn-success text-white' : 'btn-outline-secondary'}`}
                        style={{ fontSize: '0.78rem' }}
                      >
                        Normal ECG
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              {/* Row 5: Additional / Custom Tests & Imaging */}
              <div className="p-3 bg-light rounded-3 border">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-file-earmark-medical text-info"></i>
                    <span>Additional Lab Tests / Imaging / Urine Reports</span>
                  </h6>
                  <button
                    type="button"
                    onClick={() => addExtraTest()}
                    className="btn btn-sm btn-outline-primary"
                  >
                    <i className="bi bi-plus-circle me-1"></i> Add Other Test
                  </button>
                </div>

                {extraTests.length === 0 ? (
                  <div className="text-muted small py-2">
                    No additional test reports added. Standard pre-op panel (including ECG) above is fully active.
                  </div>
                ) : (
                  extraTests.map((t, idx) => (
                    <div key={idx} className="input-group mb-2">
                      <input
                        type="text"
                        className="form-control"
                        style={{ maxWidth: '280px' }}
                        value={t.name}
                        onChange={(e) => updateExtraTest(idx, 'name', e.target.value)}
                        placeholder="Test Name (e.g. Urine Albumin / USG / ECG)"
                      />
                      <span className="input-group-text bg-white">:</span>
                      <input
                        type="text"
                        className="form-control"
                        value={t.value}
                        onChange={(e) => updateExtraTest(idx, 'value', e.target.value)}
                        placeholder="Result value only (e.g. Nil / Pus cells 1-2 / Normal Sinus)"
                      />
                      <button
                        type="button"
                        onClick={() => removeExtraTest(idx)}
                        className="btn btn-outline-danger"
                        title="Remove Test"
                      >
                        <i className="bi bi-trash"></i>
                      </button>
                    </div>
                  ))
                )}
              </div>
            </div>
          </div>
        ) : (
          /* Raw Text Area (Advanced Mode) */
          <div className="card mb-4 border-0 shadow-sm">
            <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
              <div>
                <span className="badge bg-secondary me-2">RAW</span>
                <span>Direct Raw Text Editor</span>
              </div>
              <button
                type="button"
                onClick={() => setShowRawEditor(false)}
                className="btn btn-sm btn-primary"
              >
                Switch Back to Values Form
              </button>
            </div>
            <div className="card-body">
              <textarea
                className="form-control font-monospace"
                rows={12}
                value={data.investigations}
                onChange={(e) => handleRawChange(e.target.value)}
                style={{ fontSize: '0.92rem', lineHeight: '1.6' }}
              />
            </div>
          </div>
        )}

        {/* Live Document Print Preview */}
        <div className="card mb-4 border-0 shadow-sm border-start border-4 border-primary">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex justify-content-between align-items-center">
            <span className="small text-uppercase tracking-wide text-secondary">
              <i className="bi bi-printer me-1"></i> Discharge Summary Print Preview (INVESTIGATIONS Section)
            </span>
            <span className="badge bg-light text-dark border">Auto-Formatted Grid</span>
          </div>
          <div className="card-body bg-white p-3">
            <div className="table-responsive">
              <table className="table table-bordered table-sm text-center mb-0 align-middle" style={{ fontSize: '0.86rem' }}>
                <thead className="table-dark">
                  <tr>
                    <th>HB</th>
                    <th>BLOOD GROUP</th>
                    <th>HCV</th>
                    <th>HIV</th>
                    <th>VDRL</th>
                  </tr>
                </thead>
                <tbody>
                  <tr className="bg-light fw-bold text-dark">
                    <td>{coreValues.hb || '—'}</td>
                    <td>{coreValues.bg || '—'}</td>
                    <td>{coreValues.hcv || '—'}</td>
                    <td>{coreValues.hiv || '—'}</td>
                    <td>{coreValues.vdrl || '—'}</td>
                  </tr>
                </tbody>
                <thead className="table-dark">
                  <tr>
                    <th>HBSAG</th>
                    <th>UREA</th>
                    <th>CREATININE</th>
                    <th>URIC ACID</th>
                    <th>RBS</th>
                  </tr>
                </thead>
                <tbody>
                  <tr className="bg-light fw-bold text-dark">
                    <td>{coreValues.hbsag || '—'}</td>
                    <td>{coreValues.urea || '—'}</td>
                    <td>{coreValues.creatinine || '—'}</td>
                    <td>{coreValues.uric_acid || '—'}</td>
                    <td>{coreValues.rbs || '—'}</td>
                  </tr>
                </tbody>
              </table>

              {extraTests.length > 0 && (
                <div className="mt-2 pt-2 border-top">
                  <div className="small fw-bold text-secondary mb-1">Additional Findings:</div>
                  <div className="d-flex flex-wrap gap-2">
                    {extraTests.map((t, idx) => (
                      <span key={idx} className="badge bg-light text-dark border py-1.5 px-2">
                        <strong>{t.name}:</strong> {t.value}
                      </span>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Action Bar */}
        <div className="d-flex justify-content-between align-items-center pb-5">
          <Link href={`/admissions/${admission.id}/edit`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 1: Admission
          </Link>
          <div className="d-flex gap-2">
            <button
              type="button"
              onClick={handleSaveOnly}
              disabled={processing}
              className="btn btn-outline-primary px-3 py-2 fw-semibold"
            >
              Save Stage 2 Changes
            </button>
            <button
              type="button"
              onClick={handleSaveAndNext}
              disabled={processing}
              className="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
            >
              <span>Save &amp; Next: Stage 3 (Labour / Surgery)</span>
              <i className="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}
