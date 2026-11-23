import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import AdmissionStageNav from '@/Components/AdmissionStageNav';

interface AdmissionData {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  picme_no?: string | null;
  age?: string | null;
  sex?: string | null;
  format?: string;
  surgery_type?: string;
  status: string;
  clinical_history?: string | null;
  examination?: string | null;
  investigations?: string | null;
  procedure_name?: string | null;
  discharge_at?: string | null;
}

interface Props {
  admission: AdmissionData;
}

export default function AdmissionsClinical({ admission }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    clinical_history: admission.clinical_history || '',
    examination: admission.examination || '',
    next_step: 'test-reports',
  });

  const submitWithStep = (step: 'test-reports' | 'stay' | 'preview' | 'admission', e?: React.FormEvent) => {
    if (e) e.preventDefault();
    setData('next_step', step);
    put(`/admissions/${admission.id}/clinical`);
  };

  const handleSaveOnly = (e: React.FormEvent) => {
    submitWithStep('stay', e);
  };

  const handleSaveAndProceed = (e: React.FormEvent) => {
    submitWithStep('test-reports', e);
  };

  return (
    <AppLayout>
      <Head title={`Stage 2: Clinical Assessment — ${admission.patient_name}`} />

      {/* 5-Stage Navigation Header */}
      <AdmissionStageNav currentStage="clinical" admission={admission} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 2: Clinical Assessment &amp; History
          </h1>
          <p className="text-muted mb-0">
            Record presenting complaints, history of present illness, and clinical examination findings.
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

      <form id="clinicalForm" onSubmit={handleSaveAndProceed}>
        {/* Section 2.1: Presenting Complaints & Clinical History */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
            <div>
              <span className="badge bg-primary me-2">2.1</span> Presenting Complaints &amp; Clinical History
            </div>
            <span className="text-muted small">Step 2 of 5</span>
          </div>
          <div className="card-body">
            <div className="mb-3">
              <label className="form-label fw-semibold text-secondary">
                Presenting Complaints / History of Present Illness (HPI)
              </label>
              <textarea
                className={`form-control ${errors.clinical_history ? 'is-invalid' : ''}`}
                rows={5}
                value={data.clinical_history}
                onChange={(e) => setData('clinical_history', e.target.value)}
                placeholder="e.g. G3P1L1A1 / TWINS / EDD 22-9-2026 / admitted for safe confinement. C/O abdominal pain for 2 hours."
              />
              {errors.clinical_history && (
                <div className="invalid-feedback">{errors.clinical_history}</div>
              )}
              <div className="form-text text-muted">
                Document chief complaints, gestational age/parity for obstetric cases, duration, and symptom narrative.
              </div>
            </div>
          </div>
        </div>

        {/* Section 2.2: Clinical Examination */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">2.2</span> Clinical &amp; Systemic Examination
          </div>
          <div className="card-body">
            <div className="mb-3">
              <label className="form-label fw-semibold text-secondary">
                Clinical Examination Findings (General &amp; Systemic)
              </label>
              <textarea
                className={`form-control ${errors.examination ? 'is-invalid' : ''}`}
                rows={5}
                value={data.examination}
                onChange={(e) => setData('examination', e.target.value)}
                placeholder="e.g. O/E – Conscious, afebrile, no pallor, no pedal edema. CVS: S1 S2 heard. RS: Clear. P/A: Uterus term size, relaxed, cephalic, FHS: 140 bpm regular."
              />
              {errors.examination && (
                <div className="invalid-feedback">{errors.examination}</div>
              )}
              <div className="form-text text-muted">
                Record general physical condition, vital signs overview, cardiovascular/respiratory, and local examination.
              </div>
            </div>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-2">
          <Link href={`/admissions/${admission.id}/edit`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 1: Admission
          </Link>

          <div className="d-flex gap-2">
            <button
              type="button"
              onClick={handleSaveOnly}
              disabled={processing}
              className="btn btn-outline-primary"
            >
              <i className="bi bi-save me-1"></i> Save Changes
            </button>
            <button
              type="submit"
              disabled={processing}
              className="btn btn-primary px-4 fw-semibold"
            >
              Save &amp; Proceed to Stage 3: Test Reports <i className="bi bi-arrow-right ms-1"></i>
            </button>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}
