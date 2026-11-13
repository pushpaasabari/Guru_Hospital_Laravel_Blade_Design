import React from 'react';
import { Link } from '@inertiajs/react';

export interface AdmissionStageNavProps {
  currentStage: 'admission' | 'surgery' | 'test-reports' | 'discharge';
  admission?: {
    id: number;
    patient_name: string;
    uhid: string;
    ip_no: string;
    picme_no?: string | null;
    age?: string | null;
    sex?: string | null;
    format?: string;
    surgery_type?: string;
    status?: string;
    procedure_name?: string | null;
    investigations?: string | null;
    discharge_at?: string | null;
  } | null;
}

export default function AdmissionStageNav({ currentStage, admission }: AdmissionStageNavProps) {
  const stages = [
    {
      id: 'admission',
      step: 1,
      name: 'Admission',
      subtitle: 'Demographics & Case',
      href: admission ? `/admissions/${admission.id}/edit` : undefined,
      isCompleted: Boolean(admission?.uhid && admission?.ip_no),
    },
    {
      id: 'test-reports',
      step: 2,
      name: 'Test Reports',
      subtitle: 'Lab Tests & Diagnostics',
      href: admission ? `/admissions/${admission.id}/test-reports` : undefined,
      isCompleted: Boolean(admission?.investigations && admission.investigations.trim().length > 0),
    },
    {
      id: 'surgery',
      step: 3,
      name: admission?.surgery_type === 'medical' ? 'Clinical Notes' : 'Labour / Surgery',
      subtitle: admission?.surgery_type === 'medical' ? 'Bedside & Treatment' : 'OT Team & Notes',
      href: admission ? `/admissions/${admission.id}/surgery` : undefined,
      isCompleted: Boolean(
        admission?.procedure_name ||
        admission?.surgery_type === 'delivery' ||
        admission?.surgery_type === 'medical'
      ),
    },
    {
      id: 'discharge',
      step: 4,
      name: 'Discharge',
      subtitle: 'Summary, Advice & MRD',
      href: admission ? `/admissions/${admission.id}/discharge` : undefined,
      isCompleted: Boolean(admission?.discharge_at),
    },
  ];

  return (
    <div className="mb-4">
      {/* Patient Clinical Banner (When editing existing admission) */}
      {admission && (
        <div className="card border-0 shadow-sm mb-3">
          <div className="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div className="d-flex align-items-center gap-3">
              <div
                className="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                style={{ width: '42px', height: '42px', fontSize: '1.1rem' }}
              >
                {admission.patient_name ? admission.patient_name.charAt(0).toUpperCase() : 'P'}
              </div>
              <div>
                <div className="d-flex align-items-center gap-2 flex-wrap">
                  <h5 className="fw-bold mb-0 text-dark">{admission.patient_name || 'Patient Record'}</h5>
                  {admission.sex && (
                    <span className="badge bg-light text-secondary border">
                      {admission.age ? `${admission.age} Yrs / ` : ''}{admission.sex}
                    </span>
                  )}
                  <span className="badge bg-success-subtle text-success border border-success-subtle text-uppercase">
                    {admission.status || 'Draft'}
                  </span>
                </div>
                <div className="d-flex align-items-center gap-3 text-muted small mt-1 flex-wrap">
                  <span>
                    <strong className="text-secondary">UHID:</strong> <span className="font-monospace text-dark">{admission.uhid}</span>
                  </span>
                  <span>&bull;</span>
                  <span>
                    <strong className="text-secondary">IP No:</strong> <span className="font-monospace text-dark">{admission.ip_no}</span>
                  </span>
                  {admission.picme_no && (
                    <>
                      <span>&bull;</span>
                      <span>
                        <strong className="text-secondary">PICME:</strong>{' '}
                        <span className="font-monospace text-dark fw-bold">{admission.picme_no}</span>
                      </span>
                    </>
                  )}
                  <span>&bull;</span>
                  <span className="badge bg-secondary-subtle text-secondary text-uppercase">
                    {admission.format || 'General'}
                  </span>
                  <span className="badge bg-info-subtle text-info text-uppercase">
                    {admission.surgery_type === 'delivery'
                      ? 'Delivery / Obstetric'
                      : admission.surgery_type === 'medical'
                      ? 'Medical Case'
                      : 'Other Surgery'}
                  </span>
                </div>
              </div>
            </div>

            <div className="d-flex gap-2">
              <Link
                href={`/admissions/${admission.id}/documents/preview`}
                prefetch
                className="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5"
              >
                <i className="bi bi-file-earmark-text"></i>
                <span>Preview Document Pack</span>
              </Link>
            </div>
          </div>
        </div>
      )}

      {/* 4-Stage Horizontal Stepper */}
      <div className="fg-stage-stepper">
        <div className="row g-2">
          {stages.map((stage) => {
            const isActive = currentStage === stage.id;
            const isClickable = Boolean(stage.href);

            const cardClasses = [
              'fg-stage-card',
              isActive ? 'active' : '',
              stage.isCompleted && !isActive ? 'completed' : '',
              !isClickable ? 'disabled' : '',
            ]
              .filter(Boolean)
              .join(' ');

            const content = (
              <div className={cardClasses}>
                <div className="fg-stage-number">
                  {stage.isCompleted && !isActive ? (
                    <i className="bi bi-check-lg" style={{ fontSize: '1.1rem' }}></i>
                  ) : (
                    stage.step
                  )}
                </div>

                <div className="min-w-0 flex-grow-1" style={{ overflow: 'hidden' }}>
                  <div className="d-flex align-items-center justify-content-between gap-1">
                    <span
                      className={`fw-bold text-truncate ${
                        isActive ? 'text-primary' : isClickable ? 'text-dark' : 'text-secondary'
                      }`}
                      style={{ fontSize: '0.86rem' }}
                    >
                      {stage.name}
                    </span>
                    {isActive && (
                      <span
                        className="badge bg-primary text-white"
                        style={{ fontSize: '0.62rem', letterSpacing: '0.04em' }}
                      >
                        ACTIVE
                      </span>
                    )}
                  </div>
                  <div className="text-muted text-truncate" style={{ fontSize: '0.74rem' }}>
                    {stage.subtitle}
                  </div>
                </div>
              </div>
            );

            return (
              <div key={stage.id} className="col-12 col-sm-6 col-lg-3">
                {isClickable ? (
                  <Link href={stage.href!} prefetch className="text-decoration-none d-block h-100">
                    {content}
                  </Link>
                ) : (
                  content
                )}
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
}
