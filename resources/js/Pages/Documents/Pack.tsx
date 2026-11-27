import { useState, useEffect } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import ActionDropdown, {
  buildEditStageMenuItems,
  buildPdfDownloadMenuItems,
} from '../../Components/ActionDropdown';
import DischargeIntimationModal from '../../Components/DischargeIntimationModal';
import DigitalDischargeSummaryPreview, {
  DigitalSummaryPayload,
} from '../../Components/DigitalDischargeSummaryPreview';
import { resolveMrdNo } from '../../utils/inputNormalizers';
import { formatPaymentSummary } from '../../utils/dischargeFormatter';
import { isNaturalDeliveryCase } from '../../utils/deliveryMode';
import {
  formatBabyBirthTime,
  formatDate,
  formatDateTime,
  formatMrdDateTime,
  formatOtSurgeryDateTime,
} from '../../utils/dateFormatter';

interface Newborn {
  id?: number;
  baby_identifier?: string;
  sex?: string;
  birth_weight_kg?: number;
  discharge_weight_kg?: number;
  birth_at?: string;
  apgar_1_min?: string;
  apgar_5_min?: string;
  gestational_age_weeks?: number;
  neonatal_care?: string;
  notes?: string;
  birth_vaccines?: string[];
  vaccine_date?: string;
  vaccine_dates?: Record<string, string>;
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
  attender_name?: string;
  attender_relation?: string;
  uhid: string;
  ip_no: string;
  picme_no?: string;
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
  ward_room_no?: string;
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
  treatment_course?: string;
  treatment_given?: string;
  investigations?: string;
  vital_bp?: string;
  vital_pulse?: string;
  vital_spo2?: string;
  discharge_condition?: string;
  condition_at_discharge?: string;
  discharge_bp?: string;
  discharge_pulse?: string;
  discharge_spo2?: string;
  medication_orders?: string;
  discharge_advice?: string;
  post_surgery_diet?: string;
  post_surgery_activity?: string;
  follow_up?: string;
  suture_removal_date?: string;
  stent_removal_date?: string;
  handover_name?: string;
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
  digitalSummary?: DigitalSummaryPayload;
  initialDoc?: string;
  initialMode?: 'sheet' | 'pdf';
}

