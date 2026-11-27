import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';
import AdmissionStageNav from '../../Components/AdmissionStageNav';
import PaymentInsuranceFields, { normalizePaymentMode } from '../../Components/PaymentInsuranceFields';
import ActionDropdown from '../../Components/ActionDropdown';
import QuickAddDropdownModal, {
  QuickAddDropdownConfig,
  SavedDropdownOption,
} from '../../Components/QuickAddDropdownModal';
import { defaultMrdFromIp, resolveMrdNo } from '../../utils/inputNormalizers';

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
  discharge_bp?: string | null;
  discharge_pulse?: string | null;
  discharge_spo2?: string | null;
  clinical_history?: string | null;
  examination?: string | null;
  treatment_course?: string | null;
  medication_orders?: string | null;
  discharge_advice?: string | null;
  post_surgery_diet?: string | null;
  post_surgery_activity?: string | null;
  follow_up?: string | null;
  suture_removal_date?: string | null;
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
  medicationOptions?: string[];
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

export default function AdmissionsDischarge({
  admission,
  clinicalStaff = [],
  medicationOptions = [],
}: Props) {
  const initialMedRows = parseMedications(admission.medication_orders);
  const [medRows, setMedRows] = useState<MedicationRow[]>(initialMedRows);
  const [savedMedications, setSavedMedications] = useState<string[]>(() => {
    const source = medicationOptions && medicationOptions.length > 0 ? medicationOptions : COMMON_MEDICATIONS;
    const seen = new Set<string>();
    const merged: string[] = [];
    for (const item of [...source, ...COMMON_MEDICATIONS]) {
      const clean = item.trim();
      const key = clean.toLowerCase();
      if (clean && !seen.has(key)) {
        seen.add(key);
        merged.push(clean);
      }
    }
    return merged;
  });
  const [quickAddMedModal, setQuickAddMedModal] = useState<QuickAddDropdownConfig | null>(null);
  const [quickAddTargetRowIdx, setQuickAddTargetRowIdx] = useState<number | null>(null);

  const knownMedicationKeys = new Set(savedMedications.map((m) => m.trim().toLowerCase()));

  const { data, setData, put, transform, processing, errors } = useForm({
    discharge_at: admission.discharge_at ? admission.discharge_at.slice(0, 16) : '',
    discharge_condition: admission.discharge_condition || '',
    discharge_bp: admission.discharge_bp || '',
    discharge_pulse: admission.discharge_pulse || '',
    discharge_spo2: admission.discharge_spo2 || '',
    clinical_history: admission.clinical_history || '',
    examination: admission.examination || '',
    treatment_course: '',
    medication_orders: admission.medication_orders || serializeMedications(initialMedRows),
    discharge_advice: admission.discharge_advice || '',
    post_surgery_diet: admission.post_surgery_diet || '',
    post_surgery_activity: admission.post_surgery_activity || '',
    follow_up: admission.follow_up || '',
    suture_removal_date: admission.suture_removal_date ? String(admission.suture_removal_date).slice(0, 10) : '',
    mrd_no: resolveMrdNo(admission.mrd_no, admission.ip_no),
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

  const updateMedRow = (idx: number, field: keyof MedicationRow, val: string) => {
    setMedRows((prev) => {
      const updated = [...prev];
      updated[idx] = { ...updated[idx], [field]: val };
      return updated;
    });
  };

  const addMedRow = (row?: MedicationRow) => {
    const newRow = row || { medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' };
    setMedRows((prev) => {
      if (row?.medicine?.trim() && prev.length === 1 && !prev[0].medicine.trim()) {
        return [{ ...prev[0], ...row }];
      }
      return [...prev, newRow];
    });
  };

  const openQuickAddMedicineModal = (prefill = '', rowIdx: number | null = null) => {
    setQuickAddTargetRowIdx(rowIdx);
    setQuickAddMedModal({
      type: 'medication',
      title: 'Add New Medicine to Dropdown',
      fieldLabel: 'Medicine Name & Strength',
      placeholder: 'e.g. Tab. Dolo 650mg or Inj. Ceftriaxone 1g',
      helperText: 'Saves to reusable medicine dropdown immediately & persists for future discharges',
      submitLabel: rowIdx !== null ? 'Save to Dropdown' : 'Save & Add Medicine Row',
      initialValue: prefill,
    });
  };

  const handleSavedMedicationOption = (option: SavedDropdownOption) => {
    const clean = option.label.trim();
    if (!clean) return;
    setSavedMedications((prev) => {
      if (prev.some((m) => m.trim().toLowerCase() === clean.toLowerCase())) {
        return prev;
      }
      return [clean, ...prev];
    });

    if (quickAddTargetRowIdx !== null) {
      updateMedRow(quickAddTargetRowIdx, 'medicine', clean);
      setQuickAddTargetRowIdx(null);
      return;
    }

    addMedRow({ medicine: clean, frequency: '1-0-1', duration: '7 Days', timing: 'After food' });
  };

  const removeMedRow = (idx: number) => {
    setMedRows((prev) => {
      const updated = prev.filter((_, i) => i !== idx);
      return updated.length > 0 ? updated : [{ medicine: '', frequency: '1-0-1', duration: '7 Days', timing: 'After food' }];
    });
  };

  const moveMedRow = (idx: number, dir: 'up' | 'down') => {
    const target = dir === 'up' ? idx - 1 : idx + 1;
    if (target < 0 || target >= medRows.length) return;
    setMedRows((prev) => {
      const updated = [...prev];
      const [moved] = updated.splice(idx, 1);
      updated.splice(target, 0, moved);
      return updated;
    });
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
    setMedRows(rows);
  };

  const submitWithStep = (step: 'download_ds' | 'download_pack' | 'preview' | 'stay', e?: React.FormEvent) => {
    if (e) e.preventDefault();
    transform((current) => ({
      ...current,
      medication_orders: serializeMedications(medRows),
      next_step: step,
    }));
    put(`/admissions/${admission.id}/discharge`);
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
      <Head title={`Stage 5: Discharge Summary — ${admission.patient_name}`} />

      {/* 5-Stage Navigation Header */}
      <AdmissionStageNav currentStage="discharge" admission={admission} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 5: Discharge Summary &amp; MRD Records
          </h1>
          <p className="text-muted mb-0">
            Finalize discharge timing, condition at discharge, medications, post-surgery diet &amp; activity, and MRD signoffs.
          </p>
        </div>
        <div className="d-flex gap-2">
          <Link href={`/admissions/${admission.id}/surgery`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 4: {admission.surgery_type === 'medical' ? 'Clinical Notes' : 'Labour / Surgery'}
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
              <span className="badge bg-primary me-2">5.1</span> Discharge Details &amp; Patient Condition
            </div>
            <span className="text-muted small">Step 5 of 5</span>
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-3">
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

              <div className="col-md-3">
                <label className="form-label">General Condition</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.discharge_condition}
                  onChange={(e) => setData('discharge_condition', e.target.value)}
                  placeholder="e.g. Good / Stable / Afebrile"
                />
              </div>

              <div className="col-md-2">
                <label className="form-label">BP (Blood Pressure)</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.discharge_bp}
                  onChange={(e) => setData('discharge_bp', e.target.value)}
                  placeholder="e.g. 110/70 mmHg"
                />
              </div>

              <div className="col-md-2">
                <label className="form-label">Pulse Rate</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.discharge_pulse}
                  onChange={(e) => setData('discharge_pulse', e.target.value)}
                  placeholder="e.g. 84 bpm"
                />
              </div>

              <div className="col-md-2">
                <label className="form-label">SpO2</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.discharge_spo2}
                  onChange={(e) => setData('discharge_spo2', e.target.value)}
                  placeholder="e.g. 99%"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 2: Structured Medication Orders */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <span className="badge bg-primary me-2">5.2</span> Discharge Medication Orders (Dropdown &amp; Timing)
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
            <div className="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div className="d-flex flex-wrap align-items-center gap-2">
                <select
                  className="form-select form-select-sm"
                  style={{ minWidth: '280px', maxWidth: '380px' }}
                  defaultValue=""
                  aria-label="Select Saved Medicine to Add"
                  onChange={(e) => {
                    const selected = e.target.value;
                    if (selected) {
                      addMedRow({ medicine: selected, frequency: '1-0-1', duration: '7 Days', timing: 'After food' });
                      e.target.value = '';
                    }
                  }}
                >
                  <option value="">-- Add Medicine from Saved Dropdown --</option>
                  {savedMedications.map((medName, mIdx) => (
                    <option key={mIdx} value={medName}>
                      {medName}
                    </option>
                  ))}
                </select>
                <button
                  type="button"
                  onClick={() => openQuickAddMedicineModal('', null)}
                  className="btn btn-sm btn-outline-primary fw-semibold"
                >
                  <i className="bi bi-plus-circle me-1"></i> New Medicine
                </button>
              </div>
              <small className="text-muted">
                Any new medicine entered &amp; saved here is remembered automatically in the dropdown next time.
              </small>
            </div>

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
                  {medRows.map((med, idx) => {
                    const trimmedMed = med.medicine.trim();
                    const isNewMed = trimmedMed.length > 0 && !knownMedicationKeys.has(trimmedMed.toLowerCase());
                    return (
                    <tr key={idx}>
                      <td className="fw-bold text-secondary text-center">{idx + 1}</td>
                      <td>
                        <div className="input-group input-group-sm">
                          <input
                            type="text"
                            className="form-control form-control-sm"
                            value={med.medicine}
                            onChange={(e) => updateMedRow(idx, 'medicine', e.target.value)}
                            placeholder="Select or enter medicine..."
                            list="common-medications-list"
                          />
                          {isNewMed && (
                            <button
                              type="button"
                              onClick={() => openQuickAddMedicineModal(trimmedMed, idx)}
                              className="btn btn-outline-success"
                              title="Save this medicine to the dropdown now"
                            >
                              <i className="bi bi-bookmark-plus"></i>
                            </button>
                          )}
                        </div>
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
                          list="common-durations-list"
                        />
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
                    );
                  })}
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
              <datalist id="common-medications-list">
                {savedMedications.map((m, mIdx) => (
                  <option key={mIdx} value={m} />
                ))}
              </datalist>
              <datalist id="common-durations-list">
                {DURATION_OPTIONS.map((d, dIdx) => (
                  <option key={dIdx} value={d} />
                ))}
              </datalist>
            </div>
          </div>
        </div>

        {/* Section 3: Discharge Advice, Post-Surgery Diet/Activity & Review */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">5.3</span> Discharge Advice, Post-Surgery Diet, Activity &amp; Review
          </div>
          <div className="card-body">
            {/* Advice, Post-Surgery Diet & Activity, and Suture Removal */}
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label fw-semibold text-dark">
                  <i className="bi bi-cup-hot me-1 text-primary"></i> Post Surgery Diet
                </label>
                <input
                  type="text"
                  className="form-control"
                  value={data.post_surgery_diet}
                  onChange={(e) => setData('post_surgery_diet', e.target.value)}
                  placeholder="e.g. Normal diet / Soft diet / Diabetic diet"
                  list="dietOptions"
                />
                <datalist id="dietOptions">
                  <option value="Normal diet" />
                  <option value="Soft diet" />
                  <option value="Diabetic diet" />
                  <option value="High protein diet" />
                  <option value="Liquid diet" />
                  <option value="Low salt diet" />
                </datalist>
                <div className="form-text text-muted small">Specify recommended nutritional regimen.</div>
              </div>

              <div className="col-md-6">
                <label className="form-label fw-semibold text-dark">
                  <i className="bi bi-person-walking me-1 text-primary"></i> Post Surgery Activity
                </label>
                <input
                  type="text"
                  className="form-control"
                  value={data.post_surgery_activity}
                  onChange={(e) => setData('post_surgery_activity', e.target.value)}
                  placeholder="e.g. Normal activity / Avoid heavy lifting / Bed rest"
                  list="activityOptions"
                />
                <datalist id="activityOptions">
                  <option value="Normal activity" />
                  <option value="Avoid heavy lifting for 3 months" />
                  <option value="Rest for 1 week" />
                  <option value="Walking as tolerated" />
                  <option value="Post Natal Exercise" />
                </datalist>
                <div className="form-text text-muted small">Specify physical mobility or restrictions.</div>
              </div>

              <div className="col-md-6">
                <label className="form-label fw-semibold text-dark">
                  <i className="bi bi-calendar-check me-1 text-primary"></i> Suture Removal / Review Date
                </label>
                <input
                  type="date"
                  className="form-control fw-bold"
                  value={data.suture_removal_date}
                  onChange={(e) => setData('suture_removal_date', e.target.value)}
                />
                <div className="form-text text-muted small">Rendered in bold in the official discharge summary.</div>
              </div>

              <div className="col-md-6">
                <label className="form-label fw-semibold text-dark">
                  <i className="bi bi-chat-left-dots me-1 text-primary"></i> Follow-up Review Instructions
                </label>
                <input
                  type="text"
                  className="form-control"
                  value={data.follow_up}
                  onChange={(e) => setData('follow_up', e.target.value)}
                  placeholder="e.g. Review after one week for Suture Removal"
                  list="reviewPresets"
                />
                <datalist id="reviewPresets">
                  <option value="Review after one week for Suture Removal" />
                  <option value="Review after 5 days for Suture Removal" />
                  <option value="Review after 10 days" />
                  <option value="Review after 2 weeks" />
                  <option value="Review SOS (as needed)" />
                </datalist>
                <div className="form-text text-muted small">Follow-up schedule for clinical review.</div>
              </div>

              <div className="col-12">
                <label className="form-label fw-semibold text-dark">General Discharge Advice &amp; Instructions</label>
                <textarea
                  className="form-control"
                  rows={3}
                  value={data.discharge_advice}
                  onChange={(e) => setData('discharge_advice', e.target.value)}
                  placeholder="e.g. Exclusive breast feeding for 6 months&#10;Post Natal Exercise&#10;Keep incision site clean and dry"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 4: MRD & Handover */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">5.4</span> Medical Record Department (MRD) &amp; Ward Handover
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <div className="d-flex justify-content-between align-items-center mb-1">
                  <label className="form-label mb-0">MRD File Number</label>
                  <span className="badge bg-light text-muted border" style={{ fontSize: '0.68rem' }}>
                    Auto: MRD-{'{IP No}'}
                  </span>
                </div>
                <input
                  type="text"
                  className="form-control"
                  value={data.mrd_no}
                  onChange={(e) => setData('mrd_no', e.target.value)}
                  placeholder={admission.ip_no ? defaultMrdFromIp(admission.ip_no) : 'e.g. MRD-{IP No}'}
                />
                <div className="form-text text-muted small">
                  Default format: <span className="font-monospace">MRD-{'{IP No}'}</span>
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
                  role="staff_nurse"
                  value={data.handover_name}
                  onChange={(val) => setData('handover_name', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Ward Nurse --"
                />
              </div>

              <div className="col-md-6">
                <StaffSelect
                  label="Floor In-Charge Sister"
                  role="staff_nurse"
                  value={data.floor_incharge_name}
                  onChange={(val) => setData('floor_incharge_name', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Floor In-Charge --"
                />
              </div>

              <div className="col-md-6">
                <StaffSelect
                  label="MRD In-Charge"
                  role="all"
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
          <Link href={`/admissions/${admission.id}/surgery`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 3: {admission.surgery_type === 'medical' ? 'Clinical Notes' : 'Labour / Surgery'}
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
            <ActionDropdown
              groupClassName="btn-group shadow-sm"
              toggleClassName="btn btn-success dropdown-toggle dropdown-toggle-split"
              toggleTitle="Toggle PDF Options"
              fontSize="13px"
              minMenuWidth={270}
              disabled={processing}
              primaryAction={
                <button
                  type="button"
                  onClick={handleSaveAndDownloadDs}
                  disabled={processing}
                  className="btn btn-success px-4 py-2 fw-bold d-inline-flex align-items-center gap-2"
                >
                  <i className="bi bi-file-earmark-arrow-down-fill fs-5"></i>
                  <span>Complete &amp; Download Discharge Summary PDF</span>
                </button>
              }
              items={[
                {
                  type: 'button',
                  label: '1. Download Discharge Summary PDF',
                  iconClass: 'bi bi-file-earmark-medical text-success',
                  className: 'fw-semibold',
                  onClick: () => submitWithStep('download_ds'),
                },
                {
                  type: 'button',
                  label: 'Complete 3-in-1 Dossier Pack PDF',
                  iconClass: 'bi bi-collection text-primary',
                  className: 'fw-semibold',
                  onClick: () => submitWithStep('download_pack'),
                },
              ]}
            />
          </div>
        </div>
      </form>

      <QuickAddDropdownModal
        config={quickAddMedModal}
        onClose={() => {
          setQuickAddMedModal(null);
          setQuickAddTargetRowIdx(null);
        }}
        onSaved={handleSavedMedicationOption}
      />
    </AppLayout>
  );
}
