import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import AdmissionStageNav from '@/Components/AdmissionStageNav';
import {
  CLINICAL_EXAM_PRESET_GROUPS,
  applyNormalExamBundle,
  isExamPresetSelected,
  toggleExamPreset,
} from '@/utils/clinicalExamPresets';

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
  const isDelivery = admission.surgery_type === 'delivery';
  const [showObstetricPresets, setShowObstetricPresets] = useState<boolean>(isDelivery);
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
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <span className="badge bg-primary me-2">2.2</span> Clinical &amp; Systemic Examination
            </div>
            <div className="d-flex flex-wrap align-items-center gap-2">
              <button
                type="button"
                className="btn btn-sm btn-outline-primary fw-semibold"
                onClick={() => setData('examination', applyNormalExamBundle(data.examination, isDelivery))}
              >
                <i className="bi bi-check2-all me-1"></i>
                {isDelivery ? 'Fill Normal Obstetric Exam' : 'Fill Normal Baseline Exam'}
              </button>
              {!isDelivery && (
                <button
                  type="button"
                  className="btn btn-sm btn-outline-secondary"
                  onClick={() => setShowObstetricPresets((prev) => !prev)}
                >
                  <i className={`bi ${showObstetricPresets ? 'bi-chevron-up' : 'bi-plus-circle'} me-1`}></i>
                  {showObstetricPresets ? 'Hide Obstetric Presets' : 'Obstetric Presets'}
                </button>
              )}
              {data.examination.trim().length > 0 && (
                <button
                  type="button"
                  className="btn btn-sm btn-outline-danger"
                  onClick={() => setData('examination', '')}
                >
                  <i className="bi bi-x-circle me-1"></i> Clear
                </button>
              )}
            </div>
          </div>
          <div className="card-body">
            {/* Predefined Quick Checkboxes */}
            <div className="p-3 bg-light rounded-3 border mb-3">
              <div className="d-flex justify-content-between align-items-center mb-2">
                <span className="small fw-bold text-dark">
                  <i className="bi bi-ui-checks-grid text-primary me-1"></i>
                  Quick Predefined Findings (Check boxes to add/remove)
                </span>
                <span className="small text-muted" style={{ fontSize: '11.5px' }}>
                  You can still type or edit freely below
                </span>
              </div>
              <div className="row g-3">
                {CLINICAL_EXAM_PRESET_GROUPS.filter(
                  (grp) => grp.id !== 'obstetric' || showObstetricPresets
                ).map((group) => (
                  <div key={group.id} className="col-12 col-md-6">
                    <div className="bg-white rounded-3 border p-2 h-100">
                      <div className="small fw-bold text-secondary mb-2">
                        <i className={`bi ${group.icon} text-primary me-1`}></i>
                        {group.title}
                      </div>
                      <div className="d-flex flex-wrap gap-2">
                        {group.items.map((preset) => {
                          const checked = isExamPresetSelected(data.examination, preset);
                          const inputId = `exam-preset-${preset.id}`;
                          return (
                            <label
                              key={preset.id}
                              htmlFor={inputId}
                              className={`d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill border small ${
                                checked
                                  ? 'bg-primary-subtle border-primary text-primary fw-semibold'
                                  : 'bg-light border-secondary-subtle text-dark'
                              }`}
                              style={{ cursor: 'pointer', fontSize: '0.8rem', userSelect: 'none' }}
                            >
                              <input
                                id={inputId}
                                type="checkbox"
                                className="form-check-input m-0"
                                checked={checked}
                                onChange={() => setData('examination', toggleExamPreset(data.examination, preset))}
                              />
                              <span>{preset.label}</span>
                            </label>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>

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
