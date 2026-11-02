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

  // Dynamic greeting based on current time
  const currentHour = new Date().getHours();
  const greeting = currentHour < 12 ? 'Good Morning!' : currentHour < 18 ? 'Good Afternoon!' : 'Good Evening!';

  return (
    <AppLayout>
      <Head title="Clinical Document Workflow Dashboard" />

      {/* Top Greeting Header (Exactly as in FinGM Screenshot) */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            {greeting}
          </h1>
          <p className="text-muted mb-0" style={{ fontSize: '13.5px' }}>
            Here's what's happening in your organization today.
          </p>
        </div>
        {auth.user?.can_edit && (
          <Link href="/admissions/create" className="btn btn-primary" style={{ borderRadius: '10px' }}>
            <i className="bi bi-plus-circle me-1"></i> New Admission
          </Link>
        )}
      </div>

      {/* 4 Metric Stats Cards (Matching the 4 top columns in FinGM Dashboard) */}
      <div className="row g-3 mb-4">
        <div className="col-6 col-lg-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '18px' }}>
            <div className="d-flex align-items-center justify-content-between mb-2">
              <span className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px', letterSpacing: '0.05em' }}>
                Total Patients
              </span>
              <span className="p-2 rounded-circle" style={{ background: '#eff6ff', color: '#2563eb' }}>
                <i className="bi bi-people-fill"></i>
              </span>
            </div>
            <div className="h3 fw-bold mb-1 text-dark">{counts.total}</div>
            <div className="text-muted small" style={{ fontSize: '12px' }}>Total Inpatient Records</div>
          </div>
        </div>

        <div className="col-6 col-lg-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '18px' }}>
            <div className="d-flex align-items-center justify-content-between mb-2">
              <span className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px', letterSpacing: '0.05em' }}>
                Review Pending
              </span>
              <span className="p-2 rounded-circle" style={{ background: '#fffbeb', color: '#d97706' }}>
                <i className="bi bi-clock-history"></i>
              </span>
            </div>
            <div className="h3 fw-bold mb-1 text-dark">{counts.ready_for_review}</div>
            <div className="text-muted small" style={{ fontSize: '12px' }}>Awaiting Doctor Approval</div>
          </div>
        </div>

        <div className="col-6 col-lg-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '18px' }}>
            <div className="d-flex align-items-center justify-content-between mb-2">
              <span className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px', letterSpacing: '0.05em' }}>
                Approved & Ready
              </span>
              <span className="p-2 rounded-circle" style={{ background: '#f0fdf4', color: '#16a34a' }}>
                <i className="bi bi-check2-circle"></i>
              </span>
            </div>
            <div className="h3 fw-bold mb-1 text-dark">{counts.approved + counts.generated}</div>
            <div className="text-muted small" style={{ fontSize: '12px' }}>Clinically Signed Packs</div>
          </div>
        </div>

        <div className="col-6 col-lg-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '18px' }}>
            <div className="d-flex align-items-center justify-content-between mb-2">
              <span className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px', letterSpacing: '0.05em' }}>
                Drafts Inactive
              </span>
              <span className="p-2 rounded-circle" style={{ background: '#f8fafc', color: '#64748b' }}>
                <i className="bi bi-pencil-square"></i>
              </span>
            </div>
            <div className="h3 fw-bold mb-1 text-dark">{counts.draft}</div>
            <div className="text-muted small" style={{ fontSize: '12px' }}>Incomplete Clinical Intake</div>
          </div>
        </div>
      </div>

      {/* Middle Row: Recent Activity & Monthly Chart Visual (Exact FinGM Mockup) */}
      <div className="row g-4 mb-4">
        {/* Recent Activity Card */}
        <div className="col-lg-6">
          <div className="card border-0 shadow-sm h-100 p-4" style={{ borderRadius: '20px' }}>
            <div className="d-flex justify-content-between align-items-center mb-3">
              <h5 className="fw-bold mb-0 text-dark" style={{ fontSize: '15px', letterSpacing: '-0.01em' }}>
                Recent Activity
              </h5>
              <span className="badge bg-light text-muted border">Live Updates</span>
            </div>

            <div className="d-flex flex-column gap-3">
              <div className="d-flex align-items-center gap-3">
                <div
                  className="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                  style={{ width: '38px', height: '38px', background: '#dcfce7', color: '#16a34a' }}
                >
                  <i className="bi bi-check2"></i>
                </div>
                <div className="flex-grow-1 min-width-0">
                  <div className="fw-semibold text-dark small">Discharge pack clinically approved</div>
                  <div className="text-muted" style={{ fontSize: '11.5px' }}>Dr. Ambika MS approved Mrs. Anitha &bull; 2 minutes ago</div>
                </div>
              </div>

              <div className="d-flex align-items-center gap-3">
                <div
                  className="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                  style={{ width: '38px', height: '38px', background: '#e0e7ff', color: '#4f46e5' }}
                >
                  <i className="bi bi-plus-lg"></i>
                </div>
                <div className="flex-grow-1 min-width-0">
                  <div className="fw-semibold text-dark small">New obstetric admission recorded</div>
                  <div className="text-muted" style={{ fontSize: '11.5px' }}>Mrs. Ashwini (IP: 2026/582) &bull; 1 hour ago</div>
                </div>
              </div>

              <div className="d-flex align-items-center gap-3">
                <div
                  className="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                  style={{ width: '38px', height: '38px', background: '#f3e8ff', color: '#9333ea' }}
                >
                  <i className="bi bi-file-earmark-medical"></i>
                </div>
                <div className="flex-grow-1 min-width-0">
                  <div className="fw-semibold text-dark small">Operation Theatre notes recorded</div>
                  <div className="text-muted" style={{ fontSize: '11.5px' }}>Mrs. Manjupriya (IP: 2026/778) &bull; 3 hours ago</div>
                </div>
              </div>

              <div className="d-flex align-items-center gap-3">
                <div
                  className="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                  style={{ width: '38px', height: '38px', background: '#ffedd5', color: '#ea580c' }}
                >
                  <i className="bi bi-archive"></i>
                </div>
                <div className="flex-grow-1 min-width-0">
                  <div className="fw-semibold text-dark small">MRD archive finalized</div>
                  <div className="text-muted" style={{ fontSize: '11.5px' }}>Mr. Ajith kumar (IP: 2026/488) &bull; 5 hours ago</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Monthly Activity / Bar Chart Card (Exact FinGM Graphic) */}
        <div className="col-lg-6">
          <div className="card border-0 shadow-sm h-100 p-4" style={{ borderRadius: '20px' }}>
            <div className="d-flex justify-content-between align-items-center mb-3">
              <h5 className="fw-bold mb-0 text-dark" style={{ fontSize: '15px', letterSpacing: '-0.01em' }}>
                Monthly Admissions
              </h5>
              <span className="badge bg-light text-dark border fw-medium" style={{ fontSize: '11px' }}>
                This Year ▾
              </span>
            </div>

            {/* Visual Bar Chart replicating FinGM image */}
            <div className="d-flex align-items-end justify-content-between pt-3 pb-2 px-2" style={{ height: '170px' }}>
              {[
                { month: 'Jan', height: '45%' },
                { month: 'Feb', height: '65%' },
                { month: 'Mar', height: '55%' },
                { month: 'Apr', height: '80%' },
                { month: 'May', height: '95%' },
                { month: 'Jun', height: '70%' },
              ].map((item, idx) => (
                <div key={idx} className="d-flex flex-column align-items-center gap-2" style={{ width: '12%' }}>
                  <div
                    className="w-100"
                    style={{
                      height: item.height,
                      background: idx === 4 ? '#2563eb' : '#93c5fd',
                      borderRadius: '6px 6px 0 0',
                      transition: 'height 0.3s ease',
                      minHeight: '20px',
                    }}
                    title={`${item.month}: Active`}
                  ></div>
                  <span className="text-muted" style={{ fontSize: '11px' }}>{item.month}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="card border-0 shadow-sm mb-4" style={{ borderRadius: '18px' }}>
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-center">
            <div className="col-md-4">
              <div className="input-group input-group-sm">
                <span className="input-group-text bg-white border-end-0">
                  <i className="bi bi-search text-muted"></i>
                </span>
                <input
                  type="text"
                  className="form-control border-start-0"
                  placeholder="Search patient, UHID, IP No..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
              </div>
            </div>

            <div className="col-md-2">
              <select
                className="form-select form-select-sm"
                value={status}
                onChange={(e) => setStatus(e.target.value)}
              >
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="ready_for_review">Ready for Review</option>
                <option value="returned_for_correction">Returned</option>
                <option value="approved">Approved</option>
                <option value="generated">Generated</option>
              </select>
            </div>

            <div className="col-md-2">
              <select
                className="form-select form-select-sm"
                value={format}
                onChange={(e) => setFormat(e.target.value)}
              >
                <option value="">All Formats</option>
                <option value="general">General</option>
                <option value="cmchistn">CMCHISTN</option>
                <option value="employee">Employee</option>
                <option value="private">Private</option>
              </select>
            </div>

            <div className="col-md-2">
              <select
                className="form-select form-select-sm"
                value={surgeryType}
                onChange={(e) => setSurgeryType(e.target.value)}
              >
                <option value="">All Workflows</option>
                <option value="delivery">Delivery</option>
                <option value="other">Other Surgery</option>
                <option value="medical">Medical Case</option>
              </select>
            </div>

            <div className="col-md-2 d-flex gap-1">
              <button type="submit" className="btn btn-sm btn-primary w-100" style={{ borderRadius: '8px' }}>
                Filter
              </button>
              <button type="button" onClick={handleReset} className="btn btn-sm btn-outline-secondary" style={{ borderRadius: '8px' }}>
                Reset
              </button>
            </div>
          </form>
        </div>
      </div>

      {/* Admissions Table */}
      <div className="card shadow-sm border-0" style={{ borderRadius: '20px' }}>
        <div className="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
          <h5 className="card-title mb-0 fw-bold" style={{ fontSize: '15px', letterSpacing: '-0.01em' }}>
            Inpatient Admissions Directory
          </h5>
          <span className="badge bg-light text-muted border">
            Showing {admissions.data.length} of {admissions.total}
          </span>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0" style={{ fontSize: '13.5px' }}>
            <thead className="table-light text-muted small text-uppercase">
              <tr>
                <th>Patient Details</th>
                <th>Admission / Discharge</th>
                <th>Format & Workflow</th>
                <th>Status</th>
                <th className="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              {admissions.data.length === 0 ? (
                <tr>
                  <td colSpan={5} className="text-center py-5 text-muted">
                    <i className="bi bi-folder-x fs-2 d-block mb-2 text-muted"></i>
                    No patient records found.
                  </td>
                </tr>
              ) : (
                admissions.data.map((item) => (
                  <tr key={item.id}>
                    <td>
                      <div className="fw-bold text-dark">{item.patient_name}</div>
                      <div className="text-muted small">
                        UHID: <span className="font-monospace text-dark">{item.uhid}</span> &bull;
                        IP: <span className="font-monospace text-dark">{item.ip_no}</span>
                      </div>
                    </td>
                    <td>
                      <div>{item.admission_at ? new Date(item.admission_at).toLocaleDateString('en-GB') : '—'}</div>
                      <div className="text-muted small">
                        to {item.discharge_at ? new Date(item.discharge_at).toLocaleDateString('en-GB') : '—'}
                      </div>
                    </td>
                    <td>
                      <span className="badge bg-light text-dark border text-uppercase me-1" style={{ fontSize: '10px' }}>
                        {item.format}
                      </span>
                      <span className="badge bg-secondary" style={{ fontSize: '10px' }}>
                        {item.surgery_type === 'delivery'
                          ? `Delivery (${item.newborns?.length || 0})`
                          : item.surgery_type === 'medical'
                          ? 'Medical'
                          : 'Other'}
                      </span>
                    </td>
                    <td>
                      <span className={`badge ${getStatusBadge(item.status)} text-uppercase`} style={{ fontSize: '10.5px' }}>
                        {item.status.replace(/_/g, ' ')}
                      </span>
                    </td>
                    <td className="text-end">
                      <div className="btn-group btn-group-sm">
                        <div className="btn-group btn-group-sm">
                          <Link href={`/admissions/${item.id}/documents/preview`} className="btn btn-outline-secondary">
                            <i className="bi bi-eye me-1"></i> Review
                          </Link>
                          <button
                            type="button"
                            className="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Preview separate documents"
                          >
                            <span className="visually-hidden">Preview options</span>
                          </button>
                          <ul className="dropdown-menu shadow-sm border-0" style={{ fontSize: '12px' }}>
                            <li>
                              <h6 className="dropdown-header text-uppercase fw-bold" style={{ fontSize: '10px' }}>
                                Separate Document Previews
                              </h6>
                            </li>
                            <li>
                              <Link className="dropdown-item py-1" href={`/admissions/${item.id}/documents/preview?doc=discharge_summary`}>
                                <i className="bi bi-file-earmark-text text-primary me-2"></i>1. Discharge Summary
                              </Link>
                            </li>
                            <li>
                              <Link className="dropdown-item py-1" href={`/admissions/${item.id}/documents/preview?doc=operation_notes`}>
                                <i className="bi bi-journal-medical text-success me-2"></i>2. Operation Theatre Notes
                              </Link>
                            </li>
                            <li>
                              <Link className="dropdown-item py-1" href={`/admissions/${item.id}/documents/preview?doc=mrd_form`}>
                                <i className="bi bi-file-earmark-check text-warning me-2"></i>3. MRD Inpatient Form
                              </Link>
                            </li>
                            <li><hr className="dropdown-divider my-1" /></li>
                            <li>
                              <Link className="dropdown-item py-1 fw-semibold" href={`/admissions/${item.id}/documents/preview?doc=all`}>
                                <i className="bi bi-collection text-dark me-2"></i>All Documents (Pack)
                              </Link>
                            </li>
                            <li>
                              <Link className="dropdown-item py-1 text-danger fw-semibold" href={`/admissions/${item.id}/documents/preview?mode=pdf`}>
                                <i className="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Playwright PDF Preview
                              </Link>
                            </li>
                          </ul>
                        </div>
                        {auth.user?.can_edit && (item.status === 'draft' || item.status === 'returned_for_correction') && (
                          <Link href={`/admissions/${item.id}/edit`} className="btn btn-outline-secondary">
                            <i className="bi bi-pencil"></i>
                          </Link>
                        )}
                        {(item.status === 'approved' || item.status === 'generated') && (
                          <div className="btn-group btn-group-sm">
                            <a
                              href={`/admissions/${item.id}/documents/pdf/discharge-summary`}
                              target="_blank"
                              rel="noreferrer"
                              className="btn btn-outline-primary"
                              title="Download Discharge Summary PDF"
                            >
                              <i className="bi bi-file-earmark-pdf"></i>
                            </a>
                            <button
                              type="button"
                              className="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
                              data-bs-toggle="dropdown"
                              aria-expanded="false"
                              title="Download Separate PDFs"
                            >
                              <span className="visually-hidden">Toggle PDF Options</span>
                            </button>
                            <ul className="dropdown-menu dropdown-menu-end shadow-sm border-0" style={{ fontSize: '12px' }}>
                              <li>
                                <a className="dropdown-item py-1" href={`/admissions/${item.id}/documents/pdf/discharge-summary`} target="_blank" rel="noreferrer">
                                  <i className="bi bi-file-earmark-text text-primary me-2"></i>1. Discharge Summary
                                </a>
                              </li>
                              <li>
                                <a className="dropdown-item py-1" href={`/admissions/${item.id}/documents/pdf/operation-notes`} target="_blank" rel="noreferrer">
                                  <i className="bi bi-journal-medical text-success me-2"></i>2. Operation Theatre Notes
                                </a>
                              </li>
                              <li>
                                <a className="dropdown-item py-1" href={`/admissions/${item.id}/documents/pdf/mrd-form`} target="_blank" rel="noreferrer">
                                  <i className="bi bi-file-earmark-check text-warning me-2"></i>3. MRD Inpatient Form
                                </a>
                              </li>
                              <li><hr className="dropdown-divider my-1" /></li>
                              <li>
                                <a className="dropdown-item py-1 fw-semibold" href={`/admissions/${item.id}/documents/pdf/pack`} target="_blank" rel="noreferrer">
                                  <i className="bi bi-collection text-dark me-2"></i>Complete 3-in-1 Pack
                                </a>
                              </li>
                            </ul>
                          </div>
                        )}
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}
