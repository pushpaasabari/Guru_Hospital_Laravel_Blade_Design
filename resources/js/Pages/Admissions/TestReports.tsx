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

const CLINICAL_TEMPLATES = [
  {
    title: 'Complete Blood Count (CBC)',
    snippet: 'Hb: 12.4 g/dL | TC: 8,400 cells/cu.mm | DC: P68 L26 E4 M2 | Platelets: 2.5 Lakhs/cu.mm | PCV: 37.2%',
  },
  {
    title: 'Blood Group & Rh',
    snippet: "Blood Group: 'O' Positive (Rh Positive) | Screen: Antibody Negative",
  },
  {
    title: 'Urine Routine',
    snippet: 'Urine Routine: Albumin: Nil | Sugar: Nil | Deposits: Pus cells 1-2/HPF, Epithelial cells 2-3/HPF, RBCs: Nil',
  },
  {
    title: 'Biochemistry (RBS, Urea, Creatinine)',
    snippet: 'RBS: 98 mg/dL | Blood Urea: 20 mg/dL | Serum Creatinine: 0.8 mg/dL | Serum Electrolytes: Na+ 138, K+ 4.1, Cl- 102 mEq/L',
  },
  {
    title: 'Viral Screening (Serology)',
    snippet: 'HIV I & II: Non-Reactive | HBsAg: Negative | Anti-HCV: Negative | VDRL: Non-Reactive',
  },
  {
    title: 'Thyroid Function (TSH)',
    snippet: 'Serum TSH: 2.14 uIU/mL (Normal Reference: 0.35 - 4.94 uIU/mL)',
  },
  {
    title: 'Obstetric Ultrasound (USG)',
    snippet: 'USG Obstetric: Single live intrauterine fetus in cephalic presentation. Gestational age: 38+4 weeks. Good cardiac activity and fetal movements. Placenta: Fundal anterior Grade II. Adequate liquor (AFI: 13 cm). Estimated Fetal Weight: 2.9 kg.',
  },
  {
    title: 'ECG & Chest X-Ray',
    snippet: 'ECG: Normal sinus rhythm, rate 78 bpm, no ST-T changes. | Chest X-Ray PA: Normal bronchovascular markings, CTR normal, both CP angles clear.',
  },
  {
    title: 'Coagulation Profile',
    snippet: 'PT: 12.4 sec (Control: 12.0 sec) | INR: 1.03 | APTT: 31.0 sec | Bleeding Time: 2 min 10 sec | Clotting Time: 4 min 30 sec',
  },
];

export default function AdmissionsTestReports({ admission }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    investigations: admission.investigations || '',
    next_step: 'discharge',
  });

  const insertTemplate = (snippet: string) => {
    setData((prev) => {
      const current = prev.investigations.trim();
      const nextVal = current ? `${current}\n${snippet}` : snippet;
      return { ...prev, investigations: nextVal };
    });
  };

  const handleSaveOnly = (e: React.FormEvent) => {
    e.preventDefault();
    setData('next_step', 'stay');
    put(`/admissions/${admission.id}/test-reports`);
  };

  const handleSaveAndNext = (e: React.FormEvent) => {
    e.preventDefault();
    setData('next_step', 'discharge');
    put(`/admissions/${admission.id}/test-reports`);
  };

  return (
    <AppLayout>
      <Head title={`Stage 3: Test Reports — ${admission.patient_name}`} />

      {/* 4-Stage Navigation Header */}
      <AdmissionStageNav currentStage="test-reports" admission={admission} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 3: Laboratory &amp; Diagnostic Test Reports
          </h1>
          <p className="text-muted mb-0">
            Record clinical pathology, biochemistry, hematology, blood grouping, and diagnostic imaging reports.
          </p>
        </div>
        <div className="d-flex gap-2">
          <Link href={`/admissions/${admission.id}/surgery`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 2: Surgery
          </Link>
          <Link href={`/admissions/${admission.id}/documents/preview`} className="btn btn-outline-secondary">
            <i className="bi bi-file-earmark-text me-1"></i> Preview Documents
          </Link>
        </div>
      </div>

      <form id="testReportsForm">
        {/* Quick Insertion Templates */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
            <div className="d-flex align-items-center gap-2">
              <span className="badge bg-primary">3.1</span>
              <span>Quick Clinical Test Templates</span>
            </div>
            <span className="text-muted small">Click any test template below to insert instantly</span>
          </div>
          <div className="card-body bg-light-subtle">
            <div className="d-flex flex-wrap gap-2">
              {CLINICAL_TEMPLATES.map((tmpl, idx) => (
                <button
                  key={idx}
                  type="button"
                  onClick={() => insertTemplate(tmpl.snippet)}
                  className="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5 shadow-xs bg-white"
                >
                  <i className="bi bi-plus-circle text-primary"></i>
                  <span>{tmpl.title}</span>
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Main Test Reports Editor */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
            <div>
              <span className="badge bg-primary me-2">3.2</span>
              <span>Diagnostic Investigations &amp; Lab Results</span>
            </div>
            <span className="text-muted small">Step 3 of 4</span>
          </div>
          <div className="card-body">
            <div className="mb-3">
              <label className="form-label fw-semibold text-dark">
                Investigations (Hematology, Biochemistry, Urine, Scans, X-Ray, etc.)
              </label>
              <textarea
                className={`form-control font-monospace ${errors.investigations ? 'is-invalid' : ''}`}
                rows={12}
                value={data.investigations}
                onChange={(e) => setData('investigations', e.target.value)}
                placeholder="Enter or paste lab reports, test values, and diagnostic impressions here..."
                style={{ fontSize: '0.92rem', lineHeight: '1.6' }}
              />
              {errors.investigations && <div className="invalid-feedback">{errors.investigations}</div>}
              <div className="form-text text-muted mt-2">
                <i className="bi bi-info-circle me-1"></i> These findings appear in the &ldquo;INVESTIGATIONS&rdquo; section of the patient&rsquo;s Discharge Summary.
              </div>
            </div>

            {/* Live Preview Box */}
            {data.investigations.trim() && (
              <div className="mt-4 p-3 rounded-3 bg-light border">
                <h6 className="fw-bold text-secondary text-uppercase mb-2 small tracking-wide">
                  <i className="bi bi-eye me-1"></i> Document Print Preview:
                </h6>
                <div
                  className="bg-white p-3 rounded border text-dark font-sans"
                  style={{ whiteSpace: 'pre-wrap', fontSize: '0.9rem', lineHeight: '1.5' }}
                >
                  {data.investigations}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Action Bar */}
        <div className="d-flex justify-content-between align-items-center pb-5">
          <Link href={`/admissions/${admission.id}/surgery`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 2: Labour / Surgery
          </Link>
          <div className="d-flex gap-2">
            <button
              type="button"
              onClick={handleSaveOnly}
              disabled={processing}
              className="btn btn-outline-primary px-3 py-2 fw-semibold"
            >
              Save Stage 3 Changes
            </button>
            <button
              type="button"
              onClick={handleSaveAndNext}
              disabled={processing}
              className="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
            >
              <span>Save &amp; Next: Stage 4 (Discharge)</span>
              <i className="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}
