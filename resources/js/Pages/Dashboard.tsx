import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Pagination from '../Components/Pagination';
import ActionDropdown, {
  buildDocumentPreviewMenuItems,
  buildPdfDownloadMenuItems,
} from '../Components/ActionDropdown';
import { formatDate } from '../utils/dateFormatter';

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

      {/* Filter and Search Bar */}
      <div className="card border-0 shadow-sm mb-4" style={{ borderRadius: '18px' }}>
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-center">
            <div className="col-md-4">
              <div className="fg-search-input-wrap">
                <i className="bi bi-search fg-search-icon" aria-hidden="true"></i>
                <input
                  type="text"
                  className="form-control form-control-sm"
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
                      <div>{item.admission_at ? formatDate(item.admission_at) : '—'}</div>
                      <div className="text-muted small">
                        to {item.discharge_at ? formatDate(item.discharge_at) : '—'}
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
                        <ActionDropdown
                          toggleTitle="Preview separate documents"
                          fontSize="12px"
                          items={buildDocumentPreviewMenuItems(item.id)}
                          primaryAction={
                            <Link href={`/admissions/${item.id}/documents/preview`} prefetch className="btn btn-outline-secondary">
                              <i className="bi bi-eye me-1"></i> Review
                            </Link>
                          }
                        />
                        {auth.user?.can_edit && (item.status === 'draft' || item.status === 'returned_for_correction') && (
                          <Link href={`/admissions/${item.id}/edit`} prefetch className="btn btn-outline-secondary">
                            <i className="bi bi-pencil"></i>
                          </Link>
                        )}
                        {(item.status === 'approved' || item.status === 'generated') && (
                          <ActionDropdown
                            toggleClassName="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
                            toggleTitle="Download Separate PDFs"
                            fontSize="12px"
                            items={buildPdfDownloadMenuItems(item.id)}
                            primaryAction={
                              <a
                                href={`/admissions/${item.id}/documents/pdf/discharge-summary`}
                                target="_blank"
                                rel="noreferrer"
                                className="btn btn-outline-primary"
                                title="Download Discharge Summary PDF"
                              >
                                <i className="bi bi-file-earmark-pdf"></i>
                              </a>
                            }
                          />
                        )}
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
        <Pagination links={admissions.links} />
      </div>
    </AppLayout>
  );
}
