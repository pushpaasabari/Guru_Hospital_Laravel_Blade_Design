import { useState, useEffect, useMemo } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import ActionDropdown, {
  buildEditStageMenuItems,
  buildPdfDownloadMenuItems,
} from '../../Components/ActionDropdown';
import { resolveMrdNo } from '../../utils/inputNormalizers';
import {
  formatTreatmentCourse,
  formatInvestigations,
  formatConditionOnDischarge,
  formatMedications,
  formatAdviceAndReview,
  formatNewborns,
  formatSurgeryPerformed,
  formatSurgicalPastHistory,
  formatSurgicalInvestigations,
  formatSurgicalAdvice,
  formatSurgicalDrugs,
  getInsuranceDetailsList,
  formatPaymentSummary,
} from '../../utils/dischargeFormatter';

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
  address_line1?: string;
  address_line2?: string;
  address_line3?: string;
  contact?: string;
  admission_at?: string;
  discharge_at?: string;
  procedure_at?: string;
  consultant?: string;
  department?: string;
  mrd_no?: string;
  icd_code?: string;
  payment_mode?: string;
  insurance_type?: string;
  insurance_company?: string;
  policy_number?: string;
  pre_auth_no?: string;
  pre_auth_status?: string;
  approved_amount?: string | number;
  policy_holder_name?: string;
  policy_holder_relation?: string;
  policy_validity?: string;
  status: string;
  format: 'general' | 'cmchistn' | 'employee' | 'private';
  surgery_type: 'delivery' | 'other' | 'medical';
  procedure_name?: string;
  procedure_done?: string;
  surgeon?: string;
  assisted_surgeon?: string;
  anaesthetist?: string;
  assisted_anaesthetist?: string;
  assisted_by?: string;
  assistant?: string;
  staff_nurse?: string;
  ot_technician?: string;
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
  initialDoc?: string;
  initialMode?: 'sheet' | 'pdf';
}

