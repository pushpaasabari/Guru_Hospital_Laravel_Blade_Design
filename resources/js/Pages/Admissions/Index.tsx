import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

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
}

interface Props {
  admissions: {
    data: AdmissionItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  filters?: {
    search?: string;
    status?: string;
  };
  auth: {
    user: {
      can_edit?: boolean;
      can_generate_pdf?: boolean;
    };
  };
}

export default function AdmissionsIndex({ admissions, filters = {}, auth }: Props) {
  const [search, setSearch] = useState(filters.search || '');
  const [status, setStatus] = useState(filters.status || '');

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admissions', { search, status }, { preserveState: true });
  };

  const handleClear = () => {
    setSearch('');
    setStatus('');
    router.get('/admissions');
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
      <Head title="Inpatient Admissions Archive — Guru Hospital" />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Inpatient Admissions Archive
          </h1>
          <p className="text-muted mb-0">Search and manage patient admission records, discharge summaries, and MRD dossiers.</p>
        </div>
        {auth.user?.can_edit && (
          <Link href="/admissions/create" className="btn btn-primary">
            <i className="bi bi-plus-circle me-1"></i> New Admission
          </Link>
        )}
      </div>

      <div className="card mb-4 border-0">
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-center">
            <div className="col-md-4">
              <div className="input-group input-group-sm">
                <span className="input-group-text bg-light"><i className="bi bi-search"></i></span>
                <input
                  type="text"
                  className="form-control"
                  placeholder="Search Patient, UHID, IP No..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
              </div>
            </div>
            <div className="col-md-3">
              <select className="form-select form-select-sm" value={status} onChange={(e) => setStatus(e.target.value)}>
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="ready_for_review">Ready for Review</option>
                <option value="returned_for_correction">Returned for Correction</option>
                <option value="approved">Approved</option>
                <option value="generated">Generated</option>
                <option value="superseded">Superseded</option>
              </select>
            </div>
            <div className="col-md-3">
              <button type="submit" className="btn btn-sm btn-primary w-100">Filter Records</button>
            </div>
            <div className="col-md-2">
              <button type="button" onClick={handleClear} className="btn btn-sm btn-outline-secondary w-100">Clear</button>
            </div>
          </form>
        </div>
      </div>

      <div className="card shadow-sm border-0">
        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="table-light text-muted small text-uppercase">
              <tr>
                <th>Patient Name & Demographics</th>
                <th>UHID & IP No</th>
                <th>Workflow</th>
                <th>Dates</th>
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
                        {adm.age ? `${adm.age} yrs` : 'Age N/A'} / {adm.sex || 'N/A'}
                      </div>
                    </td>
                    <td>
                      <div className="small"><span className="text-muted">UHID:</span> {adm.uhid}</div>
                      <div className="small fw-semibold text-primary"><span className="text-muted">IP:</span> {adm.ip_no}</div>
                    </td>
                    <td>
                      <span className="badge bg-secondary text-uppercase">{adm.format}</span>
                      <div className="small text-muted mt-1">
                        {adm.surgery_type === 'delivery' ? 'Delivery / Obstetric' : 'Other Surgery'}
                      </div>
                    </td>
                    <td>
                      <div className="small">Adm: {adm.admission_at?.substring(0, 10)}</div>
                      <div className="small text-muted">Dis: {adm.discharge_at?.substring(0, 10)}</div>
                    </td>
                    <td>
                      <span className={`badge ${getStatusBadge(adm.status)} text-uppercase`}>
                        {adm.status.replace(/_/g, ' ')}
                      </span>
                    </td>
                    <td className="text-end">
                      <div className="btn-group btn-group-sm">
                        <div className="btn-group btn-group-sm">
                          <Link href={`/admissions/${adm.id}/documents/preview`} className="btn btn-outline-secondary" title="Preview Pack">
                            Preview
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
                              <Link className="dropdown-item py-1" href={`/admissions/${adm.id}/documents/preview?doc=discharge_summary`}>
                                <i className="bi bi-file-earmark-text text-primary me-2"></i>1. Discharge Summary
                              </Link>
                            </li>
                            <li>
                              <Link className="dropdown-item py-1" href={`/admissions/${adm.id}/documents/preview?doc=operation_notes`}>
                                <i className="bi bi-journal-medical text-success me-2"></i>2. Operation Theatre Notes
                              </Link>
                            </li>
                            <li>
                              <Link className="dropdown-item py-1" href={`/admissions/${adm.id}/documents/preview?doc=mrd_form`}>
                                <i className="bi bi-file-earmark-check text-warning me-2"></i>3. MRD Inpatient Form
                              </Link>
                            </li>
                            <li><hr className="dropdown-divider my-1" /></li>
                            <li>
                              <Link className="dropdown-item py-1 fw-semibold" href={`/admissions/${adm.id}/documents/preview?doc=all`}>
                                <i className="bi bi-collection text-dark me-2"></i>All Documents (Pack)
                              </Link>
                            </li>
                            <li>
                              <Link className="dropdown-item py-1 text-danger fw-semibold" href={`/admissions/${adm.id}/documents/preview?mode=pdf`}>
                                <i className="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Playwright PDF Preview
                              </Link>
                            </li>
                          </ul>
                        </div>
                        {auth.user?.can_edit && (
                          <div className="btn-group btn-group-sm">
                            <Link href={`/admissions/${adm.id}/edit`} className="btn btn-outline-secondary" title="Edit">
                              Edit
                            </Link>
                            <button
                              type="button"
                              className="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                              data-bs-toggle="dropdown"
                              aria-expanded="false"
                            >
                              <span className="visually-hidden">Toggle Stages</span>
                            </button>
                            <ul className="dropdown-menu shadow-sm border-0" style={{ fontSize: '12px' }}>
                              <li>
                                <Link className="dropdown-item py-1" href={`/admissions/${adm.id}/edit`}>
                                  <i className="bi bi-person me-2 text-primary"></i>1. Admission Details
                                </Link>
                              </li>
                              <li>
                                <Link className="dropdown-item py-1" href={`/admissions/${adm.id}/surgery`}>
                                  <i className="bi bi-heart-pulse me-2 text-danger"></i>2. Labour / Surgery
                                </Link>
                              </li>
                              <li>
                                <Link className="dropdown-item py-1" href={`/admissions/${adm.id}/test-reports`}>
                                  <i className="bi bi-file-earmark-medical me-2 text-info"></i>3. Test Reports
                                </Link>
                              </li>
                              <li>
                                <Link className="dropdown-item py-1" href={`/admissions/${adm.id}/discharge`}>
                                  <i className="bi bi-clipboard2-check me-2 text-success"></i>4. Discharge
                                </Link>
                              </li>
                            </ul>
                          </div>
                        )}
                        <Link href={`/documents/${adm.id}/history`} className="btn btn-outline-secondary" title="History">
                          History
                        </Link>
                        {['approved', 'generated'].includes(adm.status) && (
                          <div className="btn-group btn-group-sm">
                            <a
                              href={`/admissions/${adm.id}/documents/pdf/discharge-summary`}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="btn btn-primary"
                              title="Download Discharge Summary PDF"
                            >
                              PDF
                            </a>
                            <button
                              type="button"
                              className="btn btn-primary dropdown-toggle dropdown-toggle-split"
                              data-bs-toggle="dropdown"
                              aria-expanded="false"
                              title="Download Separate PDFs"
                            >
                              <span className="visually-hidden">Toggle PDF Options</span>
                            </button>
                            <ul className="dropdown-menu dropdown-menu-end shadow-sm border-0" style={{ fontSize: '12px' }}>
                              <li>
                                <a className="dropdown-item py-1" href={`/admissions/${adm.id}/documents/pdf/discharge-summary`} target="_blank" rel="noreferrer">
                                  <i className="bi bi-file-earmark-text text-primary me-2"></i>1. Discharge Summary
                                </a>
                              </li>
                              <li>
                                <a className="dropdown-item py-1" href={`/admissions/${adm.id}/documents/pdf/operation-notes`} target="_blank" rel="noreferrer">
                                  <i className="bi bi-journal-medical text-success me-2"></i>2. Operation Theatre Notes
                                </a>
                              </li>
                              <li>
                                <a className="dropdown-item py-1" href={`/admissions/${adm.id}/documents/pdf/mrd-form`} target="_blank" rel="noreferrer">
                                  <i className="bi bi-file-earmark-check text-warning me-2"></i>3. MRD Inpatient Form
                                </a>
                              </li>
                              <li><hr className="dropdown-divider my-1" /></li>
                              <li>
                                <a className="dropdown-item py-1 fw-semibold" href={`/admissions/${adm.id}/documents/pdf/pack`} target="_blank" rel="noreferrer">
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
              ) : (
                <tr>
                  <td colSpan={6} className="text-center py-5 text-muted">
                    <i className="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                    No records found.
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

