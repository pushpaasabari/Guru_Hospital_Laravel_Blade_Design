import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';

interface AdmissionItem {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  age?: string;
  sex?: string;
  format: string;
  surgery_type: string;
  status: string;
  admission_at: string;
  discharge_at: string;
  rejection_reason?: string;
  newborns: Array<{ id: number }>;
}

interface PaginationProps {
  data: AdmissionItem[];
  current_page: number;
  last_page: number;
  total: number;
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
  counts: {
    total: number;
    draft: number;
    ready_for_review: number;
    returned_for_correction: number;
    approved: number;
    generated: number;
  };
  admissions: PaginationProps;
  filters?: {
    search?: string;
    status?: string;
    format?: string;
    surgery_type?: string;
  };
  auth: {
    user: {
      can_edit?: boolean;
      can_generate_pdf?: boolean;
    };
  };
}

export default function Dashboard({ counts, admissions, filters = {}, auth }: Props) {
  const [search, setSearch] = useState(filters.search || '');
  const [status, setStatus] = useState(filters.status || '');
  const [format, setFormat] = useState(filters.format || '');
  const [surgeryType, setSurgeryType] = useState(filters.surgery_type || '');

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/dashboard', { search, status, format, surgery_type: surgeryType }, { preserveState: true });
  };

  const handleReset = () => {
    setSearch('');
    setStatus('');
    setFormat('');
    setSurgeryType('');
    router.get('/dashboard');
  };

  const getStatusBadge = (st: string) => {
    switch (st) {
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
      <Head title="Clinical Workflow Dashboard" />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Clinical Document Workflow Dashboard
          </h1>
          <p className="text-muted mb-0">Overview of inpatient discharge summaries, OT records, and MRD archives</p>
        </div>
        {auth.user?.can_edit && (
          <Link href="/admissions/create" className="btn btn-primary">
            <i className="bi bi-plus-circle me-1"></i> New Admission / Document Pack
          </Link>
        )}
      </div>

      {/* Workflow KPI Cards */}
      <div className="row g-3 mb-4">
        <div className="col-md-2 col-6">
          <div className="fg-kpi-card">
            <div className="fg-kpi-top">
              <span className="fg-kpi-label">Drafts</span>
              <span className="fg-kpi-dot" style={{ background: '#94a3b8' }}></span>
            </div>
            <div className="fg-kpi-number text-secondary">{counts.draft}</div>
            <p className="fg-kpi-desc">In progress</p>
          </div>
        </div>
        <div className="col-md-2 col-6">
          <div className="fg-kpi-card">
            <div className="fg-kpi-top">
              <span className="fg-kpi-label">Review</span>
              <span className="fg-kpi-dot" style={{ background: '#0284c7' }}></span>
            </div>
            <div className="fg-kpi-number text-info">{counts.ready_for_review}</div>
            <p className="fg-kpi-desc">Awaiting doctor</p>
          </div>
        </div>
        <div className="col-md-2 col-6">
          <div className="fg-kpi-card">
            <div className="fg-kpi-top">
              <span className="fg-kpi-label">Returned</span>
              <span className="fg-kpi-dot" style={{ background: '#dc2626' }}></span>
            </div>
            <div className="fg-kpi-number text-danger">{counts.returned_for_correction}</div>
            <p className="fg-kpi-desc">Needs correction</p>
          </div>
        </div>
        <div className="col-md-2 col-6">
          <div className="fg-kpi-card">
            <div className="fg-kpi-top">
              <span className="fg-kpi-label">Approved</span>
              <span className="fg-kpi-dot" style={{ background: '#059669' }}></span>
            </div>
            <div className="fg-kpi-number text-success">{counts.approved}</div>
            <p className="fg-kpi-desc">Clinically signed</p>
          </div>
        </div>
        <div className="col-md-2 col-6">
          <div className="fg-kpi-card">
            <div className="fg-kpi-top">
              <span className="fg-kpi-label">Generated</span>
              <span className="fg-kpi-dot" style={{ background: '#7c3aed' }}></span>
            </div>
            <div className="fg-kpi-number text-primary">{counts.generated}</div>
            <p className="fg-kpi-desc">Official PDF pack</p>
          </div>
        </div>
        <div className="col-md-2 col-6">
          <div className="fg-kpi-card">
            <div className="fg-kpi-top">
              <span className="fg-kpi-label">Total Cases</span>
              <span className="fg-kpi-dot" style={{ background: '#080808' }}></span>
            </div>
            <div className="fg-kpi-number text-dark">{counts.total}</div>
            <p className="fg-kpi-desc">All admissions</p>
          </div>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="card mb-4 border-0">
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-center">
            <div className="col-md-3">
              <div className="input-group input-group-sm">
                <span className="input-group-text bg-light"><i className="bi bi-search"></i></span>
                <input
                  type="text"
                  className="form-control"
                  placeholder="Search Patient, UHID, IP..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
              </div>
            </div>
            <div className="col-md-2">
              <select className="form-select form-select-sm" value={status} onChange={(e) => setStatus(e.target.value)}>
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="ready_for_review">Ready for Review</option>
                <option value="returned_for_correction">Returned</option>
                <option value="approved">Approved</option>
                <option value="generated">Generated</option>
              </select>
            </div>
            <div className="col-md-2">
              <select className="form-select form-select-sm" value={format} onChange={(e) => setFormat(e.target.value)}>
                <option value="">All Formats</option>
                <option value="general">General</option>
                <option value="cmchistn">CMCHISTN</option>
                <option value="private">Private Insurance</option>
                <option value="employee">Employee</option>
              </select>
            </div>
            <div className="col-md-2">
              <select className="form-select form-select-sm" value={surgeryType} onChange={(e) => setSurgeryType(e.target.value)}>
                <option value="">All Workflows</option>
                <option value="delivery">Delivery / Obstetric</option>
                <option value="other">Other Surgery</option>
              </select>
            </div>
            <div className="col-md-3 d-flex gap-2">
              <button type="submit" className="btn btn-sm btn-primary w-100">
                <i className="bi bi-filter"></i> Apply Filters
              </button>
              <button type="button" onClick={handleReset} className="btn btn-sm btn-outline-secondary">
                Reset
              </button>
            </div>
          </form>
        </div>
      </div>

      {/* Admissions Table */}
      <div className="card shadow-sm border-0">
        <div className="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 className="card-title mb-0 fw-bold" style={{ letterSpacing: '-0.01em' }}>
            Recent Inpatient Admissions
          </h5>
          <span className="text-muted small">
            Showing {admissions.data.length} of {admissions.total} records
          </span>
        </div>
        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="table-light text-muted small text-uppercase">
              <tr>
                <th>Patient Name</th>
                <th>Identifiers</th>
                <th>Workflow & Format</th>
                <th>Admission / Discharge</th>
                <th>Status</th>
                <th className="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              {admissions.data.length > 0 ? (
                admissions.data.map((adm) => (
                  <tr key={adm.id}>
                    <td>
                      <div className="fw-bold text-dark">{adm.patient_name}</div>
                      <div className="text-muted small">
                        {adm.age ? `${adm.age} yrs` : 'Age N/A'} &bull; {adm.sex || 'N/A'}
                      </div>
                    </td>
                    <td>
                      <div className="small">
                        <span className="text-muted">UHID:</span> <span className="fw-semibold">{adm.uhid}</span>
                      </div>
                      <div className="small">
                        <span className="text-muted">IP No:</span>{' '}
                        <span className="badge bg-light text-dark border">{adm.ip_no}</span>
                      </div>
                    </td>
                    <td>
                      <div>
                        {adm.surgery_type === 'delivery' ? (
                          <span className="badge" style={{ background: '#fdf2f8', color: '#be185d', border: '1px solid #fbcfe8' }}>
                            <i className="bi bi-gender-female"></i> Delivery ({adm.newborns?.length || 0} baby)
                          </span>
                        ) : (
                          <span className="badge" style={{ background: '#f0f9ff', color: '#0369a1', border: '1px solid #bae6fd' }}>
                            <i className="bi bi-heart-pulse"></i> Surgery
                          </span>
                        )}
                      </div>
                      <span className="badge bg-secondary text-uppercase mt-1" style={{ fontSize: '0.65rem' }}>
                        {adm.format}
                      </span>
                    </td>
                    <td>
                      <div className="small text-muted">{adm.admission_at?.substring(0, 10)}</div>
                      <div className="small text-muted">to {adm.discharge_at?.substring(0, 10)}</div>
                    </td>
                    <td>
                      <span className={`badge ${getStatusBadge(adm.status)} text-uppercase`}>
                        {adm.status.replace(/_/g, ' ')}
                      </span>
                      {adm.status === 'returned_for_correction' && adm.rejection_reason && (
                        <div
                          className="text-danger small mt-1"
                          style={{ maxWidth: '200px', fontSize: '0.75rem' }}
                          title={adm.rejection_reason}
                        >
                          <i className="bi bi-info-circle"></i> {adm.rejection_reason.substring(0, 40)}
                        </div>
                      )}
                    </td>
                    <td className="text-end">
                      <div className="btn-group btn-group-sm">
                        <Link href={`/documents/${adm.id}/preview`} className="btn btn-outline-secondary" title="Preview Pack">
                          <i className="bi bi-eye"></i> Preview
                        </Link>
                        {auth.user?.can_edit && (
                          <Link href={`/admissions/${adm.id}/edit`} className="btn btn-outline-secondary" title="Edit">
                            <i className="bi bi-pencil"></i>
                          </Link>
                        )}
                        <Link href={`/documents/${adm.id}/history`} className="btn btn-outline-secondary" title="History">
                          <i className="bi bi-clock-history"></i>
                        </Link>
                        {['approved', 'generated'].includes(adm.status) && (
                          <a
                            href={`/documents/${adm.id}/pdf`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="btn btn-primary"
                            title="Download Official PDF"
                          >
                            <i className="bi bi-file-earmark-pdf"></i> PDF
                          </a>
                        )}
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={6} className="text-center py-5 text-muted">
                    <i className="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                    No admissions found matching the selected criteria.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}