function InsuranceCoverageStrip({ admission }: { admission: Admission }) {
  const items = getInsuranceDetailsList(admission);
  if (items.length === 0) return null;

  const chunks: Array<typeof items> = [];
  for (let i = 0; i < items.length; i += 3) {
    chunks.push(items.slice(i, i + 3));
  }

  return (
    <table
      className="w-100 mb-2"
      style={{
        borderCollapse: 'collapse',
        tableLayout: 'fixed',
        border: '1px solid #94a3b8',
        backgroundColor: '#f8fafc',
      }}
    >
      <tbody>
        {chunks.map((row, rIdx) => (
          <tr key={rIdx}>
            {row.map((item, cIdx) => (
              <td key={cIdx} style={{ border: '1px solid #cbd5e1', padding: '4px 6px', verticalAlign: 'top' }}>
                <span style={{ fontSize: '6.8pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>
                  {item.label}
                </span>
                <span style={{ fontSize: '8.8pt', fontWeight: 700, color: '#000', display: 'block' }}>
                  {item.value}
                </span>
              </td>
            ))}
            {Array.from({ length: Math.max(0, 3 - row.length) }).map((_, emptyIdx) => (
              <td key={`e-${emptyIdx}`} style={{ border: '1px solid #cbd5e1', padding: '4px 6px' }} />
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}

export default function Pack({ admission, initialDoc = 'all', initialMode = 'sheet' }: Props) {
  const { auth } = usePage<{ auth: { user: any } }>().props;
  const user = auth.user;

  const [activeDoc, setActiveDoc] = useState<string>(initialDoc);
  const [viewMode, setViewMode] = useState<'sheet' | 'pdf'>(initialMode);
  const [returnModalOpen, setReturnModalOpen] = useState(false);
  const [revisionModalOpen, setRevisionModalOpen] = useState(false);

  const deliveryFormatted = useMemo(() => {
    if (admission.surgery_type !== 'delivery') return null;
    return {
      treatmentRows: formatTreatmentCourse(admission.treatment_course || admission.treatment_given),
      investigationList: formatInvestigations(admission.investigations),
      conditionData: formatConditionOnDischarge(admission.discharge_condition || admission.condition_at_discharge),
      medicationList: formatMedications(admission.medication_orders),
      adviceData: formatAdviceAndReview(
        admission.discharge_advice,
        admission.follow_up,
        admission.discharge_at,
        `${admission.procedure_name || ''} ${admission.procedure_done || ''}`
      ),
      newbornCards: formatNewborns(admission),
      surgeryPerformed: formatSurgeryPerformed(admission),
    };
  }, [admission]);

  const surgicalFormatted = useMemo(() => {
    if (admission.surgery_type === 'delivery') return null;
    return {
      hist: formatSurgicalPastHistory(admission),
      inv: formatSurgicalInvestigations(admission),
      surgAdvice: formatSurgicalAdvice(admission),
      surgDrugs: formatSurgicalDrugs(admission.medication_orders),
    };
  }, [admission]);

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const autoDownload = params.get('auto_download');
    if (autoDownload) {
      const downloadUrl = `/admissions/${admission.id}/documents/pdf/${autoDownload}`;
      const link = document.createElement('a');
      link.href = downloadUrl;
      link.setAttribute('download', '');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }, [admission.id]);

  const getDocTitle = (doc: string) => {
    switch (doc) {
      case 'discharge_summary':
        return '1. Discharge Summary';
      case 'operation_notes':
        return '2. Operation Theatre Notes';
      case 'mrd_form':
        return '3. MRD Inpatient Form';
      default:
        return 'Complete 3-in-1 Dossier Pack';
    }
  };

  const getPdfRouteType = (doc: string) => {
    switch (doc) {
      case 'discharge_summary':
        return 'discharge-summary';
      case 'operation_notes':
        return 'operation-notes';
      case 'mrd_form':
        return 'mrd-form';
      default:
        return 'pack';
    }
  };

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
                    : admission.surgery_type === 'medical'
                    ? 'Medical Case (Non-Surgical)'
                    : 'Other Surgery'}
                </strong>{' '}
                &bull; Consultant:{' '}
                <strong className="text-dark">{admission.consultant || 'Not recorded'}</strong>
              </div>
            </div>

            <div className="d-flex flex-wrap gap-2">
              <Link href="/admissions" prefetch className="btn btn-outline-secondary">
                <i className="bi bi-arrow-left me-1"></i> Back
              </Link>

              <Link href={`/documents/${admission.id}/history`} prefetch className="btn btn-outline-secondary">
                <i className="bi bi-clock-history me-1"></i> Audit Trail
              </Link>

              {canBeEdited && user?.can_edit && (
                <ActionDropdown
                  groupClassName="btn-group"
                  toggleTitle="Toggle Sub-Forms"
                  fontSize="13px"
                  items={buildEditStageMenuItems(admission.id)}
                  primaryAction={
                    <Link href={`/admissions/${admission.id}/edit`} prefetch className="btn btn-outline-secondary">
                      <i className="bi bi-pencil me-1"></i> Edit Data
                    </Link>
                  }
                />
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

              {/* Direct PDF Download Buttons (No approval required) */}
              <ActionDropdown
                groupClassName="btn-group shadow-sm"
                toggleClassName="btn btn-primary dropdown-toggle dropdown-toggle-split"
                toggleTitle="Toggle PDF Options"
                fontSize="13px"
                items={buildPdfDownloadMenuItems(admission.id, 'Separate PDF Documents')}
                primaryAction={
                  <a
                    href={`/admissions/${admission.id}/documents/pdf/discharge-summary`}
                    target="_blank"
                    rel="noreferrer"
                    className="btn btn-primary fw-semibold d-inline-flex align-items-center gap-1"
                    title="Generate and download Discharge Summary PDF"
                  >
                    <i className="bi bi-file-earmark-medical me-1"></i> 1. Discharge Summary PDF
                  </a>
                }
              />

              {(user?.is_admin || user?.role === 'doctor') && (
                <button
                  type="button"
                  className="btn btn-outline-secondary"
                  onClick={() => setRevisionModalOpen(true)}
                >
                  <i className="bi bi-pencil-square me-1"></i> Create Revision
                </button>
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

          <div className="alert alert-success mt-3 mb-0 py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <i className="bi bi-check-circle-fill me-1 text-success"></i>{' '}
              <strong>Documents Ready for Download:</strong> Complete discharge documentation is finalized. You can directly download official PDFs below without needing approval.
            </div>
            <div className="d-flex gap-2">
              <a
                href={`/admissions/${admission.id}/documents/pdf/discharge-summary`}
                className="btn btn-sm btn-success fw-semibold"
                target="_blank"
                rel="noreferrer"
              >
                <i className="bi bi-download me-1"></i> Discharge Summary PDF
              </a>
              <a
                href={`/admissions/${admission.id}/documents/pdf/pack`}
                className="btn btn-sm btn-outline-dark fw-semibold"
                target="_blank"
                rel="noreferrer"
              >
                <i className="bi bi-collection me-1"></i> Complete Pack PDF
              </a>
            </div>
          </div>
        </div>
      </div>

      {/* Document Selector & Preview Engine Bar */}
      <div className="card mb-4 no-print shadow-sm border-0" style={{ borderRadius: '20px' }}>
        <div className="card-body p-3">
          <div className="d-flex flex-wrap justify-content-between align-items-center gap-3">
            {/* Tabs for Separate Previews */}
            <div className="d-flex flex-wrap align-items-center gap-2">
              <span className="text-muted small fw-bold text-uppercase me-1" style={{ fontSize: '10.5px', letterSpacing: '0.04em' }}>
                Preview Document:
              </span>
              <div className="btn-group btn-group-sm" role="group">
                <button
                  type="button"
                  className={`btn py-2 px-3 fw-semibold ${activeDoc === 'all' ? 'btn-primary' : 'btn-outline-secondary'}`}
                  onClick={() => setActiveDoc('all')}
                >
                  <i className="bi bi-collection me-1"></i> All Documents
                </button>
                <button
                  type="button"
                  className={`btn py-2 px-3 fw-semibold ${activeDoc === 'discharge_summary' ? 'btn-primary' : 'btn-outline-secondary'}`}
                  onClick={() => setActiveDoc('discharge_summary')}
                >
                  <i className="bi bi-file-earmark-medical me-1"></i> 1. Discharge Summary
                </button>
                <button
                  type="button"
                  className={`btn py-2 px-3 fw-semibold ${activeDoc === 'operation_notes' ? 'btn-primary' : 'btn-outline-secondary'}`}
                  onClick={() => setActiveDoc('operation_notes')}
                >
                  <i className="bi bi-journal-medical me-1"></i> 2. OT Notes
                </button>
                <button
                  type="button"
                  className={`btn py-2 px-3 fw-semibold ${activeDoc === 'mrd_form' ? 'btn-primary' : 'btn-outline-secondary'}`}
                  onClick={() => setActiveDoc('mrd_form')}
                >
                  <i className="bi bi-file-earmark-check me-1"></i> 3. MRD Form
                </button>
              </div>
            </div>

            {/* View Engine Toggle: Sheet View vs Real Playwright PDF */}
            <div className="d-flex align-items-center gap-2">
              <span className="text-muted small fw-bold text-uppercase me-1" style={{ fontSize: '10.5px', letterSpacing: '0.04em' }}>
                Engine:
              </span>
              <div className="btn-group btn-group-sm" role="group">
                <button
                  type="button"
                  className={`btn py-2 px-3 fw-semibold ${viewMode === 'sheet' ? 'btn-dark' : 'btn-outline-secondary'}`}
                  onClick={() => setViewMode('sheet')}
                >
                  <i className="bi bi-file-text me-1"></i> Formatted Sheet
                </button>
                <button
                  type="button"
                  className={`btn py-2 px-3 fw-semibold ${viewMode === 'pdf' ? 'btn-danger text-white' : 'btn-outline-danger'}`}
                  onClick={() => setViewMode('pdf')}
                >
                  <i className="bi bi-file-earmark-pdf-fill me-1"></i> Playwright PDF
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {viewMode === 'pdf' ? (
        <div className="card shadow-sm border-0 mb-4" style={{ borderRadius: '20px', overflow: 'hidden' }}>
          <div className="card-header bg-white p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div className="d-flex align-items-center gap-2">
              <span className="badge bg-primary text-white text-uppercase" style={{ fontSize: '11px' }}>
                Playwright Chromium Preview
              </span>
              <strong className="text-dark small">
                {getDocTitle(activeDoc)}
              </strong>
              <span className="badge bg-light text-muted border" style={{ fontSize: '10.5px' }}>
                IP: {admission.ip_no}
              </span>
            </div>

            <div className="d-flex gap-2">
              <a
                href={`/admissions/${admission.id}/documents/pdf/${getPdfRouteType(activeDoc)}?inline=1`}
                target="_blank"
                rel="noreferrer"
                className="btn btn-sm btn-outline-secondary"
                title="Open PDF in new tab"
              >
                <i className="bi bi-box-arrow-up-right me-1"></i> New Tab Fullscreen
              </a>
              <a
                href={`/admissions/${admission.id}/documents/pdf/${getPdfRouteType(activeDoc)}`}
                download
                className="btn btn-sm btn-primary"
                title="Download standard A4 PDF"
              >
                <i className="bi bi-download me-1"></i> Download A4 PDF
              </a>
            </div>
          </div>

          <div className="card-body p-0" style={{ background: '#525659' }}>
            <div style={{ width: '100%', height: '920px' }}>
              <iframe
                src={`/admissions/${admission.id}/documents/pdf/${getPdfRouteType(activeDoc)}?inline=1`}
                style={{ width: '100%', height: '100%', border: 'none' }}
                title={`Playwright PDF Preview — ${getDocTitle(activeDoc)}`}
              />
            </div>
          </div>
        </div>
      ) : (
        <>
          {/* DOCUMENT 1: DISCHARGE SUMMARY */}
          {(activeDoc === 'all' || activeDoc === 'discharge_summary') && (() => {
            if (admission.surgery_type === 'delivery' && deliveryFormatted) {
              const {
                treatmentRows,
                investigationList,
                conditionData,
                medicationList,
                adviceData,
                newbornCards,
                surgeryPerformed,
              } = deliveryFormatted;

            return (
              <div className="document-sheet mb-4">
                <div
                  className="text-end text-muted small mb-2 no-print"
                  style={{ fontSize: '10.5px', letterSpacing: '0.05em', fontWeight: 600 }}
                >
                  DOCUMENT 1 OF 3: DISCHARGE SUMMARY ({admission.format.toUpperCase()})
                </div>

                <div
                  className="discharge-summary-document"
                  style={{
                    fontFamily: "Arial, 'Helvetica Neue', Helvetica, sans-serif",
                    fontSize: '9.5pt',
                    lineHeight: 1.4,
                    color: '#000',
                  }}
                >
                  {/* Space for Pre-printed Letterhead (First page only) */}
                  <div style={{ height: '38mm' }}></div>

                  <div className="text-center mb-3">
                    <h3
                      className="fw-bold text-uppercase mb-0"
                      style={{ letterSpacing: '0.5px', fontSize: '15pt', color: '#111' }}
                    >
                      DISCHARGE SUMMARY
                    </h3>
                  </div>

                  {/* Box 1: Patient Demographics */}
                  <table
                    className="w-100 mb-2"
                    style={{
                      borderCollapse: 'collapse',
                      tableLayout: 'fixed',
                      border: '1px solid #94a3b8',
                      backgroundColor: '#f1f5f9',
                    }}
                  >
                    <colgroup>
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                      <col style={{ width: '8.3333%' }} />
                    </colgroup>
                    <tbody>
                      <tr>
                        <td colSpan={3} style={{ border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>PATIENT NAME</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{admission.patient_name}</span>
                        </td>
                        <td colSpan={3} style={{ border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>AGE / SEX</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{admission.age ? `${admission.age} / ` : ''}{admission.sex || 'Female'}</span>
                        </td>
                        <td colSpan={3} style={{ border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>IP NO.</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{admission.ip_no}</span>
                        </td>
                        <td colSpan={3} style={{ border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>UHID NO.</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{admission.uhid}</span>
                        </td>
                      </tr>
                      <tr>
                        <td colSpan={4} style={{ border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>PHONE</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{admission.contact || '—'}</span>
                        </td>
                        <td colSpan={4} style={{ border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>ADMISSION</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{formatDateTime(admission.admission_at)}</span>
                        </td>
                        <td colSpan={4} style={{ border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>DISCHARGE</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{formatDateTime(admission.discharge_at)}</span>
                        </td>
                      </tr>
                      <tr>
                        <td colSpan={12} style={{ border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>ADDRESS</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>
                            {admission.address?.replace(/\r?\n/g, ', ') || '—'}
                            {admission.picme_no && (
                              <span style={{ fontSize: '8pt', color: '#475569', fontWeight: 'normal', marginLeft: '8px' }}>
                                (PICME: {admission.picme_no})
                              </span>
                            )}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  <InsuranceCoverageStrip admission={admission} />

                  {/* Box 2: Clinical / Surgical Team */}
                  <table
                    className="w-100 mb-2"
                    style={{
                      borderCollapse: 'collapse',
                      tableLayout: 'fixed',
                      border: '1px solid #94a3b8',
                      backgroundColor: '#ffffff',
                    }}
                  >
                    <tbody>
                      <tr>
                        <td style={{ width: '33.3%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>CONSULTANT DOCTOR</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{admission.consultant || admission.surgeon || '—'}</span>
                        </td>
                        <td style={{ width: '33.3%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>DEPARTMENT</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{admission.department || '—'}</span>
                        </td>
                        <td style={{ width: '33.4%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>DATE OF SURGERY</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{formatDateTime(admission.procedure_at)}</span>
                        </td>
                      </tr>
                      <tr>
                        <td style={{ width: '33.3%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>SURGEON</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{admission.surgeon || admission.consultant || '—'}</span>
                        </td>
                        <td style={{ width: '33.3%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>ANAESTHETIST</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>
                            {admission.anaesthetist
                              ? `${admission.anaesthetist}${admission.anaesthesia_type ? ` (${admission.anaesthesia_type})` : ''}`
                              : '—'}
                          </span>
                        </td>
                        <td style={{ width: '33.4%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>ASSISTED BY</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{admission.assisted_by || admission.assistant || '—'}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  {/* Box 3: Diagnosis & Surgery Performed */}
                  <table
                    className="w-100 mb-2"
                    style={{
                      borderCollapse: 'collapse',
                      tableLayout: 'fixed',
                      border: '1px solid #94a3b8',
                      backgroundColor: '#f1f5f9',
                    }}
                  >
                    <tbody>
                      <tr>
                        <td style={{ width: '22%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7.5pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase' }}>DIAGNOSIS</span>
                        </td>
                        <td style={{ width: '78%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '9.5pt', fontWeight: 700, color: '#000' }}>{admission.diagnosis}</span>
                        </td>
                      </tr>
                      <tr>
                        <td style={{ width: '22%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7.5pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase' }}>
                            {admission.surgery_type === 'medical' ? 'CLINICAL CARE' : 'SURGERY PERFORMED'}
                          </span>
                        </td>
                        <td style={{ width: '78%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000' }}>{surgeryPerformed}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  {/* Section: Presenting Complaints */}
                  {admission.clinical_history && (
                    <>
                      <div
                        style={{
                          fontSize: '10pt',
                          fontWeight: 'bold',
                          color: '#111',
                          textTransform: 'uppercase',
                          borderBottom: '1.5px solid #222',
                          paddingBottom: '2px',
                          marginTop: '12px',
                          marginBottom: '4px',
                        }}
                      >
                        PRESENTING COMPLAINTS
                      </div>
                      <div style={{ fontSize: '9pt', lineHeight: 1.4, color: '#000', textAlign: 'justify', marginBottom: '6px', whiteSpace: 'pre-line' }}>
                        {admission.clinical_history}
                      </div>
                    </>
                  )}

                  {/* Section: Clinical Examination */}
                  {admission.examination && (
                    <>
                      <div
                        style={{
                          fontSize: '10pt',
                          fontWeight: 'bold',
                          color: '#111',
                          textTransform: 'uppercase',
                          borderBottom: '1.5px solid #222',
                          paddingBottom: '2px',
                          marginTop: '12px',
                          marginBottom: '4px',
                        }}
                      >
                        CLINICAL EXAMINATION
                      </div>
                      <div style={{ fontSize: '9pt', lineHeight: 1.4, color: '#000', textAlign: 'justify', marginBottom: '6px', whiteSpace: 'pre-line' }}>
                        {admission.examination}
                      </div>
                    </>
                  )}

                  {/* Section: Procedure Note */}
                  {admission.procedure_note && (
                    <>
                      <div
                        style={{
                          fontSize: '10pt',
                          fontWeight: 'bold',
                          color: '#111',
                          textTransform: 'uppercase',
                          borderBottom: '1.5px solid #222',
                          paddingBottom: '2px',
                          marginTop: '12px',
                          marginBottom: '4px',
                        }}
                      >
                        {admission.surgery_type === 'medical' ? 'CLINICAL COURSE NOTE' : 'PROCEDURE NOTE'}
                      </div>
                      <div style={{ fontSize: '9pt', lineHeight: 1.4, color: '#000', textAlign: 'justify', marginBottom: '6px', whiteSpace: 'pre-line' }}>
                        {admission.procedure_note}
                      </div>
                    </>
                  )}

                  {/* Section: Baby / Newborn Details */}
                  {newbornCards.length > 0 && (
                    <>
                      <div
                        style={{
                          fontSize: '10pt',
                          fontWeight: 'bold',
                          color: '#111',
                          textTransform: 'uppercase',
                          borderBottom: '1.5px solid #222',
                          paddingBottom: '2px',
                          marginTop: '12px',
                          marginBottom: '4px',
                        }}
                      >
                        BABY / NEWBORN DETAILS
                      </div>
                      <table
                        className="w-100 mb-2"
                        style={{
                          borderCollapse: 'collapse',
                          tableLayout: 'fixed',
                          border: '1px solid #94a3b8',
                        }}
                      >
                        <thead>
                          <tr>
                            {newbornCards.map((card, i) => (
                              <th
                                key={i}
                                style={{
                                  backgroundColor: '#000',
                                  color: '#fff',
                                  fontSize: '8.5pt',
                                  fontWeight: 'bold',
                                  padding: '4px 6px',
                                  textAlign: 'left',
                                  border: '1px solid #000',
                                  width: newbornCards.length > 1 ? `${100 / newbornCards.length}%` : '100%',
                                }}
                              >
                                {card.title}
                              </th>
                            ))}
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            {newbornCards.map((card, i) => (
                              <td key={i} style={{ border: '1px solid #94a3b8', padding: '4px 6px', fontSize: '8.5pt', verticalAlign: 'top' }}>
                                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                                  <tbody>
                                    <tr>
                                      <td style={{ width: '28%', fontSize: '7pt', fontWeight: 'bold', color: '#475569', padding: '2px', border: 'none' }}>SEX</td>
                                      <td style={{ fontSize: '8.5pt', fontWeight: 'bold', padding: '2px', border: 'none' }}>: {card.sex}</td>
                                    </tr>
                                    <tr>
                                      <td style={{ fontSize: '7pt', fontWeight: 'bold', color: '#475569', padding: '2px', border: 'none' }}>BORN</td>
                                      <td style={{ fontSize: '8.5pt', padding: '2px', border: 'none' }}>: {card.born}</td>
                                    </tr>
                                    <tr>
                                      <td style={{ fontSize: '7pt', fontWeight: 'bold', color: '#475569', padding: '2px', border: 'none' }}>MODE</td>
                                      <td style={{ fontSize: '8.5pt', padding: '2px', border: 'none' }}>: {card.mode}</td>
                                    </tr>
                                    <tr>
                                      <td style={{ fontSize: '7pt', fontWeight: 'bold', color: '#475569', padding: '2px', border: 'none' }}>WEIGHT</td>
                                      <td style={{ fontSize: '8.5pt', fontWeight: 'bold', padding: '2px', border: 'none' }}>: {card.weight}</td>
                                    </tr>
                                    <tr>
                                      <td style={{ fontSize: '7pt', fontWeight: 'bold', color: '#475569', padding: '2px', border: 'none' }}>APGAR</td>
                                      <td style={{ fontSize: '8.5pt', fontWeight: 'bold', padding: '2px', border: 'none' }}>: {card.apgar}</td>
                                    </tr>
                                    <tr>
                                      <td style={{ fontSize: '7pt', fontWeight: 'bold', color: '#475569', padding: '2px', border: 'none', verticalAlign: 'top' }}>NEONATAL CARE</td>
                                      <td style={{ fontSize: '8.5pt', padding: '2px', border: 'none', verticalAlign: 'top' }}>: {card.neonatal_care}</td>
                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            ))}
                          </tr>
                        </tbody>
                      </table>
                    </>
                  )}

                  {/* Web Separator between Page 1 and Page 2 */}
                  <div className="my-4 py-2 border-top border-bottom text-center text-muted small bg-light no-print">
                    <i className="bi bi-file-earmark-break me-1"></i> Page 2 (Running Header: {admission.patient_name} &bull; IP: {admission.ip_no} &bull; UHID: {admission.uhid})
                  </div>

                  {/* Treatment Given */}
                  {treatmentRows.length > 0 && (
                    <>
                      <div
                        style={{
                          fontSize: '10pt',
                          fontWeight: 'bold',
                          color: '#111',
                          textTransform: 'uppercase',
                          borderBottom: '1.5px solid #222',
                          paddingBottom: '2px',
                          marginTop: '12px',
                          marginBottom: '4px',
                        }}
                      >
                        TREATMENT GIVEN
                      </div>
                      <table
                        className="w-100 mb-2"
                        style={{
                          borderCollapse: 'collapse',
                          tableLayout: 'fixed',
                          border: '1px solid #94a3b8',
                        }}
                      >
                        <thead>
                          <tr>
                            <th style={{ backgroundColor: '#000', color: '#fff', fontSize: '8pt', fontWeight: 'bold', padding: '4px 6px', textAlign: 'left', border: '1px solid #000', width: '60%' }}>
                              Medication / fluid
                            </th>
                            <th style={{ backgroundColor: '#000', color: '#fff', fontSize: '8pt', fontWeight: 'bold', padding: '4px 6px', textAlign: 'left', border: '1px solid #000', width: '40%' }}>
                              Dose & route
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          {treatmentRows.map((row, i) => (
                            <tr key={i}>
                              <td style={{ border: '1px solid #94a3b8', padding: '4px 6px', fontSize: '9pt', fontWeight: 500 }}>{row.medicine}</td>
                              <td style={{ border: '1px solid #94a3b8', padding: '4px 6px', fontSize: '9pt' }}>{row.dose}</td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </>
                  )}

                  {/* Investigations */}
                  {investigationList.length > 0 && (
                    <>
                      <div
                        style={{
                          fontSize: '10pt',
                          fontWeight: 'bold',
                          color: '#111',
                          textTransform: 'uppercase',
                          borderBottom: '1.5px solid #222',
                          paddingBottom: '2px',
                          marginTop: '12px',
                          marginBottom: '4px',
                        }}
                      >
                        INVESTIGATIONS
                      </div>
                      <table
                        className="w-100 mb-2"
                        style={{
                          borderCollapse: 'collapse',
                          tableLayout: 'fixed',
                          border: '1px solid #94a3b8',
                          backgroundColor: '#ffffff',
                        }}
                      >
                        <tbody>
                          {(() => {
                            const invCols = investigationList.length > 10 ? 6 : 5;
                            const colWidth = investigationList.length > 10 ? '16.66%' : '20%';
                            return Array.from({ length: Math.ceil(investigationList.length / invCols) }).map((_, rIdx) => {
                              const chunk = investigationList.slice(rIdx * invCols, rIdx * invCols + invCols);
                              return (
                                <tr key={rIdx}>
                                  {chunk.map((inv, cIdx) => (
                                    <td key={cIdx} style={{ width: colWidth, border: '1px solid #cbd5e1', padding: '3px 5px' }}>
                                      <span style={{ fontSize: '6.4pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>
                                        {inv.label} {inv.unit ? <span style={{ fontWeight: 400, textTransform: 'none', color: '#64748b', fontSize: '5.6pt' }}>({inv.unit})</span> : null}
                                      </span>
                                      <span style={{ fontSize: '8.4pt', fontWeight: 700, color: '#000', display: 'block' }}>{inv.value}</span>
                                      {inv.normal_range ? (
                                        <span style={{ fontSize: '5.6pt', color: '#64748b', display: 'block', marginTop: '1px' }}>
                                          Ref: {inv.normal_range}
                                        </span>
                                      ) : null}
                                    </td>
                                  ))}
                                  {chunk.length < invCols &&
                                    Array.from({ length: invCols - chunk.length }).map((_, padIdx) => (
                                      <td key={`pad-${padIdx}`} style={{ width: colWidth, border: '1px solid #cbd5e1' }}>&nbsp;</td>
                                    ))}
                                </tr>
                              );
                            });
                          })()}
                        </tbody>
                      </table>
                    </>
                  )}

                  {/* Condition on Discharge */}
                  <div
                    style={{
                      fontSize: '10pt',
                      fontWeight: 'bold',
                      color: '#111',
                      textTransform: 'uppercase',
                      borderBottom: '1.5px solid #222',
                      paddingBottom: '2px',
                      marginTop: '12px',
                      marginBottom: '4px',
                    }}
                  >
                    CONDITION ON DISCHARGE
                  </div>
                  <table
                    className="w-100 mb-2"
                    style={{
                      borderCollapse: 'collapse',
                      tableLayout: 'fixed',
                      border: '1px solid #94a3b8',
                      backgroundColor: '#ffffff',
                    }}
                  >
                    <tbody>
                      <tr>
                        <td style={{ width: '20%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>General condition</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{conditionData.general}</span>
                        </td>
                        <td style={{ width: '18%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>BP</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{conditionData.bp}</span>
                        </td>
                        <td style={{ width: '17%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>Pulse</span>
                          <span style={{ fontSize: '9.2pt', fontWeight: 700, color: '#000', display: 'block' }}>{conditionData.pulse}</span>
                        </td>
                        <td style={{ width: '45%', border: '1px solid #cbd5e1', padding: '4px 6px' }}>
                          <span style={{ fontSize: '7pt', fontWeight: 700, color: '#475569', textTransform: 'uppercase', display: 'block', marginBottom: '1px' }}>P/A</span>
                          <span style={{ fontSize: '8.8pt', fontWeight: 700, color: '#000', display: 'block' }}>{conditionData.pa}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  {/* Discharge Medications */}
                  {medicationList.length > 0 && (
                    <>
                      <div
                        style={{
                          fontSize: '10pt',
                          fontWeight: 'bold',
                          color: '#111',
                          textTransform: 'uppercase',
                          borderBottom: '1.5px solid #222',
                          paddingBottom: '2px',
                          marginTop: '12px',
                          marginBottom: '4px',
                        }}
                      >
                        DISCHARGE MEDICATIONS
                      </div>
                      <table
                        className="w-100 mb-2"
                        style={{
                          borderCollapse: 'collapse',
                          tableLayout: 'fixed',
                          border: '1px solid #94a3b8',
                        }}
                      >
                        <thead>
                          <tr>
                            <th style={{ backgroundColor: '#000', color: '#fff', fontSize: '8pt', fontWeight: 'bold', padding: '4px 6px', textAlign: 'left', border: '1px solid #000', width: '44%' }}>Medicine</th>
                            <th style={{ backgroundColor: '#000', color: '#fff', fontSize: '8pt', fontWeight: 'bold', padding: '4px 6px', textAlign: 'left', border: '1px solid #000', width: '18%' }}>Frequency</th>
                            <th style={{ backgroundColor: '#000', color: '#fff', fontSize: '8pt', fontWeight: 'bold', padding: '4px 6px', textAlign: 'left', border: '1px solid #000', width: '18%' }}>Duration</th>
                            <th style={{ backgroundColor: '#000', color: '#fff', fontSize: '8pt', fontWeight: 'bold', padding: '4px 6px', textAlign: 'left', border: '1px solid #000', width: '20%' }}>Timing</th>
                          </tr>
                        </thead>
                        <tbody>
                          {medicationList.map((m, i) => (
                            <tr key={i}>
                              <td style={{ border: '1px solid #94a3b8', padding: '4px 6px', fontSize: '9pt', fontWeight: 'bold' }}>{m.medicine}</td>
                              <td style={{ border: '1px solid #94a3b8', padding: '4px 6px', fontSize: '9pt' }}>{m.frequency}</td>
                              <td style={{ border: '1px solid #94a3b8', padding: '4px 6px', fontSize: '9pt' }}>{m.duration}</td>
                              <td style={{ border: '1px solid #94a3b8', padding: '4px 6px', fontSize: '9pt' }}>{m.timing}</td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </>
                  )}

                  {/* Advice & Review */}
                  {adviceData.advice_items.length > 0 && (
                    <>
                      <div
                        style={{
                          fontSize: '10pt',
                          fontWeight: 'bold',
                          color: '#111',
                          textTransform: 'uppercase',
                          borderBottom: '1.5px solid #222',
                          paddingBottom: '2px',
                          marginTop: '12px',
                          marginBottom: '4px',
                        }}
                      >
                        ADVICE
                      </div>
                      <div style={{ fontSize: '8.8pt', lineHeight: 1.5, marginBottom: '8px' }}>
                        {adviceData.advice_items.map((item, i) => (
                          <span key={i} style={{ display: 'inline-block', marginRight: '18px' }}>
                            <strong>&bull;</strong> {item}
                          </span>
                        ))}
                      </div>
                    </>
                  )}

                  {(adviceData.review_text || adviceData.review_date) && (
                    <>
                      <div
                        style={{
                          fontSize: '10pt',
                          fontWeight: 'bold',
                          color: '#111',
                          textTransform: 'uppercase',
                          borderBottom: '1.5px solid #222',
                          paddingBottom: '2px',
                          marginTop: '12px',
                          marginBottom: '4px',
                        }}
                      >
                        REVIEW & SUTURE REMOVAL
                      </div>
                      <div
                        style={{
                          border: '1.5px solid #000',
                          backgroundColor: '#f1f5f9',
                          padding: '6px 12px',
                          marginBottom: '8px',
                          display: 'flex',
                          justifyContent: 'space-between',
                          alignItems: 'center',
                        }}
                      >
                        {adviceData.review_text && <div style={{ fontSize: '9.5pt', fontWeight: 'bold' }}>{adviceData.review_text}</div>}
                        {adviceData.review_date && <div style={{ fontSize: '11.5pt', fontWeight: 'bold' }}>{adviceData.review_date}</div>}
                      </div>
                    </>
                  )}

                  {adviceData.tamil_note && (
                    <div
                      style={{
                        border: '1px solid #000',
                        borderLeft: '4px solid #000',
                        backgroundColor: '#f8fafc',
                        padding: '6px 10px',
                        marginBottom: '8px',
                        fontFamily: "'Nirmala UI', 'Nirmala', 'Arial Unicode MS', sans-serif",
                        fontSize: '8.5pt',
                        lineHeight: 1.4,
                        color: '#111',
                      }}
                    >
                      <strong style={{ display: 'block', fontSize: '8pt', marginBottom: '2px' }}>
                        ஸ்டெண்ட் குறிப்பு (STENT REMOVAL NOTICE):
                      </strong>
                      {adviceData.tamil_note}
                    </div>
                  )}

                  {/* When to Contact Box */}
                  <div
                    style={{
                      backgroundColor: '#ebebeb',
                      borderLeft: '4px solid #000',
                      padding: '8px 12px',
                      marginTop: '10px',
                      marginBottom: '10px',
                    }}
                  >
                    <div style={{ marginBottom: '3px' }}>
                      <span style={{ fontSize: '7.5pt', fontWeight: 'bold', color: '#111', width: '130px', display: 'inline-block', verticalAlign: 'top' }}>WHEN TO CONTACT</span>
                      <span style={{ fontSize: '8.8pt', fontWeight: 'bold', color: '#000', display: 'inline-block' }}>Report immediately if there is bleeding, fever, abdominal pain, or other emergencies.</span>
                    </div>
                    <div>
                      <span style={{ fontSize: '7.5pt', fontWeight: 'bold', color: '#111', width: '130px', display: 'inline-block', verticalAlign: 'middle' }}>EMERGENCY</span>
                      <span style={{ fontSize: '10.5pt', fontWeight: 'bold', color: '#000', display: 'inline-block', verticalAlign: 'middle' }}>04342-262266 &nbsp;&nbsp;|&nbsp;&nbsp; +91-7418084266</span>
                    </div>
                  </div>

                  {/* Signatures */}
                  <div
                    style={{
                      display: 'flex',
                      justifyContent: 'flex-end',
                      borderTop: '1px solid #444',
                      paddingTop: '8px',
                      marginTop: '24px',
                    }}
                  >
                    <div style={{ textAlign: 'right' }}>
                      {(admission.consultant || admission.surgeon) && (
                        <div style={{ fontSize: '9pt', fontWeight: 'bold', color: '#000' }}>{admission.consultant || admission.surgeon}</div>
                      )}
                      {admission.approver?.doctor_reg_no && (
                        <div style={{ fontSize: '7.5pt', color: '#475569', marginTop: '1px' }}>Reg: {admission.approver.doctor_reg_no}</div>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            );
          }

          // SURGICAL / NON-OBSTETRIC DISCHARGE SUMMARY (Mr. Gopalakrishnan Hospital Standard)
          const { hist, inv, surgAdvice, surgDrugs } = surgicalFormatted || {
            hist: formatSurgicalPastHistory(admission),
            inv: formatSurgicalInvestigations(admission),
            surgAdvice: formatSurgicalAdvice(admission),
            surgDrugs: formatSurgicalDrugs(admission.medication_orders),
          };

          const formatDt = (dtStr?: string | null) => {
            if (!dtStr) return '—';
            const dt = new Date(dtStr);
            if (isNaN(dt.getTime())) return dtStr;
            const d = String(dt.getDate()).padStart(2, '0');
            const m = String(dt.getMonth() + 1).padStart(2, '0');
            const y = dt.getFullYear();
            const time = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            return `${d}.${m}.${y} at ${time}`;
          };

          return (
            <div className="document-sheet mb-4">
              <div
                className="text-end text-muted small mb-2 no-print"
                style={{ fontSize: '10.5px', letterSpacing: '0.05em', fontWeight: 600 }}
              >
                DOCUMENT 1 OF 3: DISCHARGE SUMMARY ({admission.format.toUpperCase()})
              </div>

              <div
                className="discharge-summary-document surgical-doc"
                style={{
                  fontFamily: "Arial, 'Helvetica Neue', Helvetica, sans-serif",
                  fontSize: '9pt',
                  lineHeight: 1.35,
                  color: '#000',
                }}
              >
                {/* Space for Pre-printed Letterhead (First page only) */}
                <div style={{ height: '38mm' }}></div>

                <div className="text-center mb-3">
                  <h3
                    className="fw-bold text-uppercase mb-0"
                    style={{ letterSpacing: '0.5px', fontSize: '15pt', color: '#111' }}
                  >
                    DISCHARGE SUMMARY
                  </h3>
                </div>

                {/* Top Demographics Grid */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <tbody>
                    <tr>
                      <td style={{ width: '12%', fontWeight: 'bold', background: '#f8fafc' }}>NAME</td>
                      <td style={{ width: '44%', fontWeight: 'bold', fontSize: '9.5pt' }}>{admission.patient_name?.toUpperCase()}</td>
                      <td style={{ width: '10%', fontWeight: 'bold', textAlign: 'center' }}>{admission.age}</td>
                      <td style={{ width: '6%', fontWeight: 'bold', textAlign: 'center' }}>{admission.sex ? admission.sex.charAt(0).toUpperCase() : '—'}</td>
                      <td style={{ width: '14%', fontWeight: 'bold', background: '#f8fafc' }}>CONSULTANT</td>
                      <td style={{ width: '14%', fontWeight: 'bold' }}>{(admission.consultant || admission.surgeon || '—').toUpperCase()}</td>
                    </tr>
                  </tbody>
                </table>

                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <tbody>
                    <tr>
                      <td style={{ width: '14%', fontWeight: 'bold', background: '#f8fafc', verticalAlign: 'top' }}>ADDRESS</td>
                      <td style={{ width: '36%', verticalAlign: 'top' }}>{admission.address || '—'}</td>
                      <td style={{ width: '18%', fontWeight: 'bold', background: '#f8fafc', verticalAlign: 'top' }}>FINAL DIAGNOSIS</td>
                      <td style={{ width: '32%', fontWeight: 'bold', verticalAlign: 'top' }}>{(admission.diagnosis || '—').toUpperCase()}</td>
                    </tr>
                    <tr>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>PHONE</td>
                      <td>{admission.contact || '—'}</td>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>PROCEDURE DONE</td>
                      <td style={{ fontWeight: 'bold' }}>{(admission.procedure_done || admission.procedure_name || '—').toUpperCase()}</td>
                    </tr>
                    <tr>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>MRD No</td>
                      <td>{resolveMrdNo(admission.mrd_no, admission.ip_no) || '—'}</td>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>ICD CODE</td>
                      <td>{admission.icd_code || '—'}</td>
                    </tr>
                    <tr>
                      <td colSpan={2} style={{ background: '#ffffff', border: 'none' }}></td>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>IP NO :</td>
                      <td style={{ fontWeight: 'bold' }}>{admission.ip_no}</td>
                    </tr>
                  </tbody>
                </table>

                <InsuranceCoverageStrip admission={admission} />

                {/* Dates Row */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <tbody>
                    <tr>
                      <td style={{ width: '10%', fontWeight: 'bold', background: '#f8fafc' }}>DOA</td>
                      <td style={{ width: '23%' }}>{formatDt(admission.admission_at)}</td>
                      <td style={{ width: '10%', fontWeight: 'bold', background: '#f8fafc' }}>DOO</td>
                      <td style={{ width: '23%' }}>{formatDt(admission.procedure_at)}</td>
                      <td style={{ width: '10%', fontWeight: 'bold', background: '#f8fafc' }}>DOD</td>
                      <td style={{ width: '24%' }}>{formatDt(admission.discharge_at)}</td>
                    </tr>
                  </tbody>
                </table>

                {/* PRESENTING COMPLAINTS */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <thead>
                    <tr className="bg-light">
                      <th style={{ background: '#f1f5f9', textAlign: 'left', fontSize: '8.5pt' }}>PRESENTING COMPLAINTS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style={{ padding: '6px 8px', fontSize: '8.8pt' }}>
                        {admission.clinical_history ? admission.clinical_history.split('\n')[0] : '—'}
                      </td>
                    </tr>
                  </tbody>
                </table>

                {/* PAST MEDICAL & SURGICAL HISTORY */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <thead>
                    <tr className="bg-light">
                      <th style={{ width: '60%', background: '#f1f5f9', textAlign: 'left', fontSize: '8.5pt' }}>PAST MEDICAL HISTORY</th>
                      <th style={{ width: '40%', background: '#f1f5f9', textAlign: 'left', fontSize: '8.5pt' }}>PAST SURGICAL HISTORY</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style={{ padding: '4px 6px' }}>
                        <table style={{ width: '100%', borderCollapse: 'collapse', border: 'none' }}>
                          <tbody>
                            <tr>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '7.5pt', fontWeight: 'bold', width: '20%' }}>DM</td>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '7.5pt', fontWeight: 'bold', width: '20%' }}>HT</td>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '7.5pt', fontWeight: 'bold', width: '20%' }}>ASTHMA</td>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '7.5pt', fontWeight: 'bold', width: '25%' }}>TUBERCULOSIS</td>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '7.5pt', fontWeight: 'bold', width: '15%' }}>Other</td>
                            </tr>
                            <tr>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '8.5pt' }}>{hist.dm}</td>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '8.5pt' }}>{hist.ht}</td>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '8.5pt' }}>{hist.asthma}</td>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '8.5pt' }}>{hist.tb}</td>
                              <td style={{ border: 'none', padding: '1px 2px', fontSize: '8.5pt' }}>{hist.other}</td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                      <td style={{ padding: '4px 6px', fontSize: '8.5pt', verticalAlign: 'top' }}>
                        {hist.past_surgical}
                      </td>
                    </tr>
                    <tr>
                      <td style={{ padding: '3px 6px' }}>
                        <span style={{ fontWeight: 'bold', fontSize: '7.5pt', marginRight: '6px' }}>ALLERGIES:</span> {hist.allergies}
                      </td>
                      <td style={{ padding: '3px 6px' }}>
                        <span style={{ fontWeight: 'bold', fontSize: '7.5pt', marginRight: '6px' }}>MEDICATIONS:</span> {hist.medications}
                      </td>
                    </tr>
                    <tr>
                      <td style={{ padding: '3px 6px' }}>
                        <span style={{ fontWeight: 'bold', fontSize: '7.5pt', marginRight: '6px' }}>PULSE:</span> {hist.pulse}
                      </td>
                      <td style={{ padding: '3px 6px' }}>
                        <span style={{ fontWeight: 'bold', fontSize: '7.5pt', marginRight: '6px' }}>BP:</span> {hist.bp}
                      </td>
                    </tr>
                  </tbody>
                </table>

                {/* CLINICAL EXAMINATION */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <thead>
                    <tr className="bg-light">
                      <th style={{ background: '#f1f5f9', textAlign: 'left', fontSize: '8.5pt' }}>CLINICAL EXAMINATION</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style={{ padding: '6px 8px', fontSize: '8.8pt' }}>
                        {admission.examination || '—'}
                      </td>
                    </tr>
                  </tbody>
                </table>

                {/* INVESTIGATIONS */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <thead>
                    <tr className="bg-light">
                      <th style={{ background: '#f1f5f9', textAlign: 'left', fontSize: '8.5pt' }} colSpan={2}>INVESTIGATIONS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style={{ width: '50%', padding: '4px 6px', verticalAlign: 'top' }}>
                        <table style={{ width: '100%', borderCollapse: 'collapse', border: 'none' }}>
                          <tbody>
                            <tr>
                              {Object.keys(inv.group1).map((k) => (
                                <td key={k} style={{ border: 'none', padding: '1px 2px', fontSize: '7.2pt', fontWeight: 'bold' }}>
                                  {k} {inv.units?.[k] ? <span style={{ fontWeight: 400, color: '#64748b', fontSize: '5.8pt' }}>({inv.units[k]})</span> : null}
                                </td>
                              ))}
                            </tr>
                            <tr>
                              {Object.values(inv.group1).map((v, i) => (
                                <td key={i} style={{ border: 'none', padding: '1px 2px', fontSize: '8.5pt' }}>{v}</td>
                              ))}
                            </tr>
                            <tr>
                              {Object.keys(inv.group1).map((k) => (
                                <td key={`ref-${k}`} style={{ border: 'none', padding: '0 2px', fontSize: '5.8pt', color: '#64748b' }}>
                                  {inv.ranges?.[k] ? `Ref: ${inv.ranges[k]}` : ''}
                                </td>
                              ))}
                            </tr>
                          </tbody>
                        </table>
                      </td>
                      <td style={{ width: '50%', padding: '4px 6px', verticalAlign: 'top' }}>
                        <table style={{ width: '100%', borderCollapse: 'collapse', border: 'none' }}>
                          <tbody>
                            <tr>
                              {Object.keys(inv.group2).map((k) => (
                                <td key={k} style={{ border: 'none', padding: '1px 2px', fontSize: '7.2pt', fontWeight: 'bold' }}>
                                  {k} {inv.units?.[k] ? <span style={{ fontWeight: 400, color: '#64748b', fontSize: '5.8pt' }}>({inv.units[k]})</span> : null}
                                </td>
                              ))}
                            </tr>
                            <tr>
                              {Object.values(inv.group2).map((v, i) => (
                                <td key={i} style={{ border: 'none', padding: '1px 2px', fontSize: '8.5pt' }}>{v}</td>
                              ))}
                            </tr>
                            <tr>
                              {Object.keys(inv.group2).map((k) => (
                                <td key={`ref-${k}`} style={{ border: 'none', padding: '0 2px', fontSize: '5.8pt', color: '#64748b' }}>
                                  {inv.ranges?.[k] ? `Ref: ${inv.ranges[k]}` : ''}
                                </td>
                              ))}
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    {Object.entries(inv.imaging).map(([k, v]) => (
                      <tr key={k}>
                        <td style={{ fontWeight: 'bold', background: '#f8fafc', fontSize: '8pt', width: '25%' }}>{k}</td>
                        <td style={{ fontWeight: 'bold', fontSize: '8.5pt' }}>{v}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>

                {/* THEATER NOTES */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <thead>
                    <tr className="bg-light">
                      <th style={{ background: '#f1f5f9', textAlign: 'left', fontSize: '8.5pt' }} colSpan={4}>THEATER NOTES</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style={{ width: '15%', fontWeight: 'bold', background: '#f8fafc' }}>SURGERY</td>
                      <td colSpan={3} style={{ fontWeight: 'bold' }}>{(admission.procedure_name || admission.procedure_done || '—').toUpperCase()}</td>
                    </tr>
                    <tr>
                      <td style={{ width: '15%', fontWeight: 'bold', background: '#f8fafc' }}>DATE</td>
                      <td style={{ width: '35%' }}>{formatDt(admission.procedure_at)}</td>
                      <td style={{ width: '15%', fontWeight: 'bold', background: '#f8fafc' }}>ANAESTHESIA</td>
                      <td style={{ width: '35%' }}>{admission.anaesthesia_type || '—'}</td>
                    </tr>
                    <tr>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>SURGEON</td>
                      <td>{(admission.surgeon || '—').toUpperCase()}</td>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>ANESTHETIST</td>
                      <td>{(admission.anaesthetist || '—').toUpperCase()}</td>
                    </tr>
                    <tr>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>ASSISTED BY</td>
                      <td>{(admission.assisted_by || admission.assistant || '—').toUpperCase()}</td>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>ICD CODE</td>
                      <td>{admission.icd_code || '—'}</td>
                    </tr>
                    <tr>
                      <td colSpan={4} style={{ padding: '6px 8px', fontSize: '8.5pt', lineHeight: 1.4, textAlign: 'justify', whiteSpace: 'pre-line' }}>
                        {admission.procedure_note || '—'}
                      </td>
                    </tr>
                  </tbody>
                </table>

                {/* COURSE IN THE HOSPITAL */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <thead>
                    <tr className="bg-light">
                      <th style={{ background: '#f1f5f9', textAlign: 'left', fontSize: '8.5pt' }}>COURSE IN THE HOSPITAL</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style={{ padding: '6px 8px', fontSize: '8.8pt', lineHeight: 1.4, textAlign: 'justify', whiteSpace: 'pre-line' }}>
                        {admission.treatment_course || '—'}
                      </td>
                    </tr>
                  </tbody>
                </table>

                {/* CONDITION AT DISCHARGE */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <tbody>
                    <tr>
                      <td style={{ width: '25%', fontWeight: 'bold', background: '#f1f5f9', fontSize: '8.5pt' }}>CONDITION AT DISCHARGE</td>
                      <td style={{ width: '75%', fontWeight: 'bold', fontSize: '9pt' }}>{(admission.discharge_condition ? admission.discharge_condition.split('\n')[0] : '—').toUpperCase()}</td>
                    </tr>
                  </tbody>
                </table>

                {/* DISCHARGE ADVICE */}
                <table className="table table-bordered mb-2 border-dark" style={{ fontSize: '8.5pt' }}>
                  <thead>
                    <tr className="bg-light">
                      <th style={{ background: '#f1f5f9', textAlign: 'left', fontSize: '8.5pt' }} colSpan={4}>DISCHARGE ADVICE</th>
                    </tr>
                  </thead>
                  <tbody>
                    {surgDrugs.length > 0 && (
                      <>
                        <tr>
                          <td rowSpan={surgDrugs.length + 1} style={{ width: '12%', fontWeight: 'bold', background: '#f8fafc', verticalAlign: 'top', textAlign: 'center' }}>DRUGS</td>
                          <td style={{ width: '48%', fontWeight: 'bold', fontSize: '8pt', background: '#f8fafc' }}>DRUG</td>
                          <td style={{ width: '25%', fontWeight: 'bold', fontSize: '8pt', background: '#f8fafc' }}>DOSAGE</td>
                          <td style={{ width: '15%', fontWeight: 'bold', fontSize: '8pt', background: '#f8fafc', textAlign: 'center' }}>NO</td>
                        </tr>
                        {surgDrugs.map((d, i) => (
                          <tr key={i}>
                            <td style={{ fontSize: '8.8pt', fontWeight: 500 }}>{d.drug}</td>
                            <td style={{ fontSize: '8.8pt' }}>{d.dosage}</td>
                            <td style={{ fontSize: '8.8pt', textAlign: 'center' }}>{d.no}</td>
                          </tr>
                        ))}
                      </>
                    )}
                    <tr>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>IMPLANT</td>
                      <td colSpan={3} style={{ fontWeight: 'bold', fontSize: '8.8pt' }}>{surgAdvice.implant}</td>
                    </tr>
                    <tr>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>DIET</td>
                      <td colSpan={3} style={{ fontSize: '8.8pt' }}>{surgAdvice.diet}</td>
                    </tr>
                    <tr>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>EXERCISE</td>
                      <td colSpan={3} style={{ fontSize: '8.8pt' }}>{surgAdvice.exercise}</td>
                    </tr>
                    <tr>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>EMERGENCY CONTACT</td>
                      <td colSpan={3} style={{ fontWeight: 'bold', fontSize: '8.8pt' }}>{surgAdvice.emergency_contact}</td>
                    </tr>
                    <tr>
                      <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>WHEN TO CONTACT</td>
                      <td colSpan={3} style={{ fontSize: '8.8pt', color: '#b91c1c', fontWeight: 600 }}>{surgAdvice.when_to_contact}</td>
                    </tr>
                    {surgAdvice.review && (
                      <tr>
                        <td style={{ fontWeight: 'bold', background: '#f8fafc' }}>REVIEW</td>
                        <td colSpan={3} style={{ fontWeight: 'bold', fontSize: '8.8pt' }}>{surgAdvice.review}</td>
                      </tr>
                    )}
                    {surgAdvice.tamil_note && (
                      <tr>
                        <td style={{ background: '#f8fafc' }}></td>
                        <td colSpan={3} style={{ fontSize: '8.5pt', fontFamily: "'Nirmala UI', 'Nirmala', 'Arial Unicode MS', sans-serif", lineHeight: 1.4, color: '#1e293b' }}>
                          {surgAdvice.tamil_note}
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>

                {/* Signatures */}
                <table className="table table-borderless mt-3" style={{ fontSize: '8.5pt' }}>
                  <tbody>
                    <tr>
                      <td style={{ width: '50%', padding: '10px 8px 0 8px' }}>
                        <div style={{ fontSize: '8pt', fontWeight: 'bold', color: '#475569' }}>PREPARED BY</div>
                        <div style={{ fontSize: '9.5pt', fontWeight: 'bold', color: '#000', marginTop: '2px' }}>
                          {(admission.handover_name || admission.consultant || admission.surgeon || '—').toUpperCase()}
                        </div>
                      </td>
                      <td style={{ width: '50%', padding: '10px 8px 0 8px', textAlign: 'right' }}>
                        <div style={{ fontSize: '8pt', fontWeight: 'bold', color: '#475569' }}>COUNTERSIGNED BY</div>
                        <div style={{ fontSize: '9.5pt', fontWeight: 'bold', color: '#000', marginTop: '2px' }}>
                          {(admission.consultant || admission.surgeon || '—').toUpperCase()}
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          );
          })()}

      {/* DOCUMENT 2: OPERATION THEATRE NOTES */}
      {(activeDoc === 'all' || activeDoc === 'operation_notes') && (
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
          {/* Space for Pre-printed Letterhead (First page only) */}
          <div style={{ height: '40mm' }}></div>

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
                <td style={{ padding: '2px 0' }}>
                  {admission.uhid}
                  {admission.picme_no ? ` • PICME: ${admission.picme_no}` : ''}
                </td>
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
                <td style={{ padding: '2px 0' }}>{admission.assisted_surgeon || admission.assisted_by || admission.staff_nurse || admission.assistant || ''}</td>
                <td style={{ fontWeight: 'bold', padding: '2px 0' }}>ANESTHESIA TYPE</td>
                <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                <td style={{ padding: '2px 0' }}>{admission.anaesthesia_type || 'Spinal'}</td>
              </tr>
              {(admission.ot_technician || admission.assisted_anaesthetist || (admission.assisted_surgeon && admission.staff_nurse)) && (
                <tr>
                  <td style={{ fontWeight: 'bold', padding: '2px 0' }}>
                    {admission.assisted_surgeon && admission.staff_nurse ? 'STAFF NURSE' : (admission.ot_technician ? 'OT TECHNICIAN' : 'ASST. ANAESTHETIST')}
                  </td>
                  <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                  <td style={{ padding: '2px 0' }}>
                    {admission.assisted_surgeon && admission.staff_nurse ? admission.staff_nurse : (admission.ot_technician || admission.assisted_anaesthetist)}
                  </td>
                  {admission.assisted_anaesthetist && (admission.ot_technician || (admission.assisted_surgeon && admission.staff_nurse)) ? (
                    <>
                      <td style={{ fontWeight: 'bold', padding: '2px 0' }}>ASST. ANESTHETIST</td>
                      <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                      <td style={{ padding: '2px 0' }}>{admission.assisted_anaesthetist}</td>
                    </>
                  ) : (
                    <><td></td><td></td><td></td></>
                  )}
                </tr>
              )}
            </tbody>
          </table>

          {/* Procedure Note & Incision */}
          <div className="mt-3">
            <div style={{ fontWeight: 'bold', fontSize: '10.5pt', marginTop: '6px', marginBottom: '2px' }}>
              PROCEDURE NOTE :
            </div>
            {admission.procedure_note && (
              <div style={{ fontSize: '10.5pt', textAlign: 'justify', marginBottom: '6px', lineHeight: 1.45, whiteSpace: 'pre-line' }}>
                {admission.procedure_note}
              </div>
            )}
            <div style={{ fontWeight: 'bold', fontSize: '10.5pt', marginTop: '6px', marginBottom: '2px' }}>
              INCISION : {admission.incision && <span style={{ fontWeight: 'normal' }}>{admission.incision}</span>}
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
      )}

      {/* DOCUMENT 3: MRD FORM */}
      {(activeDoc === 'all' || activeDoc === 'mrd_form') && (
      <div className="document-sheet mb-4 page-break">
        <div
          className="text-end text-muted small mb-2 no-print"
          style={{ fontSize: '10.5px', letterSpacing: '0.05em', fontWeight: 600 }}
        >
          DOCUMENT 3 OF 3: MEDICAL RECORDS DEPARTMENT (MRD) FORM
        </div>

        <div className="p-4 bg-white" style={{ fontFamily: "Arial, 'Helvetica Neue', Helvetica, sans-serif" }}>
          {/* Header with Logo */}
          <div className="text-center mb-4">
            <div className="mb-2">
              <img
                src="/images/guru-hospital-logo.png"
                alt="Guru Hospital Logo"
                style={{ width: '76px', height: 'auto', display: 'inline-block' }}
              />
            </div>

            <h2
              className="fw-bold mb-1 text-dark text-uppercase"
              style={{ fontSize: '21pt', letterSpacing: '1px', fontWeight: 800 }}
            >
              GURU HOSPITAL
            </h2>
            <div style={{ fontSize: '10pt', color: '#222', lineHeight: '1.4' }}>
              32, A/2, Antony Street, Nethaji Bypass Road,
            </div>
            <div style={{ fontSize: '10pt', color: '#222', lineHeight: '1.4' }}>
              Govt. Medical College Hospital Opp. Dharmapuri - 636 701.
            </div>
            <div style={{ fontSize: '10pt', fontWeight: 600, color: '#111', marginTop: '2px' }}>
              Ph : 04342-262266, Cell : 7418084266
            </div>
          </div>

          {/* Title */}
          <div className="text-center my-4">
            <span
              style={{
                fontFamily: "'Times New Roman', Times, Georgia, serif",
                fontSize: '16.5pt',
                fontWeight: 'bold',
                textTransform: 'uppercase',
                letterSpacing: '0.5px',
                color: '#111',
                display: 'inline-block',
                borderBottom: '3px double #111',
                paddingBottom: '3px',
              }}
            >
              MEDICAL RECORDS DEPARTMENT
            </span>
          </div>

          {/* 9 Clean Form Fields covering the page */}
          <table className="table table-borderless my-4" style={{ fontSize: '14pt' }}>
            <tbody>
              <tr>
                <td style={{ width: '270px', fontWeight: 'bold', padding: '20px 4px' }}>Patient Name</td>
                <td style={{ width: '35px', fontWeight: 'bold', textAlign: 'center', padding: '20px 4px' }}>:</td>
                <td style={{ padding: '20px 4px' }}><strong>{admission.patient_name}</strong></td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '20px 4px' }}>Discharge Date &amp; Time</td>
                <td style={{ fontWeight: 'bold', textAlign: 'center', padding: '20px 4px' }}>:</td>
                <td style={{ padding: '20px 4px' }}>
                  {admission.discharge_at
                    ? `${formatDate(admission.discharge_at)} @ ${new Date(admission.discharge_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`
                    : '29.08.2026 @ 04.00 PM'}
                </td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '20px 4px' }}>MRD No.</td>
                <td style={{ fontWeight: 'bold', textAlign: 'center', padding: '20px 4px' }}>:</td>
                <td style={{ padding: '20px 4px' }}>
                  {resolveMrdNo(admission.mrd_no, admission.ip_no)}
                </td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '20px 4px' }}>IP No.</td>
                <td style={{ fontWeight: 'bold', textAlign: 'center', padding: '20px 4px' }}>:</td>
                <td style={{ padding: '20px 4px' }}>
                  <strong>{admission.ip_no}</strong>
                </td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '20px 4px' }}>No of pages</td>
                <td style={{ fontWeight: 'bold', textAlign: 'center', padding: '20px 4px' }}>:</td>
                <td style={{ padding: '20px 4px' }}></td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '20px 4px' }}>Payment Mode</td>
                <td style={{ fontWeight: 'bold', textAlign: 'center', padding: '20px 4px' }}>:</td>
                <td style={{ padding: '20px 4px' }}>{formatPaymentSummary(admission)}</td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '20px 4px' }}>Hand Over Sign</td>
                <td style={{ fontWeight: 'bold', textAlign: 'center', padding: '20px 4px' }}>:</td>
                <td style={{ padding: '20px 4px' }}></td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '20px 4px' }}>Floor Incharge Sign</td>
                <td style={{ fontWeight: 'bold', textAlign: 'center', padding: '20px 4px' }}>:</td>
                <td style={{ padding: '20px 4px' }}></td>
              </tr>
              <tr>
                <td style={{ fontWeight: 'bold', padding: '20px 4px' }}>MRD Incharge Sign</td>
                <td style={{ fontWeight: 'bold', textAlign: 'center', padding: '20px 4px' }}>:</td>
                <td style={{ padding: '20px 4px' }}></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      )}
      </>
      )}

      {returnModalOpen && (
        <ReasonModal
          title="Return Pack for Clinical Correction"
          description="Please explain the required corrections or missing details. The record will return to draft status for editing."
          label="Correction Reason / Discrepancy Note"
          placeholder="e.g. Newborn weight mismatch; clarify anesthesia type; update medication orders."
          rows={4}
          submitLabel="Confirm & Return"
          submittingLabel="Returning..."
          submitButtonClass="btn btn-danger"
          onClose={() => setReturnModalOpen(false)}
          onSubmit={(reason, done) => {
            router.post(
              `/documents/${admission.id}/return`,
              { correction_reason: reason },
              {
                onFinish: () => {
                  done();
                  setReturnModalOpen(false);
                },
              }
            );
          }}
        />
      )}

      {revisionModalOpen && (
        <ReasonModal
          title="Create New Clinical Revision"
          description="This admission is currently approved/generated. Creating a new revision will preserve an immutable historical snapshot and reset the admission to draft for correction."
          label="Reason for Revision"
          placeholder="e.g. Retrospective pathology findings added; dosage adjustment."
          rows={3}
          submitLabel="Create Revision & Unlock"
          submittingLabel="Creating..."
          submitButtonClass="btn btn-primary"
          onClose={() => setRevisionModalOpen(false)}
          onSubmit={(reason, done) => {
            router.post(
              `/admissions/${admission.id}/revision`,
              { revision_reason: reason },
              {
                onFinish: () => {
                  done();
                  setRevisionModalOpen(false);
                },
              }
            );
          }}
        />
      )}
    </AppLayout>
  );
}

interface ReasonModalProps {
  title: string;
  description: string;
  label: string;
  placeholder: string;
  rows: number;
  submitLabel: string;
  submittingLabel: string;
  submitButtonClass: string;
  onClose: () => void;
  onSubmit: (reason: string, done: () => void) => void;
}

function ReasonModal({
  title,
  description,
  label,
  placeholder,
  rows,
  submitLabel,
  submittingLabel,
  submitButtonClass,
  onClose,
  onSubmit,
}: ReasonModalProps) {
  const [reason, setReason] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!reason.trim()) return;
    setSubmitting(true);
    onSubmit(reason, () => setSubmitting(false));
  };

  return (
    <div
      className="modal fade show d-block"
      tabIndex={-1}
      style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
    >
      <div className="modal-dialog modal-dialog-centered">
        <form onSubmit={handleSubmit} className="modal-content border-0 shadow-lg">
          <div className="modal-header">
            <h5 className="modal-title fw-bold" style={{ letterSpacing: '-0.01em' }}>
              {title}
            </h5>
            <button type="button" className="btn-close" onClick={onClose}></button>
          </div>
          <div className="modal-body p-4">
            <p className="small text-muted mb-3">{description}</p>
            <div className="mb-3">
              <label className="form-label required fw-semibold">{label}</label>
              <textarea
                className="form-control"
                rows={rows}
                value={reason}
                onChange={(e) => setReason(e.target.value)}
                placeholder={placeholder}
                required
                autoFocus
              ></textarea>
            </div>
          </div>
          <div className="modal-footer">
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>
              Cancel
            </button>
            <button
              type="submit"
              className={submitButtonClass}
              disabled={submitting || !reason.trim()}
            >
              {submitting ? submittingLabel : submitLabel}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

