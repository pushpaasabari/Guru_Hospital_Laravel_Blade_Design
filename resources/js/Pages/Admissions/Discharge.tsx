import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';
import AdmissionStageNav from '../../Components/AdmissionStageNav';
import PaymentInsuranceFields, { normalizePaymentMode } from '../../Components/PaymentInsuranceFields';

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
  admission_at: string;
  procedure_at?: string | null;
  procedure_name?: string | null;
  procedure_done?: string | null;
  procedure_note?: string | null;
  findings?: string | null;
  drain?: string | null;
  discharge_at?: string | null;
  discharge_condition?: string | null;
  clinical_history?: string | null;
  examination?: string | null;
  treatment_course?: string | null;
  medication_orders?: string | null;
  discharge_advice?: string | null;
  follow_up?: string | null;
  mrd_no?: string | null;
  payment_mode?: string | null;
  insurance_type?: string | null;
  insurance_company?: string | null;
  policy_number?: string | null;
  pre_auth_no?: string | null;
  pre_auth_status?: string | null;
  approved_amount?: string | number | null;
  policy_holder_name?: string | null;
  policy_holder_relation?: string | null;
  policy_validity?: string | null;
  handover_name?: string | null;
  floor_incharge_name?: string | null;
  mrd_incharge_name?: string | null;
  status: string;
}

interface Props {
  admission: AdmissionData;
  clinicalStaff?: ClinicalStaffMember[];
}

interface MedicationRow {
  medicine: string;
  frequency: string;
  duration: string;
  timing: string;
}

const COMMON_MEDICATIONS = [
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
  'Inj. Pan 40',
];

const FREQUENCY_OPTIONS = [
  { value: '1-0-1', label: '1-0-1 (Twice daily / Morning & Night)' },
  { value: '1-0-0', label: '1-0-0 (Once daily / Morning)' },
  { value: '0-0-1', label: '0-0-1 (Once daily / Night)' },
  { value: '1-1-1', label: '1-1-1 (Thrice daily / Morning, Noon & Night)' },
  { value: '0-1-0', label: '0-1-0 (Once daily / Noon)' },
  { value: '1-0-1-0', label: '1-0-1-0 (Twice daily)' },
  { value: '1-1-1-1', label: '1-1-1-1 (Four times daily)' },
  { value: 'SOS', label: 'SOS (Whenever needed / in pain)' },
  { value: 'Stat', label: 'Stat (Single immediate dose)' },
];

const DURATION_OPTIONS = [
  '7 Days',
  '5 Days',
  '3 Days',
  '10 Days',
  '14 Days',
  '1 Month',
  '6 Nos',
  '9 Nos',
  'SOS',
];

const TIMING_OPTIONS = [
  'After food',
  'Before food',
  'With food',
  'At bedtime',
  'Empty stomach',
  'As directed',
];

