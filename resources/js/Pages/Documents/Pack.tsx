import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

interface Newborn {
  id?: number;
  baby_identifier?: string;
  sex?: string;
  birth_weight_kg?: number;
  birth_at?: string;
  apgar_1_min?: string;
  apgar_5_min?: string;
  gestational_age_weeks?: number;
  neonatal_care?: string;
}

interface User {
  id: number;
  name: string;
  role: string;
  doctor_reg_no?: string;
}

interface Approval {
  id: number;
  action: string;
  notes_or_correction_reason?: string;
  created_at?: string;
  performer?: User;
  user_role?: string;
}

interface Revision {
  id: number;
  revision_number: number;
  approved_at?: string;
  created_at?: string;
  approver?: User;
}

interface Admission {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  age?: string;
  sex?: string;
  address?: string;
  contact?: string;
  admission_at?: string;
  discharge_at?: string;
  procedure_at?: string;
  consultant?: string;
  department?: string;
  mrd_no?: string;
  icd_code?: string;
  payment_mode?: string;
  status: string;
  format: 'general' | 'cmchistn' | 'employee' | 'private';
  surgery_type: 'delivery' | 'other';
  procedure_name?: string;
  procedure_done?: string;
  surgeon?: string;
  anaesthetist?: string;
  assisted_by?: string;
  assistant?: string;
  anaesthesia_type?: string;
  pediatrician_name?: string;
  diagnosis: string;
  clinical_history?: string;
  examination?: string;
  procedure_note?: string;
  incision?: string;
  findings?: string;
  complications?: string;
  blood_loss_ml?: number;
  specimen?: string;
  drain?: string;
  postoperative_orders?: string;
  treatment_given?: string;
  investigations?: string;
  condition_at_discharge?: string;
  discharge_advice?: string;
  emergency_instructions?: string;
  rejection_reason?: string;
  created_at?: string;
  approved_at?: string;
  approver?: User;
  creator?: User;
  newborns: Newborn[];
  approvals: Approval[];
  revisions: Revision[];
}

interface Props {
  admission: Admission;
}

