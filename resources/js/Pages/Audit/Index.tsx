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
  filters?: {
    category?: string;
    action?: string;
    user_name?: string;
    date_from?: string;
    date_to?: string;
  };
}

export default function Index({ auditLogs, filters = {} }: Props) {
  const urlParams = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');
  const [category, setCategory] = useState(filters.category || urlParams.get('category') || '');
  const [action, setAction] = useState(filters.action || urlParams.get('action') || '');
  const [userName, setUserName] = useState(filters.user_name || urlParams.get('user_name') || '');
  const [dateFrom, setDateFrom] = useState(filters.date_from || urlParams.get('date_from') || '');
  const [dateTo, setDateTo] = useState(filters.date_to || urlParams.get('date_to') || '');

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      '/audit-logs',
      {
        category: category || undefined,
        action: action || undefined,
        user_name: userName || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
      },
      { preserveState: true }
    );
  };

  const applyCategory = (nextCat: string) => {
    setCategory(nextCat);
    router.get(
      '/audit-logs',
      {
        category: nextCat || undefined,
        action: action || undefined,
        user_name: userName || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
      },
      { preserveState: true }
    );
  };

  const handleReset = () => {
    setCategory('');
    setAction('');
    setUserName('');
    setDateFrom('');
    setDateTo('');
    router.get('/audit-logs');
  };

  const formatDateTime = (dateStr: string) => {
    try {
      const d = new Date(dateStr);
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      const time = d.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
      });
      return `${day}.${month}.${year} ${time}`;
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

      {/* Category Quick Tabs + Filter Card */}
      <div className="d-flex flex-wrap gap-2 mb-3">
        {[
          { key: '', label: 'All Activity' },
          { key: 'ui_clicks', label: 'UI Clicks & Controls' },
          { key: 'page_views', label: 'Page Visits' },
          { key: 'clinical', label: 'Clinical & Auth Actions' },
        ].map((tab) => (
          <button
            key={tab.key || 'all'}
            type="button"
            onClick={() => applyCategory(tab.key)}
            className={`btn btn-sm ${category === tab.key ? 'btn-primary' : 'btn-outline-secondary'}`}
          >
            {tab.label}
          </button>
        ))}
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
          <table className="table table-hover align-middle mb-0 fg-fit-table fg-mobile-stack-table" style={{ fontSize: '0.88rem' }}>
            <thead className="table-light text-muted small text-uppercase">
              <tr>
                <th style={{ width: '15%' }}>Timestamp</th>
                <th style={{ width: '16%' }}>Staff User</th>
                <th style={{ width: '14%' }}>Action</th>
                <th style={{ width: '28%' }}>Description</th>
                <th style={{ width: '14%' }}>Entity</th>
                <th style={{ width: '13%' }}>IP Address</th>
              </tr>
            </thead>
            <tbody>
              {!auditLogs.data || auditLogs.data.length === 0 ? (
                <tr className="fg-empty-row">
                  <td colSpan={6} className="text-center py-5 text-muted">
                    <i className="bi bi-shield-x fs-2 d-block mb-2 text-muted"></i>
                    No audit events found matching the selected parameters.
                  </td>
                </tr>
              ) : (
                auditLogs.data.map((log) => (
                  <tr key={log.id}>
                    <td className="small text-muted" data-label="Timestamp">{formatDateTime(log.created_at)}</td>
                    <td data-label="Staff User">
                      <div className="fw-semibold text-dark">{log.user_name}</div>
                      <span
                        className="badge bg-light text-dark border text-uppercase"
                        style={{ fontSize: '0.65rem' }}
                      >
                        {log.user_role}
                      </span>
                    </td>
                    <td data-label="Action">
                      <span className="badge bg-secondary font-monospace">{log.action}</span>
                    </td>
                    <td data-label="Description">
                      <span className="small">{log.description}</span>
                    </td>
                    <td data-label="Entity">
                      {log.auditable_type ? (
                        <span className="badge bg-light text-primary border">
                          {log.auditable_type} #{log.auditable_id}
                        </span>
                      ) : (
                        <span className="text-muted small">—</span>
                      )}
                    </td>
                    <td className="small text-muted font-monospace" data-label="IP Address">{log.ip_address || '127.0.0.1'}</td>
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