const parseMedications = (raw?: string | null): MedicationRow[] => {
  if (!raw || !raw.trim()) {
    return [
      { medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' },
    ];
  }

  if (raw.trim().startsWith('[') && raw.trim().endsWith(']')) {
    try {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed.map((item) => ({
          medicine: item.medicine || item.name || '',
          frequency: item.frequency || item.freq || '1-0-1',
          duration: item.duration || item.dur || '7 Days',
          timing: item.timing || 'After food',
        }));
      }
    } catch (e) {
      // fallback
    }
  }

  const lines = raw.split(/\r?\n/).map((l) => l.trim()).filter(Boolean);
  const rows: MedicationRow[] = [];

  for (let line of lines) {
    line = line.replace(/^[\*•\-\d\.]+\s*/, '').trim();
    if (!line) continue;

    let frequency = '1-0-1';
    let duration = '7 Days';
    let timing = 'After food';

    const fm = line.match(/\(([\d\-]+)\)/) || line.match(/\b(\d-\d-\d(?:-\d)?|SOS|Stat)\b/i);
    if (fm) {
      frequency = fm[1];
      line = line.replace(fm[0], ' ');
    }

    const tm = line.match(/(After\s+food|Before\s+food|With\s+food|At\s+bedtime|Bedtime|Empty\s+stomach)/i);
    if (tm) {
      timing = tm[1];
      line = line.replace(tm[0], ' ');
    }

    const dm = line.match(/(?:x\s*|for\s*)?(\d+\s*(?:days|weeks|months|day|nos))/i);
    if (dm) {
      duration = dm[1];
      line = line.replace(dm[0], ' ');
    }

    let med = line.replace(/[-–x\(\)]+/g, ' ').replace(/\s+/g, ' ').trim();
    if (med) {
      rows.push({
        medicine: med,
        frequency,
        duration,
        timing,
      });
    }
  }

  return rows.length > 0 ? rows : [{ medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' }];
};

const serializeMedications = (rows: MedicationRow[]): string => {
  return rows
    .filter((r) => r.medicine.trim())
    .map((r) => `* ${r.medicine.trim()} (${r.frequency || '1-0-1'}) x ${r.duration || '7 Days'} - ${r.timing || 'After food'}`)
    .join('\n');
};

const getStentNoticeText = (date: string) => {
  const d = date || '(Date)';
  return `வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு ${d} அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.`;
};

export default function AdmissionsDischarge({ admission, clinicalStaff = [] }: Props) {
  // Stent detection
  const hasStentInSurgery = Boolean(
    (admission.procedure_name || '').match(/stent/i) ||
    (admission.procedure_done || '').match(/stent/i) ||
    (admission.procedure_note || '').match(/stent/i) ||
    (admission.findings || '').match(/stent/i) ||
    (admission.drain || '').match(/stent/i) ||
    (admission.discharge_advice || '').match(/stent/i) ||
    (admission.follow_up || '').match(/stent/i)
  );

  const initialMedRows = parseMedications(admission.medication_orders);
  const [medRows, setMedRows] = useState<MedicationRow[]>(initialMedRows);
  const [hasStent, setHasStent] = useState<boolean>(hasStentInSurgery);

  // Extract stent date
  const extractDate = (text?: string | null): string => {
    if (!text) return '';
    const m = text.match(/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/);
    return m ? m[1] : '';
  };

  const [stentRemovalDate, setStentRemovalDate] = useState<string>(
    extractDate(admission.follow_up || admission.discharge_advice)
  );

  const { data, setData, put, processing, errors } = useForm({
    discharge_at: admission.discharge_at ? admission.discharge_at.slice(0, 16) : '',
    discharge_condition: admission.discharge_condition || '',
    clinical_history: admission.clinical_history || '',
    examination: admission.examination || '',
    treatment_course: admission.treatment_course || '',
    medication_orders: admission.medication_orders || serializeMedications(initialMedRows),
    discharge_advice: admission.discharge_advice || '',
    follow_up: admission.follow_up || '',
    mrd_no: admission.mrd_no || (admission.uhid ? (admission.uhid.startsWith('MRD-') ? admission.uhid : `MRD-${admission.uhid}`) : ''),
    payment_mode: normalizePaymentMode(admission.payment_mode),
    insurance_type: admission.insurance_type || '',
    insurance_company: admission.insurance_company || '',
    policy_number: admission.policy_number || '',
    pre_auth_no: admission.pre_auth_no || '',
    pre_auth_status: admission.pre_auth_status || '',
    approved_amount: admission.approved_amount ?? '',
    policy_holder_name: admission.policy_holder_name || '',
    policy_holder_relation: admission.policy_holder_relation || '',
    policy_validity: admission.policy_validity ? String(admission.policy_validity).slice(0, 10) : '',
    handover_name: admission.handover_name || '',
    floor_incharge_name: admission.floor_incharge_name || '',
    mrd_incharge_name: admission.mrd_incharge_name || '',
    next_step: 'preview',
  });

  const syncMeds = (rows: MedicationRow[]) => {
    setMedRows(rows);
    setData('medication_orders', serializeMedications(rows));
  };

  const updateMedRow = (idx: number, field: keyof MedicationRow, val: string) => {
    const updated = [...medRows];
    updated[idx] = { ...updated[idx], [field]: val };
    syncMeds(updated);
  };

  const addMedRow = (row?: MedicationRow) => {
    const newRow = row || { medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' };
    syncMeds([...medRows, newRow]);
  };

  const removeMedRow = (idx: number) => {
    const updated = medRows.filter((_, i) => i !== idx);
    syncMeds(updated.length > 0 ? updated : [{ medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' }]);
  };

  const moveMedRow = (idx: number, dir: 'up' | 'down') => {
    const target = dir === 'up' ? idx - 1 : idx + 1;
    if (target < 0 || target >= medRows.length) return;
    const updated = [...medRows];
    const [moved] = updated.splice(idx, 1);
    updated.splice(target, 0, moved);
    syncMeds(updated);
  };

  const applyMedPack = (pack: 'anitha' | 'delivery' | 'ursl') => {
    let rows: MedicationRow[] = [];
    if (pack === 'anitha') {
      rows = [
        { medicine: 'Tab. Cefixime + Sulbactam', frequency: '1-0-1', duration: '7 Days', timing: 'After food' },
        { medicine: 'Tab. Acendol-P', frequency: '1-0-1', duration: '7 Days', timing: 'After food' },
        { medicine: 'Cap. Cyra-D', frequency: '1-0-1', duration: '7 Days', timing: 'Before food' },
      ];
    } else if (pack === 'delivery') {
      rows = [
        { medicine: 'Tab. Clavam 625mg', frequency: '1-0-1', duration: '5 Days', timing: 'After food' },
        { medicine: 'Tab. Paracetamol 650mg', frequency: '1-1-1', duration: '3 Days', timing: 'After food' },
        { medicine: 'Cap. Cyra-D', frequency: '1-0-1', duration: '5 Days', timing: 'Before food' },
        { medicine: 'Tab. Iron & Folic Acid', frequency: '1-0-0', duration: '30 Days', timing: 'After food' },
      ];
    } else if (pack === 'ursl') {
      rows = [
        { medicine: 'Tab. Niftas 100mg', frequency: '1-0-1', duration: '6 Nos', timing: 'After food' },
        { medicine: 'Tab. Paracetamol 650mg', frequency: '1-1-1', duration: '9 Nos', timing: 'After food' },
        { medicine: 'Cap. Cyra-D', frequency: '1-0-1', duration: '6 Nos', timing: 'Before food' },
        { medicine: 'Tab. Zerodol-P', frequency: '1-0-1', duration: '6 Nos', timing: 'After food' },
      ];
    }
    syncMeds(rows);
  };

  const insertTamilStentNotice = () => {
    const notice = getStentNoticeText(stentRemovalDate);
    if (!data.discharge_advice.includes('வயிற்றுக்குள்')) {
      const updated = data.discharge_advice ? `${data.discharge_advice.trim()}\n${notice}` : notice;
      setData('discharge_advice', updated);
    }
    if (!data.follow_up.includes('stent removal')) {
      const followUpUpdated = data.follow_up
        ? `${data.follow_up.trim()}\n${stentRemovalDate} for stent removal`
        : `${stentRemovalDate} for stent removal`;
      setData('follow_up', followUpUpdated);
    }
  };

  const submitWithStep = (step: 'download_ds' | 'download_pack' | 'preview' | 'stay', e?: React.FormEvent) => {
    if (e) e.preventDefault();
    setData('next_step', step);
    put(`/admissions/${admission.id}/discharge`, {
      data: {
        ...data,
        next_step: step,
      },
    });
  };

  const handleSaveOnly = (e: React.FormEvent) => {
    submitWithStep('stay', e);
  };

  const handleSaveAndPreview = (e: React.FormEvent) => {
    submitWithStep('preview', e);
  };

  const handleSaveAndDownloadDs = (e: React.FormEvent) => {
    submitWithStep('download_ds', e);
  };

  const handleSaveAndDownloadPack = (e: React.FormEvent) => {
    submitWithStep('download_pack', e);
  };

  return (
    <AppLayout>
      <Head title={`Stage 4: Discharge Summary — ${admission.patient_name}`} />

      {/* 4-Stage Navigation Header */}
      <AdmissionStageNav currentStage="discharge" admission={admission} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 4: Discharge Summary &amp; MRD Records
          </h1>
          <p className="text-muted mb-0">
            Finalize discharge timing, condition at discharge, clinical history, medications, advice, and MRD signoffs.
          </p>
        </div>
        <div className="d-flex gap-2">
          <Link href={`/admissions/${admission.id}/test-reports`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 3: Test Reports
          </Link>
          <Link href={`/admissions/${admission.id}/documents/preview`} className="btn btn-outline-secondary">
            <i className="bi bi-file-earmark-text me-1"></i> Preview Documents
          </Link>
        </div>
      </div>

      <form id="dischargeForm">
        {/* Section 1: Discharge Timing & Condition */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
            <div>
              <span className="badge bg-primary me-2">4.1</span> Discharge Details &amp; Patient Condition
            </div>
            <span className="text-muted small">Step 4 of 4</span>
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label required">Discharge Date &amp; Time</label>
                <input
                  type="datetime-local"
                  className={`form-control ${errors.discharge_at ? 'is-invalid' : ''}`}
                  value={data.discharge_at}
                  onChange={(e) => setData('discharge_at', e.target.value)}
                  required
                />
                {errors.discharge_at && <div className="invalid-feedback">{errors.discharge_at}</div>}
              </div>

              <div className="col-md-8">
                <label className="form-label">Condition at Discharge</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.discharge_condition}
                  onChange={(e) => setData('discharge_condition', e.target.value)}
                  placeholder="e.g. Pt’s general condition good. BP: 110/70mmHg, Pulse: 86 b/mt, P/A: Uterus contracted well."
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 2: Clinical Narrative */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">4.2</span> Clinical History &amp; Course in Hospital
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label">Presenting Complaints / Clinical History</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.clinical_history}
                  onChange={(e) => setData('clinical_history', e.target.value)}
                  placeholder="e.g. G3P1L1A1 / TWINS / EDD 22-9-2026 / admitted for safe confinement. C/O abdominal Pain"
                />
              </div>

              <div className="col-md-6">
                <label className="form-label">Clinical Examination</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.examination}
                  onChange={(e) => setData('examination', e.target.value)}
                  placeholder="e.g. O/E – Afebrile, No Pallor, No Pedal edema. CVS & RS – NAD. P/A: Uterus Overdistended, Both FH good."
                />
              </div>

              <div className="col-12">
                <label className="form-label">Course in Hospital &amp; Treatment Given</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.treatment_course}
                  onChange={(e) => setData('treatment_course', e.target.value)}
                  placeholder="e.g. Treated with IV Fluids&#10;Inj-Pipzo 4.5g BD IV&#10;Inj-Metrogyl 100mg BD&#10;Inj-Pan 40 IV BD"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 3: Structured Medication Orders & Stent Advice */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <span className="badge bg-primary me-2">4.3</span> Discharge Medication Orders (Dropdown &amp; Timing)
            </div>
            <div className="d-flex gap-1.5 flex-wrap">
              <span className="small text-muted me-1 align-self-center">Prescription Packs:</span>
              <button
                type="button"
                onClick={() => applyMedPack('anitha')}
                className="btn btn-xs btn-outline-primary"
                style={{ fontSize: '0.78rem' }}
              >
                + Cefixime + Acendol-P + Cyra-D
              </button>
              <button
                type="button"
                onClick={() => applyMedPack('delivery')}
                className="btn btn-xs btn-outline-secondary"
                style={{ fontSize: '0.78rem' }}
              >
                + Delivery Routine
              </button>
              <button
                type="button"
                onClick={() => applyMedPack('ursl')}
                className="btn btn-xs btn-outline-secondary"
                style={{ fontSize: '0.78rem' }}
              >
                + URSL Surgery Pack
              </button>
            </div>
          </div>
          <div className="card-body">
            {/* Medications Table Repeater */}
            <div className="table-responsive mb-3">
              <table className="table table-bordered align-middle mb-2">
                <thead className="table-light">
                  <tr style={{ fontSize: '0.86rem' }}>
                    <th style={{ width: '4%' }}>#</th>
                    <th style={{ width: '40%' }}>Medication (Dropdown / Type Name)</th>
                    <th style={{ width: '18%' }}>Frequency</th>
                    <th style={{ width: '16%' }}>Duration</th>
                    <th style={{ width: '16%' }}>Timing</th>
                    <th style={{ width: '6%', textAlign: 'center' }}>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {medRows.map((med, idx) => (
                    <tr key={idx}>
                      <td className="fw-bold text-secondary text-center">{idx + 1}</td>
                      <td>
                        <input
                          type="text"
                          className="form-control form-control-sm"
                          value={med.medicine}
                          onChange={(e) => updateMedRow(idx, 'medicine', e.target.value)}
                          placeholder="Select or enter medicine..."
                          list={`medList-${idx}`}
                        />
                        <datalist id={`medList-${idx}`}>
                          {COMMON_MEDICATIONS.map((m, mIdx) => (
                            <option key={mIdx} value={m} />
                          ))}
                        </datalist>
                      </td>
                      <td>
                        <select
                          className="form-select form-select-sm"
                          value={med.frequency}
                          onChange={(e) => updateMedRow(idx, 'frequency', e.target.value)}
                        >
                          {FREQUENCY_OPTIONS.map((f, fIdx) => (
                            <option key={fIdx} value={f.value}>
                              {f.label}
                            </option>
                          ))}
                        </select>
                      </td>
                      <td>
                        <input
                          type="text"
                          className="form-control form-control-sm"
                          value={med.duration}
                          onChange={(e) => updateMedRow(idx, 'duration', e.target.value)}
                          placeholder="7 Days"
                          list={`durList-${idx}`}
                        />
                        <datalist id={`durList-${idx}`}>
                          {DURATION_OPTIONS.map((d, dIdx) => (
                            <option key={dIdx} value={d} />
                          ))}
                        </datalist>
                      </td>
                      <td>
                        <select
                          className="form-select form-select-sm"
                          value={med.timing}
                          onChange={(e) => updateMedRow(idx, 'timing', e.target.value)}
                        >
                          {TIMING_OPTIONS.map((t, tIdx) => (
                            <option key={tIdx} value={t}>
                              {t}
                            </option>
                          ))}
                        </select>
                      </td>
                      <td className="text-center">
                        <div className="btn-group btn-group-sm">
                          <button
                            type="button"
                            onClick={() => moveMedRow(idx, 'up')}
                            disabled={idx === 0}
                            className="btn btn-outline-secondary"
                            title="Move Up"
                          >
                            <i className="bi bi-arrow-up"></i>
                          </button>
                          <button
                            type="button"
                            onClick={() => removeMedRow(idx)}
                            disabled={medRows.length <= 1 && !med.medicine.trim()}
                            className="btn btn-outline-danger"
                            title="Remove Row"
                          >
                            <i className="bi bi-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
              <div className="text-end">
                <button
                  type="button"
                  onClick={() => addMedRow()}
                  className="btn btn-sm btn-primary fw-semibold"
                >
                  <i className="bi bi-plus-circle me-1"></i> Add Medication Row
                </button>
              </div>
            </div>

            {/* Live 4-Column Table Print Preview */}
            <div className="p-3 bg-light rounded-3 mb-4 border">
              <div className="fw-bold text-dark small text-uppercase mb-2">
                <i className="bi bi-eye me-1"></i> Document Print Preview: 4-Column Medication Table
              </div>
              <table className="table table-sm table-bordered bg-white mb-0" style={{ fontSize: '0.84rem' }}>
                <thead className="table-dark">
                  <tr>
                    <th style={{ width: '44%' }}>Medicine</th>
                    <th style={{ width: '18%' }}>Frequency</th>
                    <th style={{ width: '18%' }}>Duration</th>
                    <th style={{ width: '20%' }}>Timing</th>
                  </tr>
                </thead>
                <tbody>
                  {medRows
                    .filter((m) => m.medicine.trim())
                    .map((m, idx) => (
                      <tr key={idx}>
                        <td className="fw-semibold">{m.medicine}</td>
                        <td>{m.frequency}</td>
                        <td>{m.duration}</td>
                        <td>{m.timing}</td>
                      </tr>
                    ))}
                </tbody>
              </table>
            </div>

            {/* Stent Placement Detection & Mandatory Tamil Instruction */}
            <div className="card mb-4 border-warning bg-warning-subtle">
              <div className="card-body p-3">
                <div className="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                  <div className="form-check form-switch mb-0">
                    <input
                      className="form-check-input"
                      type="checkbox"
                      role="switch"
                      id="stentDischargeSwitch"
                      checked={hasStent}
                      onChange={(e) => setHasStent(e.target.checked)}
                    />
                    <label className="form-check-label fw-bold text-dark" htmlFor="stentDischargeSwitch">
                      <i className="bi bi-shield-plus text-primary me-1"></i> Patient has STENT (Internal Stent in situ)
                    </label>
                  </div>
                  {hasStent && (
                    <button
                      type="button"
                      onClick={insertTamilStentNotice}
                      className="btn btn-sm btn-warning text-dark fw-bold shadow-xs"
                    >
                      <i className="bi bi-pencil-square me-1"></i> Insert Tamil Stent Instruction into Advice
                    </button>
                  )}
                </div>

                {hasStent && (
                  <div className="p-3 bg-white rounded-3 border mt-2">
                    <div className="row g-2 align-items-center mb-2">
                      <div className="col-auto">
                        <label className="form-label fw-bold small text-dark mb-0">Stent Removal Due Date:</label>
                      </div>
                      <div className="col-auto">
                        <input
                          type="text"
                          className="form-control form-control-sm"
                          value={stentRemovalDate}
                          onChange={(e) => setStentRemovalDate(e.target.value)}
                          placeholder="e.g. 21.08.2026"
                          style={{ width: '140px' }}
                        />
                      </div>
                      <div className="col-auto">
                        <span className="text-muted small">(Date to appear in Tamil Stent Notice)</span>
                      </div>
                    </div>

                    <div className="p-2.5 bg-light rounded border" style={{ fontFamily: "'Nirmala', 'Arial Unicode MS', sans-serif", fontSize: '0.92rem', lineHeight: '1.5' }}>
                      <strong className="text-primary d-block mb-1">
                        <i className="bi bi-info-circle-fill me-1"></i> அதிகாரப்பூர்வ ஸ்டெண்ட் அகற்றல் குறிப்பு (Official Stent Notice):
                      </strong>
                      <span className="text-dark fw-semibold">
                        &ldquo;{getStentNoticeText(stentRemovalDate)}&rdquo;
                      </span>
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Advice and Follow-Up */}
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label fw-semibold text-dark">Discharge Advice &amp; Instructions</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.discharge_advice}
                  onChange={(e) => setData('discharge_advice', e.target.value)}
                  placeholder="e.g. Immunisation&#10;Exclusive breast feeding for 6 months&#10;Post Natal Exercise"
                />
              </div>

              <div className="col-md-6">
                <label className="form-label fw-semibold text-dark">Follow-up Advice &amp; Review Date</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.follow_up}
                  onChange={(e) => setData('follow_up', e.target.value)}
                  placeholder="e.g. Review after one week (03/09/2026)"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 4: MRD & Handover */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">4.4</span> Medical Record Department (MRD) &amp; Ward Handover
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <div className="d-flex justify-content-between align-items-center mb-1">
                  <label className="form-label mb-0">MRD File Number</label>
                  <span className="badge bg-light text-muted border" style={{ fontSize: '0.68rem' }}>
                    Auto: MRD-{'{UHID}'}
                  </span>
                </div>
                <input
                  type="text"
                  className="form-control"
                  value={data.mrd_no}
                  onChange={(e) => setData('mrd_no', e.target.value)}
                  placeholder={admission.uhid ? `MRD-${admission.uhid}` : 'e.g. MRD-{UHID}'}
                />
                <div className="form-text text-muted small">
                  Default format: <span className="font-monospace">MRD-{'{UHID}'}</span>
                </div>
              </div>

              <PaymentInsuranceFields
                data={data}
                errors={errors}
                onPatch={(patch) => setData((prev) => ({ ...prev, ...patch }))}
                syncFormat={false}
                label="Payment / Insurance Scheme"
              />

              <div className="col-md-4">
                <StaffSelect
                  label="Handed Over By (Ward Nurse)"
                  role="nurse"
                  value={data.handover_name}
                  onChange={(val) => setData('handover_name', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Ward Nurse --"
                />
              </div>

              <div className="col-md-6">
                <StaffSelect
                  label="Floor In-Charge Sister"
                  role="nurse"
                  value={data.floor_incharge_name}
                  onChange={(val) => setData('floor_incharge_name', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Floor In-Charge --"
                />
              </div>

              <div className="col-md-6">
                <StaffSelect
                  label="MRD In-Charge"
                  role="other"
                  value={data.mrd_incharge_name}
                  onChange={(val) => setData('mrd_incharge_name', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select MRD In-Charge --"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Action Bar */}
        <div className="d-flex justify-content-between align-items-center pb-5">
          <Link href={`/admissions/${admission.id}/test-reports`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 3: Test Reports
          </Link>
          <div className="d-flex flex-wrap gap-2">
            <button
              type="button"
              onClick={handleSaveOnly}
              disabled={processing}
              className="btn btn-outline-secondary px-3 py-2 fw-semibold"
            >
              Save Draft
            </button>
            <button
              type="button"
              onClick={handleSaveAndPreview}
              disabled={processing}
              className="btn btn-outline-primary px-3 py-2 fw-semibold"
            >
              <i className="bi bi-eye me-1"></i> Save &amp; View Dossier
            </button>
            <div className="btn-group shadow-sm">
              <button
                type="button"
                onClick={handleSaveAndDownloadDs}
                disabled={processing}
                className="btn btn-success px-4 py-2 fw-bold d-inline-flex align-items-center gap-2"
              >
                <i className="bi bi-file-earmark-arrow-down-fill fs-5"></i>
                <span>Complete &amp; Download Discharge Summary PDF</span>
              </button>
              <button
                type="button"
                className="btn btn-success dropdown-toggle dropdown-toggle-split"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                disabled={processing}
              >
                <span className="visually-hidden">Toggle PDF Options</span>
              </button>
              <ul className="dropdown-menu dropdown-menu-end shadow border-0" style={{ borderRadius: '12px', fontSize: '13px' }}>
                <li>
                  <button
                    type="button"
                    className="dropdown-item py-2 fw-semibold"
                    onClick={handleSaveAndDownloadDs}
                  >
                    <i className="bi bi-file-earmark-medical text-success me-2"></i>
                    1. Download Discharge Summary PDF
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    className="dropdown-item py-2 fw-semibold"
                    onClick={handleSaveAndDownloadPack}
                  >
                    <i className="bi bi-collection text-primary me-2"></i>
                    Complete 3-in-1 Dossier Pack PDF
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}