export default function Pack({ admission }: Props) {
  const { auth } = usePage<{ auth: { user: any } }>().props;
  const user = auth.user;

  const [returnModalOpen, setReturnModalOpen] = useState(false);
  const [revisionModalOpen, setRevisionModalOpen] = useState(false);
  const [correctionReason, setCorrectionReason] = useState('');
  const [revisionReason, setRevisionReason] = useState('');
  const [submittingReturn, setSubmittingReturn] = useState(false);
  const [submittingRevision, setSubmittingRevision] = useState(false);

  const isDraft = admission.status === 'draft';
  const isReturned = admission.status === 'returned_for_correction';
  const isReady = admission.status === 'ready_for_review';
  const isApproved = admission.status === 'approved';
  const isGenerated = admission.status === 'generated';
  const canBeEdited = isDraft || isReturned;

  const handleSubmitReview = (e: React.FormEvent) => {
    e.preventDefault();
    if (confirm('Submit this document pack for clinical review by a doctor?')) {
      router.post(`/documents/${admission.id}/submit-review`);
    }
  };

  const handleApprove = (e: React.FormEvent) => {
    e.preventDefault();
    if (confirm('Are you sure you want to clinically approve this complete document pack?')) {
      router.post(`/documents/${admission.id}/approve`);
    }
  };

  const handleConfirmReturn = (e: React.FormEvent) => {
    e.preventDefault();
    if (!correctionReason.trim()) return;
    setSubmittingReturn(true);
    router.post(
      `/documents/${admission.id}/return`,
      { correction_reason: correctionReason },
      {
        onFinish: () => {
          setSubmittingReturn(false);
          setReturnModalOpen(false);
        },
      }
    );
  };

  const handleConfirmRevision = (e: React.FormEvent) => {
    e.preventDefault();
    if (!revisionReason.trim()) return;
    setSubmittingRevision(true);
    router.post(
      `/admissions/${admission.id}/revision`,
      { revision_reason: revisionReason },
      {
        onFinish: () => {
          setSubmittingRevision(false);
          setRevisionModalOpen(false);
        },
      }
    );
  };

  const formatDate = (dateStr?: string) => {
    if (!dateStr) return '—';
    try {
      const d = new Date(dateStr);
      return d.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
      });
    } catch {
      return dateStr;
    }
  };

  const formatDateTime = (dateStr?: string) => {
    if (!dateStr) return '—';
    try {
      const d = new Date(dateStr);
      return (
        d.toLocaleDateString('en-GB', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
        }) +
        ' at ' +
        d.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit',
          hour12: true,
        })
      );
    } catch {
      return dateStr;
    }
  };

  const getStatusBadgeClass = (status: string) => {
    switch (status) {
      case 'draft':
        return 'badge-draft';
      case 'ready_for_review':
        return 'badge-ready_for_review';
      case 'returned_for_correction':
        return 'badge-returned_for_correction';
      case 'approved':
        return 'badge-approved';
      case 'generated':
        return 'badge-generated';
      default:
        return 'badge-superseded';
    }
  };

  return (
    <AppLayout>
      <Head title={`Review Document Pack — ${admission.patient_name} (${admission.ip_no})`} />

      {/* Control & Action Bar (Hidden on Print) */}
      <div className="card mb-4 no-print shadow-sm border-0" style={{ borderRadius: '20px' }}>
        <div className="card-body p-4">
          <div className="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
              <div className="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h3 className="h4 fw-bold mb-0 text-dark" style={{ letterSpacing: '-0.02em' }}>
                  {admission.patient_name}
                </h3>
                <span className="badge bg-light text-dark border">IP: {admission.ip_no}</span>
                <span className="badge bg-light text-muted border">UHID: {admission.uhid}</span>
                <span className={`badge ${getStatusBadgeClass(admission.status)} text-uppercase`}>
                  {admission.status.replace(/_/g, ' ')}
                </span>
              </div>
              <div className="text-muted small">
                Format: <strong className="text-uppercase text-dark">{admission.format}</strong> &bull;{' '}
                Workflow:{' '}
                <strong className="text-dark">
                  {admission.surgery_type === 'delivery'
                    ? `Delivery / Obstetric (${admission.newborns?.length || 0} baby)`
                    : 'Other Surgery'}
                </strong>{' '}
                &bull; Consultant:{' '}
                <strong className="text-dark">{admission.consultant || 'Not recorded'}</strong>
              </div>
            </div>

            <div className="d-flex flex-wrap gap-2">
              <Link href="/admissions" className="btn btn-outline-secondary">
                <i className="bi bi-arrow-left me-1"></i> Back
              </Link>

              <Link href={`/documents/${admission.id}/history`} className="btn btn-outline-secondary">
                <i className="bi bi-clock-history me-1"></i> Audit Trail
              </Link>

              {canBeEdited && user?.can_edit && (
                <Link href={`/admissions/${admission.id}/edit`} className="btn btn-outline-secondary">
                  <i className="bi bi-pencil me-1"></i> Edit Data
                </Link>
              )}

              {(isDraft || isReturned) && (
                <form onSubmit={handleSubmitReview} className="d-inline">
                  <button type="submit" className="btn btn-info text-white fw-semibold">
                    <i className="bi bi-send-check me-1"></i> Submit for Review
                  </button>
                </form>
              )}

              {isReady && user?.can_approve && (
                <>
                  <button
                    type="button"
                    className="btn btn-danger"
                    onClick={() => setReturnModalOpen(true)}
                  >
                    <i className="bi bi-arrow-return-left me-1"></i> Return for Correction
                  </button>
                  <form onSubmit={handleApprove} className="d-inline">
                    <button type="submit" className="btn btn-success fw-semibold">
                      <i className="bi bi-check2-circle me-1"></i> Clinically Approve Pack
                    </button>
                  </form>
                </>
              )}

              {(isApproved || isGenerated) && (
                <>
                  <div className="btn-group">
                    <a
                      href={`/admissions/${admission.id}/documents/pdf/discharge-summary`}
                      target="_blank"
                      rel="noreferrer"
                      className="btn btn-primary fw-semibold"
                      title="Generate and download Discharge Summary PDF"
                    >
                      <i className="bi bi-file-earmark-medical me-1"></i> 1. Discharge Summary PDF
                    </a>
                    <button
                      type="button"
                      className="btn btn-primary dropdown-toggle dropdown-toggle-split"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                    >
                      <span className="visually-hidden">Toggle PDF Options</span>
                    </button>
                    <ul className="dropdown-menu dropdown-menu-end shadow-sm border-0" style={{ borderRadius: '12px', fontSize: '13px' }}>
                      <li>
                        <h6 className="dropdown-header text-uppercase fw-bold" style={{ fontSize: '10.5px' }}>
                          Separate PDF Documents
                        </h6>
                      </li>
                      <li>
                        <a
                          className="dropdown-item py-2"
                          href={`/admissions/${admission.id}/documents/pdf/discharge-summary`}
                          target="_blank"
                          rel="noreferrer"
                        >
                          <i className="bi bi-file-earmark-text text-primary me-2"></i>
                          1. Discharge Summary PDF
                        </a>
                      </li>
                      <li>
                        <a
                          className="dropdown-item py-2"
                          href={`/admissions/${admission.id}/documents/pdf/operation-notes`}
                          target="_blank"
                          rel="noreferrer"
                        >
                          <i className="bi bi-journal-medical text-success me-2"></i>
                          2. Operation Theatre Notes PDF
                        </a>
                      </li>
                      <li>
                        <a
                          className="dropdown-item py-2"
                          href={`/admissions/${admission.id}/documents/pdf/mrd-form`}
                          target="_blank"
                          rel="noreferrer"
                        >
                          <i className="bi bi-file-earmark-check text-warning me-2"></i>
                          3. MRD Inpatient Form PDF
                        </a>
                      </li>
                      <li><hr className="dropdown-divider" /></li>
                      <li>
                        <a
                          className="dropdown-item py-2 fw-semibold"
                          href={`/admissions/${admission.id}/documents/pdf/pack`}
                          target="_blank"
                          rel="noreferrer"
                        >
                          <i className="bi bi-collection text-dark me-2"></i>
                          Complete 3-in-1 Pack PDF
                        </a>
                      </li>
                    </ul>
                  </div>

                  {(user?.is_admin || user?.role === 'doctor') && (
                    <button
                      type="button"
                      className="btn btn-outline-secondary"
                      onClick={() => setRevisionModalOpen(true)}
                    >
                      <i className="bi bi-pencil-square me-1"></i> Create Revision
                    </button>
                  )}
                </>
              )}

              <button className="btn btn-outline-secondary" onClick={() => window.print()}>
                <i className="bi bi-printer me-1"></i> Print
              </button>
            </div>
          </div>

          {isReturned && admission.rejection_reason && (
            <div className="alert alert-danger mt-3 mb-0 py-2">
              <strong>
                <i className="bi bi-exclamation-triangle-fill me-1"></i> Correction Reason:
              </strong>{' '}
              {admission.rejection_reason}
            </div>
          )}

          {isReady && (
            <div className="alert alert-info mt-3 mb-0 py-2">
              <i className="bi bi-info-circle-fill me-1"></i> This document pack has been submitted for
              clinical review. Authorized clinicians can approve or return it for correction.
            </div>
          )}

          {isDraft && (
            <div className="alert alert-warning mt-3 mb-0 py-2">
              <i className="bi bi-shield-exclamation me-1"></i> <strong>Draft Notice:</strong> Review
              the three documents below. Clinical approval is required before official generation.
            </div>
          )}

          {isApproved && (
            <div className="alert alert-success mt-3 mb-0 py-2">
              <i className="bi bi-shield-check me-1"></i> <strong>Approved:</strong> Clinically approved
              by Dr. {admission.approver?.name || 'Staff Doctor'} on{' '}
              {formatDateTime(admission.approved_at)}.
            </div>
          )}
        </div>
      </div>

      {/* DOCUMENT 1: DISCHARGE SUMMARY */}
      <div className="document-sheet mb-4">
        <div
          className="text-end text-muted small mb-2 no-print"
          style={{ fontSize: '10.5px', letterSpacing: '0.05em', fontWeight: 600 }}
        >
          DOCUMENT 1 OF 3: DISCHARGE SUMMARY ({admission.format.toUpperCase()})
        </div>

        <div className="discharge-summary-document">
          <div className="text-center mb-3">
            <h2 className="fw-bold mb-0 text-dark" style={{ letterSpacing: '1px' }}>
              GURU HOSPITAL
            </h2>
            <div className="small text-muted">
              32, A/2, Antony Street, Nethaji Bypass Road, Govt. Medical College Hospital Opp.
              Dharmapuri - 636 701
            </div>
            <div className="small text-muted">Ph: 04342-262266 &bull; Cell: +91 7418084266</div>
            <h4 className="fw-bold mt-2 text-decoration-underline text-uppercase">
              DISCHARGE SUMMARY
            </h4>
          </div>

          <table className="table table-sm table-bordered mb-3">
            <tbody>
              <tr>
                <td style={{ width: '50%' }}>
                  <strong>Name :</strong> {admission.patient_name}
                </td>
                <td style={{ width: '50%' }}>
                  <strong>IP. NO :</strong> {admission.ip_no}
                </td>
              </tr>
              <tr>
                <td>
                  <strong>UHID NO :</strong> {admission.uhid}
                </td>
                <td>
                  <strong>Age / Sex :</strong> {admission.age ? `${admission.age} Yrs` : 'N/A'} /{' '}
                  {admission.sex || 'Female'} &nbsp;&nbsp;&nbsp;
                  <strong>Date :</strong> {formatDate(admission.discharge_at)}
                </td>
              </tr>
              <tr>
                <td colSpan={2}>
                  <strong>Address :</strong> {admission.address || 'Not recorded'}
                  {admission.contact && (
                    <>
                      &nbsp;&bull;&nbsp; <strong>Ph :</strong> {admission.contact}
                    </>
                  )}
                </td>
              </tr>
              <tr>
                <td colSpan={2}>
                  <div className="row g-1">
                    <div className="col-md-4">
                      <strong>Date of admission :</strong> {formatDateTime(admission.admission_at)}
                    </div>
                    <div className="col-md-4">
                      <strong>Date of Surgery :</strong> {formatDateTime(admission.procedure_at)}
                    </div>
                    <div className="col-md-4">
                      <strong>Date of Discharge :</strong> {formatDateTime(admission.discharge_at)}
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div className="mb-2">
            <strong>Presenting Complaints :</strong>{' '}
            <span>{admission.clinical_history || 'Admitted for Safe Confinement'}</span>
          </div>

          <div className="mb-2">
            <strong>Clinical Examination :</strong>{' '}
            <span>{admission.examination || 'O/E – No Pallor, No Pedal edema, CVS & RS – NAD'}</span>
          </div>

          <div className="mb-2">
            <strong>Diagnosis :</strong> <span className="fw-bold">{admission.diagnosis}</span>
          </div>

          <div className="mb-2">
            <strong>Surgery :</strong>{' '}
            <span className="fw-bold">{admission.procedure_name || 'Emergency LSCS'}</span>
          </div>

          <table className="table table-sm table-borderless mb-2">
            <tbody>
              <tr>
                <td style={{ width: '50%' }}>
                  <strong>Surgeon :</strong> {admission.surgeon || 'Dr. Ambika M.S (O&G)'}
                </td>
                <td style={{ width: '50%' }}>
                  <strong>Anaesthetist :</strong> {admission.anaesthetist || 'Dr. M.S Karthick'}
                </td>
              </tr>
              <tr>
                <td>
                  <strong>Assisted By S/N :</strong>{' '}
                  {admission.assisted_by || admission.assistant || 'Miss Soudnharya'}
                </td>
                <td>
                  <strong>Anaesthesia :</strong> {admission.anaesthesia_type || 'SA'}
                </td>
              </tr>
            </tbody>
          </table>

          {admission.procedure_note && (
            <div className="mb-3">
              <strong>Procedure note :</strong>
              <p className="mb-0 text-justify" style={{ whiteSpace: 'pre-line' }}>
                {admission.procedure_note}
              </p>
            </div>
          )}

          {/* Baby Details (if delivery) */}
          {admission.surgery_type === 'delivery' && (
            <div className="mb-3 p-3 border rounded bg-light">
              <div className="d-flex justify-content-between align-items-center mb-2">
                <strong className="text-primary text-uppercase">Baby / Newborn Details</strong>
                {(admission.pediatrician_name || admission.consultant) && (
                  <span className="small">
                    <strong>Consultants :</strong>{' '}
                    {admission.pediatrician_name || `${admission.consultant} (Paediatrician)`}
                  </span>
                )}
              </div>

              {(!admission.newborns || admission.newborns.length === 0) ? (
                <p className="mb-0 text-muted small">No newborn records documented.</p>
              ) : (
                <div className="row g-2">
                  {admission.newborns.map((baby, i) => (
                    <div key={i} className={admission.newborns.length > 1 ? 'col-md-6' : 'col-12'}>
                      <div className="p-2 border rounded bg-white h-100 small">
                        <div className="fw-bold text-dark border-bottom pb-1 mb-1">
                          {i === 0
                            ? 'BABY TWIN A (Newborn #1)'
                            : i === 1
                            ? 'BABY TWIN B (Newborn #2)'
                            : `Newborn #${i + 1}`}
                        </div>
                        <div>
                          : Alive {baby.sex || 'Male'} baby on{' '}
                          {formatDate(baby.birth_at || admission.procedure_at)}
                        </div>
                        <div>
                          : Wt :{' '}
                          <strong>
                            {baby.birth_weight_kg
                              ? `${Number(baby.birth_weight_kg).toFixed(3)} kg`
                              : '2.250 kg'}
                          </strong>{' '}
                          at{' '}
                          {baby.birth_at
                            ? new Date(baby.birth_at).toLocaleTimeString('en-US', {
                                hour: '2-digit',
                                minute: '2-digit',
                              })
                            : ''}
                        </div>
                        <div>
                          - Apgar :{' '}
                          <strong>
                            {baby.apgar_1_min || '7'}/10, {baby.apgar_5_min || '8'}/10
                          </strong>
                        </div>
                        {baby.neonatal_care && (
                          <div className="text-danger fw-semibold mt-1">{baby.neonatal_care}</div>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}

          {/* Treatment Given */}
          {admission.treatment_given && (
            <div className="mb-3">
              <strong>Treated with IV Fluids / Medications :</strong>
              <div
                className="p-2 bg-light rounded mt-1 small"
                style={{ whiteSpace: 'pre-line' }}
              >
                {admission.treatment_given}
              </div>
            </div>
          )}

          {/* Investigations */}
          {admission.investigations && (
            <div className="mb-3">
              <strong className="text-uppercase">Investigations :</strong>
              <div
                className="p-2 border rounded mt-1 small bg-white"
                style={{ whiteSpace: 'pre-line', lineHeight: '1.6' }}
              >
                {admission.investigations}
              </div>
            </div>
          )}

          {/* Condition on Discharge */}
          {admission.condition_at_discharge && (
            <div className="mb-3">
              <strong>Condition on discharge :</strong>
              <div
                className="p-2 bg-light rounded mt-1 small"
                style={{ whiteSpace: 'pre-line' }}
              >
                {admission.condition_at_discharge}
              </div>
            </div>
          )}

          {/* Discharge Advice */}
          {admission.discharge_advice && (
            <div className="mb-3">
              <strong className="text-uppercase">Discharge Advice :</strong>
              <div
                className="p-2 border rounded mt-1 small bg-white"
                style={{ whiteSpace: 'pre-line', lineHeight: '1.5' }}
              >
                {admission.discharge_advice}
              </div>
            </div>
          )}

          {/* Emergency Notice */}
          <div className="p-2 border border-danger rounded bg-light small mb-4">
            <strong className="text-danger text-uppercase">When to Contact Immediately :</strong>
            <p className="mb-1 text-muted">
              {admission.emergency_instructions ||
                'To report immediately if there is bleeding, fever, acute wound discharge, abdominal pain, or other emergencies.'}
            </p>
            <div className="fw-bold text-dark">
              Emergency Contact 24x7: 04342-262266, +91-7418084266
            </div>
          </div>

          <div className="row mt-5 pt-4 text-center">
            <div className="col-4">
              <div className="border-top pt-2 small fw-semibold">Staff Nurse / Prepared By</div>
            </div>
            <div className="col-4">
              <div className="border-top pt-2 small fw-semibold">MRD Incharge</div>
            </div>
            <div className="col-4">
              <div className="border-top pt-2 small fw-bold">Surgeon / Consultant Signature</div>
            </div>
          </div>
        </div>
      </div>

      {/* DOCUMENT 2: OPERATION THEATRE NOTES */}
      <div className="document-sheet mb-4 page-break">
        <div
          className="text-end text-muted small mb-2 no-print"
          style={{ fontSize: '10.5px', letterSpacing: '0.05em', fontWeight: 600 }}
        >
          DOCUMENT 2 OF 3: OPERATION THEATRE NOTES
        </div>

        <div
          className="operation-theatre-document"
          style={{
            fontFamily: "Arial, 'Helvetica Neue', Helvetica, sans-serif",
            fontSize: '11pt',
            lineHeight: 1.4,
            color: '#000',
          }}
        >
          <div className="text-center mb-4 pt-2">
            <h3
              className="fw-bold text-decoration-underline text-uppercase mb-0"
              style={{ letterSpacing: '0.5px', fontSize: '14pt' }}
            >
              OPERATION THEATRE NOTES
            </h3>
          </div>

          {/* Metadata 2-column block */}
          <table className="ot-table" style={{ width: '100%', borderCollapse: 'collapse', marginBottom: '4px' }}>
            <tbody>
              <tr>
                <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>NAME</td>
                <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ width: '35%', padding: '2px 0' }}>{admission.patient_name}</td>
                <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>IP.NO</td>
                <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ width: '33%', padding: '2px 0' }}>{admission.ip_no}</td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>AGE/SEX</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>
                  {admission.age ? `${admission.age} / ${admission.sex || 'Female'}` : admission.sex || 'Female'}
                </td>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>UHID NO</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>{admission.uhid}</td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>CONSULTANT</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>{admission.consultant || admission.surgeon || ''}</td>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>PHONE</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>{admission.contact || ''}</td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>ICD CODE</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>{admission.icd_code || ''}</td>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>DATE</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>
                  {formatDate(admission.procedure_at || admission.admission_at)}
                </td>
              </tr>
            </tbody>
          </table>

          {/* Address & Date of Surgery */}
          <table className="ot-table mt-1" style={{ width: '100%', borderCollapse: 'collapse', marginBottom: '4px' }}>
            <tbody>
              <tr>
                <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>ADDRESS</td>
                <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ width: '83%', padding: '2px 0' }}>{admission.address || ''}</td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>DATE OF SURGERY</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>{formatDateTime(admission.procedure_at)}</td>
              </tr>
            </tbody>
          </table>

          {/* Diagnosis & Procedure Done */}
          <table className="ot-table mt-2" style={{ width: '100%', borderCollapse: 'collapse', marginBottom: '4px' }}>
            <tbody>
              <tr>
                <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>DIAGNOSIS</td>
                <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ width: '83%', padding: '2px 0' }}>{admission.diagnosis}</td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>PROCEDURE DONE</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>{admission.procedure_done || ''}</td>
              </tr>
            </tbody>
          </table>

          {/* Surgery & Team Block */}
          <table className="ot-table mt-2" style={{ width: '100%', borderCollapse: 'collapse', marginBottom: '4px' }}>
            <tbody>
              <tr>
                <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>SURGERY</td>
                <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td colSpan={4} style={{ padding: '2px 0' }}>
                  <strong>{admission.procedure_name || 'ELECTIVE LSCS'}</strong>
                </td>
              </tr>
              <tr>
                <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>SURGEON</td>
                <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ width: '35%', padding: '2px 0' }}>{admission.surgeon || admission.consultant || ''}</td>
                <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>ANESTHETIST</td>
                <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ width: '33%', padding: '2px 0' }}>{admission.anaesthetist || ''}</td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>ASSISTANT BY S/N</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>{admission.assisted_by || admission.assistant || ''}</td>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>ANESTHESIA TYPE</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>{admission.anaesthesia_type || 'Spinal'}</td>
              </tr>
            </tbody>
          </table>

          {/* Procedure Note & Incision */}
          <div className="mt-3">
            <div style={{ fontWeight: 'bold', fontSize: '10.5pt', marginTop: '6px', marginBottom: '2px' }}>
              PROCEDURE NOTE :
            </div>
            <div style={{ fontWeight: 'bold', fontSize: '10.5pt', marginBottom: '2px' }}>
              INCISION :
            </div>
            <div style={{ fontSize: '10.5pt', textAlign: 'justify', marginBottom: '6px', lineHeight: 1.45, whiteSpace: 'pre-line' }}>
              {admission.procedure_note || admission.incision || ''}
            </div>
          </div>

          {/* Findings */}
          {admission.findings && (
            <div className="mt-2">
              <div style={{ fontWeight: 'bold', fontSize: '10.5pt', marginTop: '6px', marginBottom: '2px' }}>
                FINDINGS :
              </div>
              <div style={{ fontSize: '10.5pt', textAlign: 'justify', marginBottom: '6px', lineHeight: 1.45, whiteSpace: 'pre-line' }}>
                {admission.findings}
              </div>
            </div>
          )}

          {/* Complications */}
          {admission.complications && (
            <div className="mt-2">
              <div style={{ fontWeight: 'bold', fontSize: '10.5pt', marginTop: '6px', marginBottom: '2px' }}>
                COMPLICATIONS :
              </div>
              <div style={{ fontSize: '10.5pt', textAlign: 'justify', marginBottom: '6px', lineHeight: 1.45, whiteSpace: 'pre-line' }}>
                {admission.complications}
              </div>
            </div>
          )}

          {/* Blood Loss, Specimen, Drain */}
          <table className="ot-table mt-2 mb-2" style={{ width: '100%', borderCollapse: 'collapse' }}>
            <tbody>
              <tr>
                <td style={{ width: '35%', padding: '2px 0' }}>
                  <span style={{ fontWeight: 'bold' }}>BLOOD LOSS (ML) :</span>{' '}
                  <span>{admission.blood_loss_ml ? `${admission.blood_loss_ml} ml` : ''}</span>
                </td>
                <td style={{ width: '35%', padding: '2px 0' }}>
                  <span style={{ fontWeight: 'bold' }}>SPECIMEN :</span>{' '}
                  <span>{admission.specimen || ''}</span>
                </td>
                <td style={{ width: '30%', padding: '2px 0' }}>
                  <span style={{ fontWeight: 'bold' }}>DRAIN :</span>{' '}
                  <span>{admission.drain || ''}</span>
                </td>
              </tr>
            </tbody>
          </table>

          {/* Baby Details (for delivery) */}
          {admission.surgery_type === 'delivery' && (
            <table className="ot-table mt-2" style={{ width: '100%', borderCollapse: 'collapse' }}>
              <tbody>
                <tr>
                  <td style={{ width: '15%', fontWeight: 'bold', verticalAlign: 'top', padding: '2px 0' }}>
                    BABY
                  </td>
                  <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', verticalAlign: 'top', padding: '2px 6px' }}>
                    :
                  </td>
                  <td style={{ width: '83%', padding: '2px 0' }}>
                    {admission.newborns && admission.newborns.length > 0 ? (
                      admission.newborns.map((baby, i) => (
                        <div key={i} className="mb-1">
                          Alive {baby.sex || 'Female'} baby on{' '}
                          {formatDateTime(baby.birth_at || admission.procedure_at)}
                          <br />
                          Baby Weight{' '}
                          {baby.birth_weight_kg
                            ? `${Number(baby.birth_weight_kg).toFixed(3)}Kg`
                            : '2.650Kg'}
                          {baby.neonatal_care && (
                            <span className="text-muted small"> ({baby.neonatal_care})</span>
                          )}
                        </div>
                      ))
                    ) : (
                      <div>
                        Alive Female baby on {formatDateTime(admission.procedure_at)}
                        <br />
                        Baby Weight 2.650Kg
                      </div>
                    )}
                  </td>
                </tr>
              </tbody>
            </table>
          )}

          {/* Post-Operative Orders */}
          {admission.postoperative_orders && (
            <div className="mt-2">
              <div style={{ fontWeight: 'bold', fontSize: '10.5pt', marginTop: '6px', marginBottom: '2px' }}>
                POST-OPERATIVE ORDERS :
              </div>
              <div style={{ fontSize: '10.5pt', textAlign: 'justify', marginBottom: '6px', lineHeight: 1.45, whiteSpace: 'pre-line' }}>
                {admission.postoperative_orders}
              </div>
            </div>
          )}

          {/* Surgeon Seal & Signature */}
          <div className="text-end mt-5 pt-4" style={{ pageBreakInside: 'avoid' }}>
            <span className="fw-bold" style={{ fontSize: '11pt', letterSpacing: '0.5px' }}>
              SURGEON SEAL &amp; SIGNATURE
            </span>
          </div>
        </div>
      </div>

      {/* DOCUMENT 3: MRD FORM */}
      <div className="document-sheet mb-4 page-break">
        <div
          className="text-end text-muted small mb-2 no-print"
          style={{ fontSize: '10.5px', letterSpacing: '0.05em', fontWeight: 600 }}
        >
          DOCUMENT 3 OF 3: MEDICAL RECORDS DEPARTMENT (MRD) FORM
        </div>

        <div>
          <h2 className="text-center fw-bold">GURU HOSPITAL</h2>
          <h3 className="text-center fw-bold text-uppercase" style={{ fontSize: '1.25rem' }}>
            MEDICAL RECORDS DEPARTMENT
          </h3>
          <p className="text-center text-muted small">
            32, A/2, Antony Street, Nethaji Bypass Road, Govt. Medical College Hospital Opp.
            Dharmapuri - 636 701.
            <br />
            Ph: 04342-262266, Cell: +91 7418084266
          </p>

          <table className="table table-bordered mt-3">
            <tbody>
              <tr>
                <th style={{ width: '35%' }}>Patient Name</th>
                <td>{admission.patient_name}</td>
              </tr>
              <tr>
                <th>Discharge Date & Time</th>
                <td>{formatDateTime(admission.discharge_at)}</td>
              </tr>
              <tr>
                <th>MRD No.</th>
                <td>{admission.mrd_no || 'Not recorded'}</td>
              </tr>
              <tr>
                <th>IP No.</th>
                <td>{admission.ip_no}</td>
              </tr>
              <tr>
                <th>No. of Pages</th>
                <td>Calculate after final combined PDF is rendered</td>
              </tr>
              <tr>
                <th>Payment Mode</th>
                <td>{admission.payment_mode || 'Not recorded'}</td>
              </tr>
              <tr>
                <th>Hand Over Sign</th>
                <td>________________________________</td>
              </tr>
              <tr>
                <th>Floor Incharge Sign</th>
                <td>________________________________</td>
              </tr>
              <tr>
                <th>MRD Incharge Sign</th>
                <td>________________________________</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      {/* Return for Correction Modal */}
      {returnModalOpen && (
        <div
          className="modal fade show d-block"
          tabIndex={-1}
          style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
        >
          <div className="modal-dialog modal-dialog-centered">
            <form onSubmit={handleConfirmReturn} className="modal-content border-0 shadow-lg">
              <div className="modal-header">
                <h5 className="modal-title fw-bold" style={{ letterSpacing: '-0.01em' }}>
                  Return Pack for Clinical Correction
                </h5>
                <button
                  type="button"
                  className="btn-close"
                  onClick={() => setReturnModalOpen(false)}
                ></button>
              </div>
              <div className="modal-body p-4">
                <p className="small text-muted mb-3">
                  Please explain the required corrections or missing details. The record will return
                  to draft status for editing.
                </p>
                <div className="mb-3">
                  <label className="form-label required fw-semibold">
                    Correction Reason / Discrepancy Note
                  </label>
                  <textarea
                    className="form-control"
                    rows={4}
                    value={correctionReason}
                    onChange={(e) => setCorrectionReason(e.target.value)}
                    placeholder="e.g. Newborn weight mismatch; clarify anesthesia type; update medication orders."
                    required
                  ></textarea>
                </div>
              </div>
              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-outline-secondary"
                  onClick={() => setReturnModalOpen(false)}
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="btn btn-danger"
                  disabled={submittingReturn || !correctionReason.trim()}
                >
                  {submittingReturn ? 'Returning...' : 'Confirm & Return'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Create Revision Modal */}
      {revisionModalOpen && (
        <div
          className="modal fade show d-block"
          tabIndex={-1}
          style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
        >
          <div className="modal-dialog modal-dialog-centered">
            <form onSubmit={handleConfirmRevision} className="modal-content border-0 shadow-lg">
              <div className="modal-header">
                <h5 className="modal-title fw-bold" style={{ letterSpacing: '-0.01em' }}>
                  Create New Clinical Revision
                </h5>
                <button
                  type="button"
                  className="btn-close"
                  onClick={() => setRevisionModalOpen(false)}
                ></button>
              </div>
              <div className="modal-body p-4">
                <p className="small text-muted mb-3">
                  This admission is currently approved/generated. Creating a new revision will
                  preserve an immutable historical snapshot and reset the admission to draft for
                  correction.
                </p>
                <div className="mb-3">
                  <label className="form-label required fw-semibold">Reason for Revision</label>
                  <textarea
                    className="form-control"
                    rows={3}
                    value={revisionReason}
                    onChange={(e) => setRevisionReason(e.target.value)}
                    placeholder="e.g. Retrospective pathology findings added; dosage adjustment."
                    required
                  ></textarea>
                </div>
              </div>
              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-outline-secondary"
                  onClick={() => setRevisionModalOpen(false)}
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                  disabled={submittingRevision || !revisionReason.trim()}
                >
                  {submittingRevision ? 'Creating...' : 'Create Revision & Unlock'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AppLayout>
  );
}

