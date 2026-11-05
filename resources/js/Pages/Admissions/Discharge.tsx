import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';
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
  admission_at: string;
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
  handover_name?: string | null;
  floor_incharge_name?: string | null;
  mrd_incharge_name?: string | null;
  status: string;
}

interface Props {
  admission: AdmissionData;
  clinicalStaff?: ClinicalStaffMember[];
}

export default function AdmissionsDischarge({ admission, clinicalStaff = [] }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    discharge_at: admission.discharge_at ? admission.discharge_at.slice(0, 16) : '',
    discharge_condition: admission.discharge_condition || '',
    clinical_history: admission.clinical_history || '',
    examination: admission.examination || '',
    treatment_course: admission.treatment_course || '',
    medication_orders: admission.medication_orders || '',
    discharge_advice: admission.discharge_advice || '',
    follow_up: admission.follow_up || '',
    mrd_no: admission.mrd_no || (admission.uhid ? (admission.uhid.startsWith('MRD-') ? admission.uhid : `MRD-${admission.uhid}`) : ''),
    payment_mode: admission.payment_mode || '',
    handover_name: admission.handover_name || '',
    floor_incharge_name: admission.floor_incharge_name || '',
    mrd_incharge_name: admission.mrd_incharge_name || '',
    next_step: 'preview',
  });

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
                  placeholder="e.g. Patient is hemodynamically stable, afebrile, surgical wound healthy and dry."
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
                  placeholder="e.g. C/O Lower abdominal pain radiating to back x 1 day. EDD: 22-09-2026. Admitted for safe confinement."
                />
              </div>

              <div className="col-md-6">
                <label className="form-label">Clinical Examination</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.examination}
                  onChange={(e) => setData('examination', e.target.value)}
                  placeholder="e.g. O/E Afebrile, no pallor, no pedal edema. PR: 78/min, BP: 120/80 mmHg. CVS & RS: Normal. P/A: Uterus term size, relaxed, cephalic."
                />
              </div>

              <div className="col-12">
                <label className="form-label">Course in Hospital &amp; Treatment Given</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.treatment_course}
                  onChange={(e) => setData('treatment_course', e.target.value)}
                  placeholder="e.g. Patient underwent emergency LSCS under spinal anaesthesia. Delivered healthy live baby. Post-op recovery uneventful. Wound healthy, suture removed on POD 7."
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 3: Medication & Discharge Advice */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">4.3</span> Medication Orders &amp; Discharge Advice
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-12">
                <label className="form-label">Discharge Medication Orders</label>
                <textarea
                  className="form-control font-monospace"
                  rows={4}
                  value={data.medication_orders}
                  onChange={(e) => setData('medication_orders', e.target.value)}
                  placeholder="1. Tab. Cefixime 200mg BD x 5 days&#10;2. Tab. Pantoprazole 40mg OD (before breakfast) x 5 days&#10;3. Tab. Paracetamol 650mg TDS x 3 days&#10;4. Tab. Calcium 500mg OD x 30 days"
                />
              </div>

              <div className="col-md-6">
                <label className="form-label">Discharge Advice &amp; Instructions</label>
                <textarea
                  className="form-control"
                  rows={3}
                  value={data.discharge_advice}
                  onChange={(e) => setData('discharge_advice', e.target.value)}
                  placeholder="e.g. High protein diet, plenty of oral fluids. Keep wound clean and dry. Avoid lifting heavy weights."
                />
              </div>

              <div className="col-md-6">
                <label className="form-label">Follow-up Advice &amp; Review Date</label>
                <textarea
                  className="form-control"
                  rows={3}
                  value={data.follow_up}
                  onChange={(e) => setData('follow_up', e.target.value)}
                  placeholder="e.g. Review in OPD on 05/09/2026 or SOS in case of pain, excessive bleeding, or fever."
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

              <div className="col-md-4">
                <label className="form-label">Payment / Insurance Scheme</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.payment_mode}
                  onChange={(e) => setData('payment_mode', e.target.value)}
                  placeholder="e.g. CMCHISTN / Cash / Star Health"
                />
              </div>

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

