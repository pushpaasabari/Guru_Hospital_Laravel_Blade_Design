import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';
import { handleNormalizedInputChange, toUpperCaseValue } from '../../utils/inputNormalizers';
import AnesthesiaSelect, { AnesthesiaTypeItem } from '../../Components/AnesthesiaSelect';
import AdmissionStageNav from '../../Components/AdmissionStageNav';

interface Newborn {
  id?: number;
  sex?: string;
  birth_at?: string;
  birth_weight_kg?: string | number;
  apgar_1_min?: number;
  apgar_5_min?: number;
  neonatal_care?: string;
  notes?: string;
}

interface AdmissionData {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  picme_no?: string | null;
  age?: string | null;
  sex?: string | null;
  address?: string | null;
  contact?: string | null;
  admission_at: string;
  procedure_at?: string | null;
  discharge_at?: string | null;
  consultant?: string | null;
  department?: string | null;
  ward_room_no?: string | null;
  format: string;
  surgery_type: string;
  diagnosis: string;
  investigations?: string | null;
  procedure_name?: string | null;
  procedure_done?: string | null;
  icd_code?: string | null;
  surgeon?: string | null;
  assisted_surgeon?: string | null;
  pediatrician_name?: string | null;
  anaesthetist?: string | null;
  assisted_anaesthetist?: string | null;
  assistant?: string | null;
  assisted_by?: string | null;
  staff_nurse?: string | null;
  ot_technician?: string | null;
  anaesthesia_type?: string | null;
  procedure_note?: string | null;
  incision?: string | null;
  findings?: string | null;
  complications?: string | null;
  blood_loss_ml?: string | number | null;
  specimen?: string | null;
  drain?: string | null;
  postoperative_orders?: string | null;
  status: string;
  newborns?: Newborn[];
}

interface Props {
  admission: AdmissionData;
  clinicalStaff?: ClinicalStaffMember[];
  anesthesiaTypes?: AnesthesiaTypeItem[];
}