export default function Pack({
  admission,
  digitalSummary,
  initialDoc = 'all',
  initialMode = 'sheet',
}: Props) {
  const { auth } = usePage<{ auth: { user: any } }>().props;
  const user = auth.user;

  const [activeDoc, setActiveDoc] = useState<string>(initialDoc);
  const [viewMode, setViewMode] = useState<'sheet' | 'pdf'>(initialMode);
  const [returnModalOpen, setReturnModalOpen] = useState(false);
  const [revisionModalOpen, setRevisionModalOpen] = useState(false);
  const [intimationModalOpen, setIntimationModalOpen] = useState(false);

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

  const isNatDel = isNaturalDeliveryCase(admission);

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
                items={buildPdfDownloadMenuItems(
                  admission.id,
                  'Separate PDF Documents',
                  () => setIntimationModalOpen(true)
                )}
                primaryAction={
                  <a
                    href={`/admissions/${admission.id}/documents/pdf/discharge-summary`}
                    target="_blank"
                    rel="noreferrer"
                    className="btn btn-primary fw-semibold d-inline-flex align-items-center gap-1"
                    title="Generate and download Digital Discharge Summary PDF"
                  >
                    <i className="bi bi-file-earmark-medical me-1"></i> 1. Discharge Summary PDF
                  </a>
                }
              />

              <button
                type="button"
                className="btn btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm"
                onClick={() => setIntimationModalOpen(true)}
                title="Copy formatted discharge message"
              >
                <i className="bi bi-clipboard-check"></i> Copy Message
              </button>

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
            <div className="d-flex flex-wrap gap-2">
              <button
                type="button"
                onClick={() => setIntimationModalOpen(true)}
                className="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                title="Copy formatted discharge message"
              >
                <i className="bi bi-clipboard-check me-1"></i> Copy Discharge Message
              </button>
              <a
                href={`/admissions/${admission.id}/documents/pdf/discharge-summary`}
                className="btn btn-sm btn-outline-success fw-semibold"
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
          {/* DOCUMENT 1: DIGITAL DISCHARGE SUMMARY */}
          {(activeDoc === 'all' || activeDoc === 'discharge_summary') && (
            <DigitalDischargeSummaryPreview
              admission={admission}
              digitalSummary={digitalSummary}
              formatDateTime={formatDateTime}
            />
          )}

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
                  {isNatDel ? 'LABOUR / DELIVERY NOTES' : 'OPERATION THEATRE NOTES'}
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
                    <td style={{ fontWeight: 'bold', padding: '2px 0' }}>{isNatDel ? 'DELIVERY DATE' : 'DATE OF SURGERY'}</td>
                    <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                    <td style={{ padding: '2px 0' }}>{formatOtSurgeryDateTime(admission.procedure_at)}</td>
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
                    <td style={{ fontWeight: 'bold', padding: '2px 0' }}>{isNatDel ? 'MODE OF DELIVERY' : 'PROCEDURE DONE'}</td>
                    <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                    <td style={{ padding: '2px 0' }}>{admission.procedure_done || admission.procedure_name || ''}</td>
                  </tr>
                </tbody>
              </table>

              {/* Surgery & Team Block */}
              <table className="ot-table mt-2" style={{ width: '100%', borderCollapse: 'collapse', marginBottom: '4px' }}>
                <tbody>
                  <tr>
                    <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>{isNatDel ? 'DELIVERY' : 'SURGERY'}</td>
                    <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                    <td colSpan={4} style={{ padding: '2px 0' }}>
                      <strong>{admission.procedure_name || (isNatDel ? 'NORMAL VAGINAL DELIVERY' : 'ELECTIVE LSCS')}</strong>
                    </td>
                  </tr>
                  <tr>
                    <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>{isNatDel ? 'DELIVERED BY' : 'SURGEON'}</td>
                    <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                    <td style={{ width: '35%', padding: '2px 0' }}>{admission.surgeon || admission.consultant || ''}</td>
                    <td style={{ width: '15%', fontWeight: 'bold', padding: '2px 0' }}>ANESTHETIST</td>
                    <td style={{ width: '15px', textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                    <td style={{ width: '33%', padding: '2px 0' }}>{admission.anaesthetist || ''}</td>
                  </tr>
                  <tr>
                    <td style={{ fontWeight: 'bold', padding: '2px 0' }}>{isNatDel ? 'ASSISTED BY / S/N' : 'ASSISTANT BY S/N'}</td>
                    <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                    <td style={{ padding: '2px 0' }}>{admission.assisted_surgeon || admission.assisted_by || admission.staff_nurse || admission.assistant || ''}</td>
                    <td style={{ fontWeight: 'bold', padding: '2px 0' }}>{isNatDel ? 'ANALGESIA / LA' : 'ANESTHESIA TYPE'}</td>
                    <td style={{ textAlign: 'center', fontWeight: 'bold', padding: '2px 6px' }}>:</td>
                    <td style={{ padding: '2px 0' }}>{admission.anaesthesia_type || (isNatDel ? 'Local Infiltration / None' : 'Spinal')}</td>
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
                  {isNatDel ? 'DELIVERY NOTE :' : 'PROCEDURE NOTE :'}
                </div>
                {admission.procedure_note && (
                  <div style={{ fontSize: '10.5pt', textAlign: 'justify', marginBottom: '6px', lineHeight: 1.45, whiteSpace: 'pre-line' }}>
                    {admission.procedure_note}
                  </div>
                )}
                <div style={{ fontWeight: 'bold', fontSize: '10.5pt', marginTop: '6px', marginBottom: '2px' }}>
                  {isNatDel ? 'EPISIOTOMY / TEAR :' : 'INCISION :'} {admission.incision && <span style={{ fontWeight: 'normal' }}>{admission.incision}</span>}
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
                              Alive {baby.sex || 'newborn'} baby on{' '}
                              {formatBabyBirthTime(baby.birth_at || admission.procedure_at)}
                              <br />
                              Baby Weight{' '}
                              {baby.birth_weight_kg
                                ? `${Number(baby.birth_weight_kg).toFixed(3)}Kg`
                                : '—'}
                              {baby.neonatal_care && (
                                <span className="text-muted small"> ({baby.neonatal_care})</span>
                              )}
                              {baby.notes && (
                                <div className="small text-secondary">
                                  Presentation / Notes: {baby.notes}
                                </div>
                              )}
                            </div>
                          ))
                        ) : (
                          <div className="text-muted">Newborn details not recorded.</div>
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
                    {isNatDel ? 'POST-DELIVERY ORDERS :' : 'POST-OPERATIVE ORDERS :'}
                  </div>
                  <div style={{ fontSize: '10.5pt', textAlign: 'justify', marginBottom: '6px', lineHeight: 1.45, whiteSpace: 'pre-line' }}>
                    {admission.postoperative_orders}
                  </div>
                </div>
              )}

              {/* Surgeon Seal & Signature */}
              <div className="text-end mt-5 pt-4" style={{ pageBreakInside: 'avoid' }}>
                <span className="fw-bold" style={{ fontSize: '11pt', letterSpacing: '0.5px' }}>
                  {isNatDel ? 'OBSTETRICIAN / DOCTOR SEAL & SIGNATURE' : 'SURGEON SEAL & SIGNATURE'}
                </span>
                {(admission.surgeon || admission.consultant) && (
                  <div style={{ fontSize: '9pt', color: '#444', marginTop: '4px' }}>
                    {admission.surgeon || admission.consultant}
                  </div>
                )}
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
                      {formatMrdDateTime(admission.discharge_at)}
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

      {/* Discharge Intimation Copy Modal */}
      <DischargeIntimationModal
        show={intimationModalOpen}
        onClose={() => setIntimationModalOpen(false)}
        admission={admission}
      />
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
