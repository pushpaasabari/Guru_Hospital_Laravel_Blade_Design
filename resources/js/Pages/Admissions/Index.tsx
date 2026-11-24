import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import ActionDropdown, {
  buildDocumentPreviewMenuItems,
  buildEditStageMenuItems,
  buildPdfDownloadMenuItems,
} from '../../Components/ActionDropdown';
import DischargeIntimationModal from '../../Components/DischargeIntimationModal';
import { formatDate } from '../../utils/dateFormatter';

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
  const [intimationAdmission, setIntimationAdmission] = useState<any>(null);

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
              <div className="fg-search-input-wrap">
                <i className="bi bi-search fg-search-icon" aria-hidden="true"></i>
                <input
                  type="text"
                  className="form-control form-control-sm"
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
                        {adm.surgery_type === 'delivery'
                          ? 'Delivery / Obstetric'
                          : adm.surgery_type === 'medical'
                          ? 'Medical Case'
                          : 'Other Surgery'}
                      </div>
                    </td>
                    <td>
                      <div className="small">Adm: {formatDate(adm.admission_at)}</div>
                      <div className="small text-muted">Dis: {formatDate(adm.discharge_at)}</div>
                    </td>
                    <td>
                      <span className={`badge ${getStatusBadge(adm.status)} text-uppercase`}>
                        {adm.status.replace(/_/g, ' ')}
                      </span>
                    </td>
                    <td className="text-end">
                      <div className="btn-group btn-group-sm">
                        <ActionDropdown
                          toggleTitle="Preview separate documents"
                          fontSize="12px"
                          items={buildDocumentPreviewMenuItems(adm.id)}
                          primaryAction={
                            <Link href={`/admissions/${adm.id}/documents/preview`} prefetch className="btn btn-outline-secondary" title="Preview Pack">
                              Preview
                            </Link>
                          }
                        />
                        {auth.user?.can_edit && (
                          <ActionDropdown
                            toggleTitle="Toggle Stages"
                            fontSize="12px"
                            items={buildEditStageMenuItems(adm.id)}
                            primaryAction={
                              <Link href={`/admissions/${adm.id}/edit`} prefetch className="btn btn-outline-secondary" title="Edit">
                                Edit
                              </Link>
                            }
                          />
                        )}
                        <Link href={`/documents/${adm.id}/history`} prefetch className="btn btn-outline-secondary" title="History">
                          History
                        </Link>
                        {['approved', 'generated'].includes(adm.status) && (
                          <ActionDropdown
                            toggleClassName="btn btn-primary dropdown-toggle dropdown-toggle-split"
                            toggleTitle="Download Separate PDFs"
                            fontSize="12px"
                            items={buildPdfDownloadMenuItems(adm.id, undefined, () => setIntimationAdmission(adm))}
                            primaryAction={
                              <a
                                href={`/admissions/${adm.id}/documents/pdf/discharge-summary`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="btn btn-primary"
                                title="Download Discharge Summary PDF"
                              >
                                PDF
                              </a>
                            }
                          />
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
        <Pagination links={admissions.links} />
      </div>

      {/* Discharge Intimation WhatsApp Copy Modal */}
      {intimationAdmission && (
        <DischargeIntimationModal
          show={true}
          onClose={() => setIntimationAdmission(null)}
          admission={intimationAdmission}
        />
      )}
    </AppLayout>
  );
}