export default function AdmissionsSurgery({
  admission,
  clinicalStaff = [],
  anesthesiaTypes = [],
}: Props) {
  const isDelivery = admission.surgery_type === 'delivery';
  const isMedical = admission.surgery_type === 'medical';

  const initialNewborns: Newborn[] =
    admission.newborns && admission.newborns.length > 0
      ? admission.newborns.map((nb) => ({
          ...nb,
          birth_at: nb.birth_at ? nb.birth_at.slice(0, 16) : '',
        }))
      : isDelivery
      ? [
          {
            sex: '',
            birth_at: '',
            birth_weight_kg: '',
            apgar_1_min: undefined,
            apgar_5_min: undefined,
            neonatal_care: 'Routine newborn care',
            notes: '',
          },
        ]
      : [];

  const { data, setData, put, processing, errors } = useForm({
    picme_no: admission.picme_no || '',
    procedure_at: admission.procedure_at ? admission.procedure_at.slice(0, 16) : '',
    procedure_name: admission.procedure_name || '',
    procedure_done: admission.procedure_done || '',
    icd_code: admission.icd_code || '',
    surgeon: toUpperCaseValue(admission.surgeon || ''),
    assisted_surgeon: toUpperCaseValue(admission.assisted_surgeon || ''),
    pediatrician_name: toUpperCaseValue(admission.pediatrician_name || ''),
    anaesthetist: toUpperCaseValue(admission.anaesthetist || ''),
    assisted_anaesthetist: toUpperCaseValue(admission.assisted_anaesthetist || ''),
    assistant: toUpperCaseValue(admission.assistant || ''),
    assisted_by: toUpperCaseValue(admission.assisted_by || ''),
    staff_nurse: toUpperCaseValue(admission.staff_nurse || ''),
    ot_technician: toUpperCaseValue(admission.ot_technician || ''),
    anaesthesia_type: admission.anaesthesia_type || '',
    procedure_note: admission.procedure_note || '',
    incision: admission.incision || '',
    findings: admission.findings || '',
    complications: admission.complications || '',
    blood_loss_ml: admission.blood_loss_ml || '',
    specimen: admission.specimen || '',
    drain: admission.drain || '',
    postoperative_orders: admission.postoperative_orders || '',
    newborns: initialNewborns,
    next_step: 'test-reports',
  });

  // Parse existing postoperative orders into individual rows
  const parseOrders = (raw?: string | null): string[] => {
    if (!raw || !raw.trim()) {
      return [''];
    }
    const lines = raw
      .split(/\r?\n/)
      .map((s) => s.replace(/^[-*•\d+.\s]+/, '').trim())
      .filter(Boolean);
    if (lines.length === 0) {
      const commaParts = raw
        .split(',')
        .map((s) => s.replace(/^[-*•\d+.\s]+/, '').trim())
        .filter(Boolean);
      if (commaParts.length > 1) return commaParts;
      return [raw.trim()];
    }
    return lines;
  };

  const [orderRows, setOrderRows] = useState<string[]>(() => parseOrders(admission.postoperative_orders));

  // Stent detection in operative procedure
  const hasStentInText = Boolean(
    (admission.procedure_name || '').match(/stent/i) ||
    (admission.procedure_done || '').match(/stent/i) ||
    (admission.procedure_note || '').match(/stent/i) ||
    (admission.findings || '').match(/stent/i) ||
    (admission.drain || '').match(/stent/i)
  );
  const [hasStent, setHasStent] = useState<boolean>(hasStentInText);

  const syncOrders = (rows: string[]) => {
    const valid = rows.map((r) => r.trim()).filter(Boolean);
    const serialized = valid.map((r, i) => `${i + 1}. ${r}`).join('\n');
    setData('postoperative_orders', serialized);
  };

  const updateOrderRow = (idx: number, val: string) => {
    const updated = [...orderRows];
    updated[idx] = val;
    setOrderRows(updated);
    syncOrders(updated);
  };

  const addOrderRow = (text = '') => {
    const updated = [...orderRows, text];
    setOrderRows(updated);
    syncOrders(updated);
  };

  const removeOrderRow = (idx: number) => {
    const updated = orderRows.filter((_, i) => i !== idx);
    const finalRows = updated.length > 0 ? updated : [''];
    setOrderRows(finalRows);
    syncOrders(finalRows);
  };

  const moveOrderRow = (idx: number, direction: 'up' | 'down') => {
    const targetIdx = direction === 'up' ? idx - 1 : idx + 1;
    if (targetIdx < 0 || targetIdx >= orderRows.length) return;
    const updated = [...orderRows];
    const [moved] = updated.splice(idx, 1);
    updated.splice(targetIdx, 0, moved);
    setOrderRows(updated);
    syncOrders(updated);
  };

  const addBaby = () => {
    setData('newborns', [
      ...data.newborns,
      {
        sex: '',
        birth_at: '',
        birth_weight_kg: '',
        apgar_1_min: undefined,
        apgar_5_min: undefined,
        neonatal_care: 'Routine newborn care',
        notes: '',
      },
    ]);
  };

  const removeBaby = (index: number) => {
    setData(
      'newborns',
      data.newborns.filter((_, i) => i !== index)
    );
  };

  const updateBaby = (index: number, field: keyof Newborn, val: any) => {
    const updated = [...data.newborns];
    updated[index] = { ...updated[index], [field]: val };
    setData('newborns', updated);
  };

  const handleSaveOnly = (e: React.FormEvent) => {
    e.preventDefault();
    setData('next_step', 'stay');
    put(`/admissions/${admission.id}/surgery`);
  };

  const handleSaveAndNext = (e: React.FormEvent) => {
    e.preventDefault();
    setData('next_step', 'test-reports');
    put(`/admissions/${admission.id}/surgery`);
  };

  return (
    <AppLayout>
      <Head title={`Stage 2: ${isMedical ? 'Clinical Notes' : 'Labour / Surgery'} — ${admission.patient_name}`} />

      {/* 4-Stage Navigation Header */}
      <AdmissionStageNav currentStage="surgery" admission={admission} />

      {isMedical && (
        <div className="alert alert-info d-flex flex-wrap align-items-center justify-content-between mb-4 border-0 shadow-sm p-3">
          <div className="d-flex align-items-center gap-2">
            <i className="bi bi-info-circle-fill fs-5 text-info"></i>
            <div>
              <strong>Medical Case (Non-Surgical):</strong> This patient is admitted for medical / conservative management. Operation theatre notes and newborn details are optional. You may record bedside notes or proceed directly to Stage 3.
            </div>
          </div>
          <Link
            href={`/admissions/${admission.id}/test-reports`}
            className="btn btn-sm btn-info text-white text-nowrap fw-semibold mt-2 mt-md-0"
          >
            Skip to Stage 3: Test Reports <i className="bi bi-arrow-right ms-1"></i>
          </Link>
        </div>
      )}

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 2: {isMedical ? 'Clinical & Bedside Notes' : 'Labour / LSCS / Surgery Notes'}
          </h1>
          <p className="text-muted mb-0">
            {isMedical
              ? 'Record any bedside procedures, conservative management notes, or clinical team details.'
              : 'Record Operation Theatre notes, surgical team members, procedure details, and newborn birth records.'}
          </p>
        </div>
        <div className="d-flex gap-2">
          <Link href={`/admissions/${admission.id}/edit`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 1: Admission
          </Link>
          <Link href={`/admissions/${admission.id}/documents/preview`} className="btn btn-outline-secondary">
            <i className="bi bi-file-earmark-text me-1"></i> Preview Documents
          </Link>
        </div>
      </div>

      <form id="surgeryForm">
        {/* Section 1: Procedure Info & Timing */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
            <div>
              <span className="badge bg-primary me-2">2.1</span> Operation Theatre &amp; Procedure Schedule
            </div>
            <span className="text-muted small">Step 2 of 4</span>
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label">Procedure / Delivery Date &amp; Time</label>
                <input
                  type="datetime-local"
                  className={`form-control ${errors.procedure_at ? 'is-invalid' : ''}`}
                  value={data.procedure_at}
                  onChange={(e) => setData('procedure_at', e.target.value)}
                />
                {errors.procedure_at && <div className="invalid-feedback">{errors.procedure_at}</div>}
              </div>

              {isDelivery && (
                <div className="col-md-4">
                  <label className="form-label required">
                    PICME Number (RCH ID)
                    <span className="badge bg-danger-subtle text-danger ms-2" style={{ fontSize: '0.68rem' }}>
                      DELIVERY / OBSTETRIC
                    </span>
                  </label>
                  <input
                    type="text"
                    className={`form-control ${errors.picme_no ? 'is-invalid' : ''}`}
                    value={data.picme_no}
                    onChange={(e) => setData('picme_no', e.target.value)}
                    placeholder="e.g. 331024567890 (12-digit PICME / RCH ID)"
                    maxLength={25}
                  />
                  {errors.picme_no && <div className="invalid-feedback">{errors.picme_no}</div>}
                </div>
              )}

              <div className={isDelivery ? 'col-md-4' : 'col-md-5'}>
                <label className="form-label">Procedure / Surgery Name</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.procedure_name}
                  onChange={(e) => setData('procedure_name', e.target.value)}
                  placeholder="e.g. Emergency Lower Segment Caesarean Section (LSCS)"
                />
              </div>

              <div className={isDelivery ? 'col-md-4' : 'col-md-3'}>
                <label className="form-label">ICD-10 Code</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.icd_code}
                  onChange={(e) => setData('icd_code', e.target.value)}
                  placeholder="e.g. O82.0 / K35.8"
                />
              </div>

              <div className="col-12">
                <label className="form-label">Procedure Done / Operation Summary</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.procedure_done}
                  onChange={(e) => setData('procedure_done', e.target.value)}
                  placeholder="e.g. Emergency LSCS with Pfannenstiel Incision & Bilateral Tubal Ligation"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 2: Surgical Team */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">2.2</span> Surgical &amp; Anaesthesia Team
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <StaffSelect
                  label="Surgeon"
                  role="doctor"
                  value={data.surgeon}
                  onChange={(val) => setData('surgeon', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Surgeon --"
                  uppercase
                />
              </div>

              <div className="col-md-4">
                <StaffSelect
                  label="Assisted Surgeon"
                  role="doctor"
                  value={data.assisted_surgeon}
                  onChange={(val) => setData('assisted_surgeon', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Assisted Surgeon --"
                  uppercase
                />
              </div>

              <div className="col-md-4">
                <StaffSelect
                  label="Anaesthetist"
                  role="anaesthetist"
                  value={data.anaesthetist}
                  onChange={(val) => setData('anaesthetist', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Anaesthetist --"
                  uppercase
                />
              </div>

              <div className="col-md-4">
                <StaffSelect
                  label="Assisted Anaesthetist"
                  role="anaesthetist"
                  value={data.assisted_anaesthetist}
                  onChange={(val) => setData('assisted_anaesthetist', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Assisted Anaesthetist --"
                  uppercase
                />
              </div>

              <div className="col-md-4">
                <AnesthesiaSelect
                  label="Anaesthesia Type"
                  value={data.anaesthesia_type}
                  onChange={(val) => setData('anaesthesia_type', val)}
                  anesthesiaList={anesthesiaTypes}
                  placeholder="-- Select Anaesthesia Type --"
                />
              </div>

              <div className="col-md-4">
                <StaffSelect
                  label="Scrub / Staff Nurse"
                  role="nurse"
                  value={data.staff_nurse}
                  onChange={(val) => setData('staff_nurse', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Staff Nurse --"
                  uppercase
                />
              </div>

              <div className="col-md-4">
                <StaffSelect
                  label="OT Technician"
                  role="ot_tech"
                  value={data.ot_technician}
                  onChange={(val) => setData('ot_technician', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select OT Technician --"
                  uppercase
                />
              </div>

              <div className="col-md-4">
                <StaffSelect
                  label="Pediatrician / Neonatologist"
                  role="doctor"
                  value={data.pediatrician_name}
                  onChange={(val) => setData('pediatrician_name', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Pediatrician --"
                  uppercase
                />
              </div>

              <div className="col-md-4">
                <label className="form-label">Assistant / Other Clinical Help</label>
                <input
                  type="text"
                  className="form-control"
                  style={{ textTransform: 'uppercase' }}
                  value={data.assisted_by}
                  onChange={(e) =>
                    handleNormalizedInputChange(e, toUpperCaseValue, (val) => setData('assisted_by', val))
                  }
                  placeholder="e.g. STAFF NURSE KAVITHA"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 3: Operative Notes & Findings */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">2.3</span> Operative Narrative &amp; Clinical Findings
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-12">
                <label className="form-label">Detailed Operative Procedure Note</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.procedure_note}
                  onChange={(e) => setData('procedure_note', e.target.value)}
                  placeholder="Describe step-by-step operative technique, closure, and findings..."
                />
              </div>

              <div className="col-md-6">
                <label className="form-label">Incision</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.incision}
                  onChange={(e) => setData('incision', e.target.value)}
                  placeholder="e.g. Pfannenstiel incision / Right lower paramedian"
                />
              </div>

              <div className="col-md-6">
                <label className="form-label">Operative Findings</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.findings}
                  onChange={(e) => setData('findings', e.target.value)}
                  placeholder="e.g. Single live male infant, clear liquor, intact placenta"
                />
              </div>

              <div className="col-md-3">
                <label className="form-label">Blood Loss (ml)</label>
                <input
                  type="number"
                  step="0.1"
                  className="form-control"
                  value={data.blood_loss_ml}
                  onChange={(e) => setData('blood_loss_ml', e.target.value)}
                  placeholder="e.g. 350"
                />
              </div>

              <div className="col-md-3">
                <label className="form-label">Complications</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.complications}
                  onChange={(e) => setData('complications', e.target.value)}
                  placeholder="e.g. Nil / None"
                />
              </div>

              <div className="col-md-3">
                <label className="form-label">Specimen Sent</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.specimen}
                  onChange={(e) => setData('specimen', e.target.value)}
                  placeholder="e.g. Placenta / Appendix in formalin / Nil"
                />
              </div>

              <div className="col-md-3">
                <label className="form-label">Drain Placed</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.drain}
                  onChange={(e) => setData('drain', e.target.value)}
                  placeholder="e.g. Nil / Romovac drain in pelvic cavity"
                />
              </div>

              {/* Stent Placement Detection / Toggle */}
              <div className="col-12 mt-2 pt-2 border-top">
                <div className="card bg-light-subtle border">
                  <div className="card-body p-3">
                    <div className="form-check form-switch mb-1">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="hasStentSwitch"
                        checked={hasStent}
                        onChange={(e) => {
                          const val = e.target.checked;
                          setHasStent(val);
                          if (val && !data.findings.toLowerCase().includes('stent')) {
                            const newFindings = data.findings ? `${data.findings}. DJ stent in situ.` : 'DJ stent in situ.';
                            setData('findings', newFindings);
                          }
                        }}
                      />
                      <label className="form-check-label fw-bold text-dark" htmlFor="hasStentSwitch">
                        <i className="bi bi-shield-plus text-primary me-1"></i> Patient has STENT (e.g. DJ Stent inserted)
                      </label>
                    </div>

                    {hasStent && (
                      <div className="alert alert-warning py-2 px-3 mb-0 border-0 rounded-2 mt-2">
                        <div className="d-flex align-items-center gap-2">
                          <i className="bi bi-info-circle-fill text-warning fs-5"></i>
                          <div className="small">
                            <strong>Stent Placement Notice:</strong> When a patient has a stent, the mandatory Tamil stent removal instruction:
                            <div className="font-monospace fw-bold text-dark mt-1" style={{ fontSize: '0.85rem' }}>
                              &ldquo;வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு (Date) அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.&rdquo;
                            </div>
                            will automatically appear on the patient&rsquo;s Discharge Summary and discharge review advice.
                          </div>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>

              {/* Immediate Postoperative Orders (Separate Rows) */}
              <div className="col-12 mt-3">
                <div className="d-flex justify-content-between align-items-center mb-2">
                  <label className="form-label fw-bold text-dark mb-0">
                    <i className="bi bi-list-check text-primary me-1"></i> Immediate Postoperative Orders (Row by Row)
                  </label>
                  <button
                    type="button"
                    onClick={() => addOrderRow()}
                    className="btn btn-sm btn-outline-primary fw-semibold"
                  >
                    <i className="bi bi-plus-circle me-1"></i> Add Order Row
                  </button>
                </div>

                {/* Quick Presets */}
                <div className="mb-2 d-flex flex-wrap gap-1.5 align-items-center">
                  <span className="small text-muted me-1">Quick add:</span>
                  {[
                    'NPO for 6 hours',
                    'IV Fluids: RL @ 100 ml/hr',
                    'Monitor vitals q1h x 4h, then q4h',
                    'Inj. Tramadol 50mg IV SOS (severe pain)',
                    'Inj. Ondansetron 4mg IV SOS (vomiting)',
                    'Catheter care & monitor hourly urine output',
                    'Check dressing site for soakage / bleeding',
                    'Sips of water after 6 hours if bowel sounds present',
                  ].map((preset, pIdx) => (
                    <button
                      key={pIdx}
                      type="button"
                      onClick={() => addOrderRow(preset)}
                      className="btn btn-xs btn-light border text-secondary"
                      style={{ fontSize: '0.76rem', padding: '2px 8px' }}
                    >
                      + {preset}
                    </button>
                  ))}
                </div>

                {/* Order Rows Repeater */}
                <div className="bg-light p-2.5 rounded-3 border">
                  {orderRows.map((order, oIdx) => (
                    <div key={oIdx} className="input-group mb-2 shadow-xs">
                      <span className="input-group-text bg-white fw-bold text-secondary" style={{ width: '48px', justifyContent: 'center' }}>
                        #{oIdx + 1}
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={order}
                        onChange={(e) => updateOrderRow(oIdx, e.target.value)}
                        placeholder={`Order #${oIdx + 1} (e.g. Monitor vitals q1h x 4h, IV fluids RL @ 100ml/hr)`}
                      />
                      <button
                        type="button"
                        onClick={() => moveOrderRow(oIdx, 'up')}
                        disabled={oIdx === 0}
                        className="btn btn-outline-secondary"
                        title="Move Up"
                      >
                        <i className="bi bi-arrow-up"></i>
                      </button>
                      <button
                        type="button"
                        onClick={() => moveOrderRow(oIdx, 'down')}
                        disabled={oIdx === orderRows.length - 1}
                        className="btn btn-outline-secondary"
                        title="Move Down"
                      >
                        <i className="bi bi-arrow-down"></i>
                      </button>
                      <button
                        type="button"
                        onClick={() => removeOrderRow(oIdx)}
                        disabled={orderRows.length <= 1 && !order.trim()}
                        className="btn btn-outline-danger"
                        title="Remove Order Row"
                      >
                        <i className="bi bi-trash"></i>
                      </button>
                    </div>
                  ))}

                  <div className="mt-2 text-end">
                    <button
                      type="button"
                      onClick={() => addOrderRow()}
                      className="btn btn-sm btn-primary fw-semibold"
                    >
                      <i className="bi bi-plus-lg me-1"></i> Add Another Order Row
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Section 4: Newborn Details (Delivery Only) */}
        {isDelivery && (
          <div className="card mb-4 border-0 shadow-sm border-start border-4 border-primary">
            <div className="card-header bg-white py-3 fw-bold text-dark d-flex justify-content-between align-items-center">
              <div>
                <span className="badge bg-primary me-2">2.4</span> Newborn / Birth Information
                <span className="text-muted small ms-2">(Delivery / Obstetric Workflow)</span>
              </div>
              <button type="button" onClick={addBaby} className="btn btn-sm btn-outline-primary">
                <i className="bi bi-plus-circle me-1"></i> Add Another Baby (Twins / Multiples)
              </button>
            </div>
            <div className="card-body">
              {data.newborns.length === 0 ? (
                <div className="text-center py-4 text-muted">
                  <p className="mb-2">No newborn baby records added yet.</p>
                  <button type="button" onClick={addBaby} className="btn btn-primary btn-sm">
                    <i className="bi bi-plus me-1"></i> Add Newborn Baby Details
                  </button>
                </div>
              ) : (
                data.newborns.map((baby, idx) => (
                  <div key={idx} className="p-3 bg-light rounded-3 mb-3 border">
                    <div className="d-flex justify-content-between align-items-center mb-3">
                      <h6 className="fw-bold mb-0 text-dark">
                        <i className="bi bi-person-heart text-primary me-2"></i>
                        Newborn Baby #{idx + 1}
                      </h6>
                      {data.newborns.length > 1 && (
                        <button
                          type="button"
                          onClick={() => removeBaby(idx)}
                          className="btn btn-sm btn-outline-danger"
                        >
                          <i className="bi bi-trash me-1"></i> Remove Baby #{idx + 1}
                        </button>
                      )}
                    </div>

                    <div className="row g-3">
                      <div className="col-md-3">
                        <label className="form-label required">Sex</label>
                        <select
                          className="form-select"
                          value={baby.sex || ''}
                          onChange={(e) => updateBaby(idx, 'sex', e.target.value)}
                          required
                        >
                          <option value="">Select Sex</option>
                          <option value="Female">Female</option>
                          <option value="Male">Male</option>
                          <option value="Other">Other</option>
                        </select>
                      </div>

                      <div className="col-md-3">
                        <label className="form-label">Birth Date &amp; Time</label>
                        <input
                          type="datetime-local"
                          className="form-control"
                          value={baby.birth_at || ''}
                          onChange={(e) => updateBaby(idx, 'birth_at', e.target.value)}
                        />
                      </div>

                      <div className="col-md-2">
                        <label className="form-label">Birth Weight (kg)</label>
                        <input
                          type="number"
                          step="0.01"
                          className="form-control"
                          value={baby.birth_weight_kg || ''}
                          onChange={(e) => updateBaby(idx, 'birth_weight_kg', e.target.value)}
                          placeholder="e.g. 2.85"
                        />
                      </div>

                      <div className="col-md-2">
                        <label className="form-label">Apgar (1 min)</label>
                        <input
                          type="number"
                          min="0"
                          max="10"
                          className="form-control"
                          value={baby.apgar_1_min ?? ''}
                          onChange={(e) => updateBaby(idx, 'apgar_1_min', e.target.value ? parseInt(e.target.value) : undefined)}
                          placeholder="0-10"
                        />
                      </div>

                      <div className="col-md-2">
                        <label className="form-label">Apgar (5 min)</label>
                        <input
                          type="number"
                          min="0"
                          max="10"
                          className="form-control"
                          value={baby.apgar_5_min ?? ''}
                          onChange={(e) => updateBaby(idx, 'apgar_5_min', e.target.value ? parseInt(e.target.value) : undefined)}
                          placeholder="0-10"
                        />
                      </div>

                      <div className="col-md-6">
                        <label className="form-label">Neonatal Care / Immediate Outcome</label>
                        <input
                          type="text"
                          className="form-control"
                          value={baby.neonatal_care || ''}
                          onChange={(e) => updateBaby(idx, 'neonatal_care', e.target.value)}
                          placeholder="e.g. Routine newborn care, baby cried immediately at birth"
                        />
                      </div>

                      <div className="col-md-6">
                        <label className="form-label">Baby Notes / Presentation</label>
                        <input
                          type="text"
                          className="form-control"
                          value={baby.notes || ''}
                          onChange={(e) => updateBaby(idx, 'notes', e.target.value)}
                          placeholder="e.g. Cephalic presentation, clear liquor, no birth injuries"
                        />
                      </div>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        )}

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
              <span>Save &amp; Next: Stage 3 (Test Reports)</span>
              <i className="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}

