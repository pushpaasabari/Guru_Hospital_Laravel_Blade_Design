import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

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
  pdf_storage_path?: string;
}

interface Admission {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  approvals: Approval[];
  revisions: Revision[];
}

interface Props {
  admission: Admission;
}

export default function History({ admission }: Props) {
  const formatDateTime = (dateStr?: string) => {
    if (!dateStr) return '—';
    try {
      const d = new Date(dateStr);
      return (
        d.toLocaleDateString('en-GB', {
          day: '2-digit',
          month: 'short',
          year: 'numeric',
        }) +
        ', ' +
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

  return (
    <AppLayout>
      <Head title={`Document History & Revisions — ${admission.patient_name}`} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Audit & Revision Trail
          </h1>
          <p className="text-muted mb-0">
            Complete lifecycle history for <strong>{admission.patient_name}</strong> (UHID:{' '}
            {admission.uhid}, IP: {admission.ip_no})
          </p>
        </div>
        <Link href={`/documents/${admission.id}`} className="btn btn-outline-secondary">
          <i className="bi bi-arrow-left me-1"></i> Back to Document Pack
        </Link>
      </div>

      <div className="row g-4">
        {/* Left Column: Approval Lifecycle Events */}
        <div className="col-md-6">
          <div className="card shadow-sm border-0 h-100" style={{ borderRadius: '20px' }}>
            <div className="card-header bg-white py-3 border-0">
              <h5 className="card-title mb-0 fw-bold" style={{ letterSpacing: '-0.01em' }}>
                <i className="bi bi-clock-history text-primary me-2"></i> Document Lifecycle Transitions
              </h5>
            </div>
            <div className="card-body">
              {!admission.approvals || admission.approvals.length === 0 ? (
                <p className="text-muted text-center py-5">No approval transitions recorded yet.</p>
              ) : (
                <div className="timeline">
                  {admission.approvals.map((approval) => (
                    <div
                      key={approval.id}
                      className="border-start border-2 ps-3 pb-3 mb-2"
                      style={{ borderColor: 'var(--fg-border-strong, #e2e8f0)' }}
                    >
                      <div className="d-flex justify-content-between align-items-center">
                        <span className="badge bg-secondary text-uppercase" style={{ fontSize: '0.7rem' }}>
                          {approval.action.replace(/_/g, ' ')}
                        </span>
                        <span className="text-muted small">{formatDateTime(approval.created_at)}</span>
                      </div>
                      <div className="mt-1 small">
                        <strong>{approval.performer?.name || 'Staff User'}</strong>
                        <span
                          className="badge bg-light text-dark border text-uppercase ms-2"
                          style={{ fontSize: '0.65rem' }}
                        >
                          {approval.user_role}
                        </span>
                      </div>
                      {approval.notes_or_correction_reason && (
                        <div className="alert alert-light border small mt-2 py-1 px-2 mb-0">
                          <strong>Note:</strong> {approval.notes_or_correction_reason}
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Right Column: Historical Revisions */}
        <div className="col-md-6">
          <div className="card shadow-sm border-0 h-100" style={{ borderRadius: '20px' }}>
            <div className="card-header bg-white py-3 border-0">
              <h5 className="card-title mb-0 fw-bold" style={{ letterSpacing: '-0.01em' }}>
                <i className="bi bi-file-earmark-lock text-success me-2"></i> Immutable Approved Revisions
              </h5>
            </div>
            <div className="card-body">
              {!admission.revisions || admission.revisions.length === 0 ? (
                <div className="text-muted text-center py-5">
                  <i className="bi bi-shield-check fs-2 d-block mb-2 text-muted"></i>
                  No archived revisions exist yet. Revisions are created when approved documents are modified
                  or officially issued.
                </div>
              ) : (
                admission.revisions.map((rev) => (
                  <div key={rev.id} className="card border mb-3" style={{ borderRadius: '12px' }}>
                    <div className="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                      <span className="fw-bold">Revision #{rev.revision_number}</span>
                      <span className="text-muted small">{formatDateTime(rev.created_at)}</span>
                    </div>
                    <div className="card-body p-3 small">
                      <div>
                        <strong>Approved By:</strong> {rev.approver?.name || 'Doctor'}
                      </div>
                      <div>
                        <strong>Approved At:</strong> {formatDateTime(rev.approved_at)}
                      </div>
                      {rev.pdf_storage_path && (
                        <div className="text-muted mt-1">
                          <i className="bi bi-file-earmark-check text-success me-1"></i> PDF Snapshot Archived in
                          Private Storage
                        </div>
                      )}
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

