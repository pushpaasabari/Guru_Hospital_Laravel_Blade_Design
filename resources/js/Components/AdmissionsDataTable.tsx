import React, { useState, useMemo } from 'react';
import { Link } from '@inertiajs/react';
import ActionDropdown, {
  buildDocumentPreviewMenuItems,
  buildPdfDownloadMenuItems,
} from './ActionDropdown';
import DischargeIntimationModal from './DischargeIntimationModal';
import { formatDate } from '../utils/dateFormatter';

export interface AdmissionItem {
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
  consultant?: string;
  department?: string;
  ward_room_no?: string;
  newborns?: Array<{ id: number }>;
}

export interface AdmissionsDataTableProps {
  admissions: {
    data: AdmissionItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  counts: {
    total: number;
    draft: number;
    ready_for_review: number;
    returned_for_correction: number;
    approved: number;
    generated: number;
  };
  filters?: {
    search?: string;
    status?: string;
    format?: string;
    surgery_type?: string;
    per_page?: string | number;
  };
  auth: {
    user: {
      can_edit?: boolean;
      can_generate_pdf?: boolean;
    };
  };
}

type SortField = 'patient_name' | 'uhid' | 'admission_at' | 'discharge_at' | 'consultant' | 'format' | 'status' | 'id';
type SortDirection = 'asc' | 'desc';

export default function AdmissionsDataTable({
  admissions,
  counts,
  filters = {},
  auth,
}: AdmissionsDataTableProps) {
  // Dynamic client-side controls
  const [searchQuery, setSearchQuery] = useState(filters.search || '');
  const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
  const [formatFilter, setFormatFilter] = useState(filters.format || 'all');
  const [workflowFilter, setWorkflowFilter] = useState(filters.surgery_type || 'all');
  const [sortField, setSortField] = useState<SortField>('id');
  const [sortDirection, setSortDirection] = useState<SortDirection>('desc');
  const [pageSize, setPageSize] = useState<number>(10);
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [intimationAdmission, setIntimationAdmission] = useState<any>(null);

  // Status configuration
  const statusTabs = [
    { key: 'all', label: 'All Records', count: counts.total, icon: 'bi-grid' },
    { key: 'ready_for_review', label: 'Review Pending', count: counts.ready_for_review, icon: 'bi-clock-history', badgeBg: '#fffbeb', badgeColor: '#d97706' },
    { key: 'approved', label: 'Approved', count: counts.approved, icon: 'bi-check2-circle', badgeBg: '#f0fdf4', badgeColor: '#16a34a' },
    { key: 'generated', label: 'Generated', count: counts.generated, icon: 'bi-file-earmark-check', badgeBg: '#eff6ff', badgeColor: '#2563eb' },
    { key: 'draft', label: 'Drafts', count: counts.draft, icon: 'bi-pencil-square', badgeBg: '#f8fafc', badgeColor: '#64748b' },
    { key: 'returned_for_correction', label: 'Returned', count: counts.returned_for_correction, icon: 'bi-arrow-return-left', badgeBg: '#fef2f2', badgeColor: '#dc2626' },
  ];

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

  // Toggle column sort
  const handleSort = (field: SortField) => {
    if (sortField === field) {
      setSortDirection((prev) => (prev === 'asc' ? 'desc' : 'asc'));
    } else {
      setSortField(field);
      setSortDirection('asc');
    }
    setCurrentPage(1);
  };

  // Reset all filters
  const handleResetFilters = () => {
    setSearchQuery('');
    setStatusFilter('all');
    setFormatFilter('all');
    setWorkflowFilter('all');
    setSortField('id');
    setSortDirection('desc');
    setCurrentPage(1);
  };

  const isFiltered = searchQuery.trim() !== '' || statusFilter !== 'all' || formatFilter !== 'all' || workflowFilter !== 'all';

  // 1. Dynamic filtering
  const filteredItems = useMemo(() => {
    let items = admissions.data;

    // Status filter
    if (statusFilter !== 'all') {
      items = items.filter((item) => item.status === statusFilter);
    }

    // Format filter
    if (formatFilter !== 'all') {
      items = items.filter((item) => item.format?.toLowerCase() === formatFilter.toLowerCase());
    }

    // Workflow filter
    if (workflowFilter !== 'all') {
      items = items.filter((item) => item.surgery_type?.toLowerCase() === workflowFilter.toLowerCase());
    }

    // Search query
    if (searchQuery.trim() !== '') {
      const q = searchQuery.toLowerCase().trim();
      items = items.filter((item) => {
        return (
          item.patient_name?.toLowerCase().includes(q) ||
          item.uhid?.toLowerCase().includes(q) ||
          item.ip_no?.toLowerCase().includes(q) ||
          item.consultant?.toLowerCase().includes(q) ||
          item.department?.toLowerCase().includes(q) ||
          item.ward_room_no?.toLowerCase().includes(q) ||
          item.status?.toLowerCase().includes(q) ||
          item.format?.toLowerCase().includes(q) ||
          item.surgery_type?.toLowerCase().includes(q)
        );
      });
    }

    return items;
  }, [admissions.data, statusFilter, formatFilter, workflowFilter, searchQuery]);

  // 2. Dynamic sorting
  const sortedItems = useMemo(() => {
    const list = [...filteredItems];
    list.sort((a, b) => {
      let valA: any = a[sortField];
      let valB: any = b[sortField];

      if (valA === undefined || valA === null) valA = '';
      if (valB === undefined || valB === null) valB = '';

      if (typeof valA === 'string') valA = valA.toLowerCase();
      if (typeof valB === 'string') valB = valB.toLowerCase();

      if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
      if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
      return 0;
    });
    return list;
  }, [filteredItems, sortField, sortDirection]);

  // 3. Dynamic pagination
  const totalEntries = sortedItems.length;
  const totalPages = pageSize === -1 ? 1 : Math.ceil(totalEntries / pageSize) || 1;
  const paginatedItems = useMemo(() => {
    if (pageSize === -1) return sortedItems;
    const startIdx = (currentPage - 1) * pageSize;
    return sortedItems.slice(startIdx, startIdx + pageSize);
  }, [sortedItems, currentPage, pageSize]);

  // Calculate length stay duration
  const getStayDuration = (admAt?: string, disAt?: string) => {
    if (!admAt || !disAt) return null;
    try {
      const d1 = new Date(admAt);
      const d2 = new Date(disAt);
      const diffTime = Math.abs(d2.getTime() - d1.getTime());
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      return diffDays <= 1 ? '1 Day' : `${diffDays} Days`;
    } catch {
      return null;
    }
  };

  // Export current filtered dataset to CSV
  const exportToCSV = () => {
    if (sortedItems.length === 0) return;
    const headers = [
      'ID',
      'Patient Name',
      'Age',
      'Sex',
      'UHID',
      'IP No',
      'Admission Date',
      'Discharge Date',
      'Consultant Doctor',
      'Department',
      'Format',
      'Workflow',
      'Status',
    ];
    const rows = sortedItems.map((item) => [
      item.id,
      `"${(item.patient_name || '').replace(/"/g, '""')}"`,
      `"${item.age || ''}"`,
      `"${item.sex || ''}"`,
      `"${item.uhid || ''}"`,
      `"${item.ip_no || ''}"`,
      `"${item.admission_at ? formatDate(item.admission_at) : ''}"`,
      `"${item.discharge_at ? formatDate(item.discharge_at) : ''}"`,
      `"${(item.consultant || '').replace(/"/g, '""')}"`,
      `"${(item.department || '').replace(/"/g, '""')}"`,
      `"${item.format || ''}"`,
      `"${item.surgery_type || ''}"`,
      `"${item.status || ''}"`,
    ]);
    const csvContent =
      'data:text/csv;charset=utf-8,' +
      [headers.join(','), ...rows.map((e) => e.join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `inpatient_admissions_directory_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Helper for sort icon
  const renderSortIcon = (field: SortField) => {
    if (sortField !== field) {
      return <i className="bi bi-arrow-down-up text-muted ms-1 opacity-50" style={{ fontSize: '11px' }}></i>;
    }
    return sortDirection === 'asc' ? (
      <i className="bi bi-sort-alpha-down text-primary ms-1 fw-bold" style={{ fontSize: '13px' }}></i>
    ) : (
      <i className="bi bi-sort-alpha-down-alt text-primary ms-1 fw-bold" style={{ fontSize: '13px' }}></i>
    );
  };

  // Page range numbers
  const pageNumbers = useMemo(() => {
    if (totalPages <= 7) {
      return Array.from({ length: totalPages }, (_, i) => i + 1);
    }
    if (currentPage <= 4) {
      return [1, 2, 3, 4, 5, '...', totalPages];
    }
    if (currentPage >= totalPages - 3) {
      return [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
    }
    return [1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages];
  }, [currentPage, totalPages]);

  return (
    <div className="card shadow-sm border-0" style={{ borderRadius: '20px' }}>
      {/* 1. Status Filter Pills Bar (Dynamic Datatable Category Switcher) */}
      <div className="card-header bg-white pt-3 pb-2 px-3 px-md-4 border-0">
        <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
          <div className="d-flex align-items-center gap-2">
            <span className="p-2 rounded-circle bg-primary-subtle text-primary">
              <i className="bi bi-hospital fs-6"></i>
            </span>
            <div>
              <h5 className="card-title mb-0 fw-bold text-dark" style={{ fontSize: '16px', letterSpacing: '-0.01em' }}>
                Inpatient Admissions Directory
              </h5>
              <div className="text-muted small" style={{ fontSize: '12px' }}>
                Interactive live clinical dossier tracker &bull; {totalEntries} {totalEntries === 1 ? 'patient' : 'patients'} matching
              </div>
            </div>
          </div>

          <div className="d-flex align-items-center gap-2">
            <button
              type="button"
              onClick={exportToCSV}
              className="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 shadow-none"
              style={{ borderRadius: '8px', fontSize: '12.5px' }}
              title="Export current table to CSV spreadsheet"
            >
              <i className="bi bi-download"></i>
              <span>Export CSV</span>
            </button>
            {isFiltered && (
              <button
                type="button"
                onClick={handleResetFilters}
                className="btn btn-sm btn-light border text-secondary d-flex align-items-center gap-1 shadow-none"
                style={{ borderRadius: '8px', fontSize: '12.5px' }}
                title="Reset all filters and search"
              >
                <i className="bi bi-x-circle"></i>
                <span>Reset Filters</span>
              </button>
            )}
          </div>
        </div>

        {/* Dynamic Status Filter Tabs */}
        <div className="d-flex flex-wrap gap-2 pt-2 border-top border-light-subtle">
          {statusTabs.map((tab) => {
            const isActive = statusFilter === tab.key;
            return (
              <button
                key={tab.key}
                type="button"
                onClick={() => {
                  setStatusFilter(tab.key);
                  setCurrentPage(1);
                }}
                className={`btn btn-sm d-flex align-items-center gap-2 transition-all ${
                  isActive
                    ? 'btn-primary text-white shadow-sm'
                    : 'btn-light border-0 text-secondary bg-light'
                }`}
                style={{
                  borderRadius: '20px',
                  padding: '5px 12px',
                  fontSize: '12.5px',
                  fontWeight: isActive ? 600 : 500,
                }}
              >
                <i className={`bi ${tab.icon}`}></i>
                <span>{tab.label}</span>
                <span
                  className="badge rounded-pill"
                  style={{
                    backgroundColor: isActive ? 'rgba(255, 255, 255, 0.28)' : (tab.badgeBg || '#e2e8f0'),
                    color: isActive ? '#fff' : (tab.badgeColor || '#475569'),
                    fontSize: '11px',
                    fontWeight: 600,
                  }}
                >
                  {tab.count}
                </span>
              </button>
            );
          })}
        </div>
      </div>

      {/* 2. Dynamic Controls Header (Search, Page Size, Quick Dropdowns) */}
      <div className="card-body bg-light-subtle p-3 border-top border-bottom border-light-subtle">
        <div className="row g-2 align-items-center justify-content-between">
          {/* Entries per page & Workflow / Format filters */}
          <div className="col-12 col-lg-7 d-flex flex-wrap align-items-center gap-2">
            <div className="d-flex align-items-center gap-1">
              <span className="text-muted small" style={{ fontSize: '12.5px' }}>Show</span>
              <select
                className="form-select form-select-sm shadow-none"
                value={pageSize}
                onChange={(e) => {
                  setPageSize(Number(e.target.value));
                  setCurrentPage(1);
                }}
                style={{ width: '80px', borderRadius: '8px', fontSize: '12.5px' }}
              >
                <option value={10}>10</option>
                <option value={25}>25</option>
                <option value={50}>50</option>
                <option value={100}>100</option>
                <option value={-1}>All</option>
              </select>
              <span className="text-muted small" style={{ fontSize: '12.5px' }}>entries</span>
            </div>

            <div className="vr d-none d-sm-block my-1 text-muted opacity-25"></div>

            {/* Workflow Filter */}
            <select
              className="form-select form-select-sm shadow-none"
              value={workflowFilter}
              onChange={(e) => {
                setWorkflowFilter(e.target.value);
                setCurrentPage(1);
              }}
              style={{ width: '135px', borderRadius: '8px', fontSize: '12.5px' }}
            >
              <option value="all">All Workflows</option>
              <option value="delivery">Delivery</option>
              <option value="other">Other Surgery</option>
              <option value="medical">Medical Case</option>
            </select>

            {/* Format Filter */}
            <select
              className="form-select form-select-sm shadow-none"
              value={formatFilter}
              onChange={(e) => {
                setFormatFilter(e.target.value);
                setCurrentPage(1);
              }}
              style={{ width: '130px', borderRadius: '8px', fontSize: '12.5px' }}
            >
              <option value="all">All Formats</option>
              <option value="general">General</option>
              <option value="cmchistn">CMCHISTN</option>
              <option value="employee">Employee</option>
              <option value="private">Private</option>
            </select>
          </div>

          {/* Dynamic Live Search Input */}
          <div className="col-12 col-lg-4">
            <div className="position-relative">
              <i
                className="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted"
                style={{ fontSize: '12px' }}
              ></i>
              <input
                type="text"
                className="form-control form-control-sm ps-4 pe-4 shadow-none"
                placeholder="Live search patient, UHID, IP..."
                value={searchQuery}
                onChange={(e) => {
                  setSearchQuery(e.target.value);
                  setCurrentPage(1);
                }}
                style={{ borderRadius: '8px', fontSize: '12.5px' }}
              />
              {searchQuery && (
                <button
                  type="button"
                  onClick={() => setSearchQuery('')}
                  className="btn btn-sm btn-link position-absolute top-50 end-0 translate-middle-y me-1 p-0 text-muted"
                  style={{ textDecoration: 'none', lineHeight: 1 }}
                  title="Clear search"
                >
                  <i className="bi bi-x-circle-fill"></i>
                </button>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* 3. The Dynamic Interactive DataTable */}
      <div className="table-responsive">
        <table className="table table-hover align-middle mb-0" style={{ fontSize: '13.5px' }}>
          <thead className="table-light text-muted small text-uppercase" style={{ letterSpacing: '0.04em', fontSize: '11px' }}>
            <tr>
              <th
                onClick={() => handleSort('patient_name')}
                style={{ cursor: 'pointer', userSelect: 'none', width: '28%' }}
                className={sortField === 'patient_name' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between">
                  <span>Patient Details</span>
                  {renderSortIcon('patient_name')}
                </div>
              </th>

              <th
                onClick={() => handleSort('admission_at')}
                style={{ cursor: 'pointer', userSelect: 'none', width: '20%' }}
                className={sortField === 'admission_at' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between">
                  <span>Admission / Stay</span>
                  {renderSortIcon('admission_at')}
                </div>
              </th>

              <th
                onClick={() => handleSort('consultant')}
                style={{ cursor: 'pointer', userSelect: 'none', width: '20%' }}
                className={sortField === 'consultant' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between">
                  <span>Doctor &amp; Department</span>
                  {renderSortIcon('consultant')}
                </div>
              </th>

              <th
                onClick={() => handleSort('format')}
                style={{ cursor: 'pointer', userSelect: 'none', width: '14%' }}
                className={sortField === 'format' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between">
                  <span>Format &amp; Workflow</span>
                  {renderSortIcon('format')}
                </div>
              </th>

              <th
                onClick={() => handleSort('status')}
                style={{ cursor: 'pointer', userSelect: 'none', width: '10%' }}
                className={sortField === 'status' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between">
                  <span>Status</span>
                  {renderSortIcon('status')}
                </div>
              </th>

              <th className="text-end pe-3" style={{ width: '8%', userSelect: 'none' }}>
                Actions
              </th>
            </tr>
          </thead>

          <tbody>
            {paginatedItems.length === 0 ? (
              <tr>
                <td colSpan={6} className="text-center py-5 text-muted">
                  <div className="py-4">
                    <i className="bi bi-search-heart text-secondary fs-1 d-block mb-3 opacity-50"></i>
                    <h6 className="fw-semibold text-dark mb-1">No matching patient records found</h6>
                    <p className="text-muted small mb-3">
                      {isFiltered
                        ? 'Try adjusting or resetting your filter criteria or search terms.'
                        : 'No inpatient admissions have been recorded yet.'}
                    </p>
                    {isFiltered && (
                      <button
                        type="button"
                        onClick={handleResetFilters}
                        className="btn btn-sm btn-outline-primary"
                        style={{ borderRadius: '8px' }}
                      >
                        <i className="bi bi-arrow-counterclockwise me-1"></i> Reset All Filters
                      </button>
                    )}
                  </div>
                </td>
              </tr>
            ) : (
              paginatedItems.map((item) => {
                const isFemale = item.sex?.toLowerCase() === 'female';
                const avatarBg = isFemale ? '#fce7f3' : '#e0f2fe';
                const avatarColor = isFemale ? '#db2777' : '#0284c7';
                const initial = item.patient_name ? item.patient_name.trim().charAt(0).toUpperCase() : 'P';
                const stayDuration = getStayDuration(item.admission_at, item.discharge_at);

                return (
                  <tr key={item.id} className="transition-all">
                    {/* Patient Details */}
                    <td>
                      <div className="d-flex align-items-center gap-2">
                        <div
                          className="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                          style={{
                            width: '38px',
                            height: '38px',
                            backgroundColor: avatarBg,
                            color: avatarColor,
                            fontSize: '14px',
                          }}
                        >
                          {initial}
                        </div>
                        <div>
                          <div className="fw-bold text-dark text-truncate" style={{ maxWidth: '240px' }}>
                            {item.patient_name}
                          </div>
                          <div className="d-flex flex-wrap align-items-center gap-1 mt-0.5">
                            {item.age && (
                              <span className="text-muted small" style={{ fontSize: '11px' }}>
                                {item.age} Yrs &bull; {item.sex || 'Patient'}
                              </span>
                            )}
                            <span className="badge bg-light text-dark border font-monospace" style={{ fontSize: '9.5px', padding: '2px 4px' }}>
                              UHID: {item.uhid}
                            </span>
                            <span className="badge bg-light text-primary border font-monospace" style={{ fontSize: '9.5px', padding: '2px 4px' }}>
                              IP: {item.ip_no}
                            </span>
                          </div>
                        </div>
                      </div>
                    </td>

                    {/* Admission / Stay */}
                    <td>
                      <div className="d-flex align-items-center gap-1 text-dark fw-medium" style={{ fontSize: '12.5px' }}>
                        <i className="bi bi-box-arrow-in-right text-success" style={{ fontSize: '12px' }}></i>
                        <span>{item.admission_at ? formatDate(item.admission_at) : '—'}</span>
                      </div>
                      <div className="d-flex align-items-center gap-1 text-muted small mt-0.5" style={{ fontSize: '11.5px' }}>
                        <i className="bi bi-box-arrow-right text-muted" style={{ fontSize: '12px' }}></i>
                        <span>to {item.discharge_at ? formatDate(item.discharge_at) : '—'}</span>
                        {stayDuration && (
                          <span className="badge bg-light text-secondary border ms-1" style={{ fontSize: '9.5px' }}>
                            {stayDuration}
                          </span>
                        )}
                      </div>
                    </td>

                    {/* Consultant & Dept */}
                    <td>
                      <div className="text-dark fw-medium text-truncate" style={{ maxWidth: '180px', fontSize: '12.5px' }}>
                        <i className="bi bi-person-badge text-primary me-1" style={{ fontSize: '12px' }}></i>
                        {item.consultant || '—'}
                      </div>
                      <div className="text-muted small text-truncate mt-0.5" style={{ maxWidth: '180px', fontSize: '11.5px' }}>
                        {item.department ? `${item.department}` : ''}
                        {item.ward_room_no ? ` &bull; ${item.ward_room_no}` : ''}
                      </div>
                    </td>

                    {/* Format & Workflow */}
                    <td>
                      <div className="d-flex flex-wrap align-items-center gap-1">
                        <span className="badge bg-light text-dark border text-uppercase" style={{ fontSize: '9.5px' }}>
                          {item.format}
                        </span>
                        <span
                          className={`badge ${
                            item.surgery_type === 'delivery'
                              ? 'bg-danger-subtle text-danger border border-danger-subtle'
                              : item.surgery_type === 'medical'
                              ? 'bg-success-subtle text-success border border-success-subtle'
                              : 'bg-primary-subtle text-primary border border-primary-subtle'
                          }`}
                          style={{ fontSize: '9.5px' }}
                        >
                          {item.surgery_type === 'delivery' ? (
                            <>
                              <i className="bi bi-heart-pulse-fill me-1"></i>
                              Delivery ({item.newborns?.length || 0})
                            </>
                          ) : item.surgery_type === 'medical' ? (
                            <>
                              <i className="bi bi-capsule me-1"></i>Medical
                            </>
                          ) : (
                            <>
                              <i className="bi bi-scissors me-1"></i>Surgical
                            </>
                          )}
                        </span>
                      </div>
                    </td>

                    {/* Status */}
                    <td>
                      <span className={`badge ${getStatusBadge(item.status)} text-uppercase`} style={{ fontSize: '10px' }}>
                        {item.status.replace(/_/g, ' ')}
                      </span>
                    </td>

                    {/* Actions */}
                    <td className="text-end pe-3">
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
                          <Link href={`/admissions/${item.id}/edit`} prefetch className="btn btn-outline-secondary" title="Edit Clinical Workflow">
                            <i className="bi bi-pencil"></i>
                          </Link>
                        )}
                        {(item.status === 'approved' || item.status === 'generated') && (
                          <ActionDropdown
                            toggleClassName="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
                            toggleTitle="Download Separate PDFs"
                            fontSize="12px"
                            items={buildPdfDownloadMenuItems(item.id, undefined, () => setIntimationAdmission(item))}
                            primaryAction={
                              <a
                                href={`/admissions/${item.id}/documents/pdf/discharge-summary-a4`}
                                target="_blank"
                                rel="noreferrer"
                                className="btn btn-outline-primary"
                                title="Download Digital Discharge Summary PDF"
                              >
                                <i className="bi bi-file-earmark-pdf"></i>
                              </a>
                            }
                          />
                        )}
                      </div>
                    </td>
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>

      {/* 4. Dynamic DataTable Pagination Footer */}
      <div className="card-footer bg-white p-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div className="text-muted small" style={{ fontSize: '12.5px' }}>
          {totalEntries === 0 ? (
            'Showing 0 entries'
          ) : (
            <>
              Showing <span className="fw-semibold text-dark">{pageSize === -1 ? 1 : (currentPage - 1) * pageSize + 1}</span> to{' '}
              <span className="fw-semibold text-dark">
                {pageSize === -1 ? totalEntries : Math.min(currentPage * pageSize, totalEntries)}
              </span>{' '}
              of <span className="fw-semibold text-dark">{totalEntries}</span> entries
              {isFiltered && <span className="ms-1 text-muted">(filtered from {admissions.data.length} records)</span>}
            </>
          )}
        </div>

        {totalPages > 1 && pageSize !== -1 && (
          <nav aria-label="Admissions directory pagination">
            <ul className="pagination pagination-sm mb-0 gap-1">
              {/* First button */}
              <li className={`page-item ${currentPage === 1 ? 'disabled' : ''}`}>
                <button
                  type="button"
                  className="page-link shadow-none border-0"
                  onClick={() => setCurrentPage(1)}
                  disabled={currentPage === 1}
                  style={{ borderRadius: '6px' }}
                  title="First Page"
                >
                  <i className="bi bi-chevron-double-left"></i>
                </button>
              </li>

              {/* Prev button */}
              <li className={`page-item ${currentPage === 1 ? 'disabled' : ''}`}>
                <button
                  type="button"
                  className="page-link shadow-none border-0"
                  onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                  disabled={currentPage === 1}
                  style={{ borderRadius: '6px' }}
                  title="Previous Page"
                >
                  <i className="bi bi-chevron-left"></i>
                </button>
              </li>

              {/* Page numbers */}
              {pageNumbers.map((page, idx) => {
                if (page === '...') {
                  return (
                    <li key={`ellipsis-${idx}`} className="page-item disabled">
                      <span className="page-link border-0 text-muted">...</span>
                    </li>
                  );
                }
                const isCurrent = currentPage === page;
                return (
                  <li key={`page-${page}`} className={`page-item ${isCurrent ? 'active' : ''}`}>
                    <button
                      type="button"
                      className={`page-link shadow-none border-0 ${
                        isCurrent ? 'bg-primary text-white fw-bold' : 'text-dark'
                      }`}
                      onClick={() => setCurrentPage(Number(page))}
                      style={{ borderRadius: '6px', minWidth: '30px', textAlign: 'center' }}
                    >
                      {page}
                    </button>
                  </li>
                );
              })}

              {/* Next button */}
              <li className={`page-item ${currentPage === totalPages ? 'disabled' : ''}`}>
                <button
                  type="button"
                  className="page-link shadow-none border-0"
                  onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                  disabled={currentPage === totalPages}
                  style={{ borderRadius: '6px' }}
                  title="Next Page"
                >
                  <i className="bi bi-chevron-right"></i>
                </button>
              </li>

              {/* Last button */}
              <li className={`page-item ${currentPage === totalPages ? 'disabled' : ''}`}>
                <button
                  type="button"
                  className="page-link shadow-none border-0"
                  onClick={() => setCurrentPage(totalPages)}
                  disabled={currentPage === totalPages}
                  style={{ borderRadius: '6px' }}
                  title="Last Page"
                >
                  <i className="bi bi-chevron-double-right"></i>
                </button>
              </li>
            </ul>
          </nav>
        )}
      </div>

      {/* Discharge Intimation WhatsApp Copy Modal */}
      {intimationAdmission && (
        <DischargeIntimationModal
          show={true}
          onClose={() => setIntimationAdmission(null)}
          admission={intimationAdmission}
        />
      )}
    </div>
  );
}
