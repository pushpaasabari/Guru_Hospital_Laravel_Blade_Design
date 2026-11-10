import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';

interface AuditLog {
  id: number;
  user_name: string;
  user_role: string;
  action: string;
  description: string;
  auditable_type?: string;
  auditable_id?: number;
  ip_address?: string;
  created_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: PaginationLink[];
}

interface Props {
  auditLogs: PaginatedData<AuditLog>;
}

export default function Index({ auditLogs }: Props) {
  const urlParams = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');
  const [action, setAction] = useState(urlParams.get('action') || '');
  const [userName, setUserName] = useState(urlParams.get('user_name') || '');
  const [dateFrom, setDateFrom] = useState(urlParams.get('date_from') || '');
  const [dateTo, setDateTo] = useState(urlParams.get('date_to') || '');

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      '/audit-logs',
      {
        action: action || undefined,
        user_name: userName || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
      },
      { preserveState: true }
    );
  };

  const handleReset = () => {
    setAction('');
    setUserName('');
    setDateFrom('');
    setDateTo('');
    router.get('/audit-logs');
  };

  const formatDateTime = (dateStr: string) => {
    try {
      const d = new Date(dateStr);
      return (
        d.toLocaleDateString('en-GB', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
        }) +
        ' ' +
        d.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          hour12: false,
        })
      );
    } catch {
      return dateStr;
    }
  };

  return (
    <AppLayout>
      <Head title="System Audit Trail — Guru Hospital" />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Tamper-Resistant System Audit Trail
          </h1>
          <p className="text-muted mb-0">
            Immutable tracking of user authentication, patient creation, clinical edits, approvals, and
            PDF exports.
          </p>
        </div>
        <span className="badge bg-secondary py-2 px-3">
          <i className="bi bi-shield-lock-fill me-1"></i> NABH / HIPAA Compliant Audit Log
        </span>
      </div>

      {/* Filter Card */}
      <div className="card mb-4 border-0 shadow-sm" style={{ borderRadius: '16px' }}>
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-center">
            <div className="col-md-3">
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="Action (e.g. admission.created)"
                value={action}
                onChange={(e) => setAction(e.target.value)}
              />
            </div>
            <div className="col-md-3">
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="Staff Name"
                value={userName}
                onChange={(e) => setUserName(e.target.value)}
              />
            </div>
            <div className="col-md-2">
              <input
                type="date"
                className="form-control form-control-sm"
                value={dateFrom}
                onChange={(e) => setDateFrom(e.target.value)}
              />
            </div>
            <div className="col-md-2">
              <input
                type="date"
                className="form-control form-control-sm"
                value={dateTo}
                onChange={(e) => setDateTo(e.target.value)}
              />
            </div>
            <div className="col-md-2 d-flex gap-1">
              <button type="submit" className="btn btn-sm btn-primary w-100">
                Filter
              </button>
              <button
                type="button"
                onClick={handleReset}
                className="btn btn-sm btn-outline-secondary"
              >
                Reset
              </button>
            </div>
          </form>
        </div>
      </div>

      {/* Logs Table */}
      <div className="card shadow-sm border-0" style={{ borderRadius: '20px' }}>
        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0" style={{ fontSize: '0.9rem' }}>
            <thead className="table-light text-muted small text-uppercase">
              <tr>
                <th>Timestamp</th>
                <th>Staff User</th>
                <th>Action</th>
                <th>Description</th>
                <th>Entity</th>
                <th>IP Address</th>
              </tr>
            </thead>
            <tbody>
              {!auditLogs.data || auditLogs.data.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center py-5 text-muted">
                    <i className="bi bi-shield-x fs-2 d-block mb-2 text-muted"></i>
                    No audit events found matching the selected parameters.
                  </td>
                </tr>
              ) : (
                auditLogs.data.map((log) => (
                  <tr key={log.id}>
                    <td className="text-nowrap small text-muted">{formatDateTime(log.created_at)}</td>
                    <td>
                      <div className="fw-semibold text-dark">{log.user_name}</div>
                      <span
                        className="badge bg-light text-dark border text-uppercase"
                        style={{ fontSize: '0.65rem' }}
                      >
                        {log.user_role}
                      </span>
                    </td>
                    <td>
                      <span className="badge bg-secondary font-monospace">{log.action}</span>
                    </td>
                    <td>
                      <span className="small">{log.description}</span>
                    </td>
                    <td>
                      {log.auditable_type ? (
                        <span className="badge bg-light text-primary border">
                          {log.auditable_type} #{log.auditable_id}
                        </span>
                      ) : (
                        <span className="text-muted small">—</span>
                      )}
                    </td>
                    <td className="small text-muted font-monospace">{log.ip_address || '127.0.0.1'}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        <Pagination links={auditLogs.links} />
      </div>
    </AppLayout>
  );
}

