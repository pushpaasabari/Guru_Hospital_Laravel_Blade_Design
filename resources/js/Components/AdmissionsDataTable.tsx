import React, { useState, useMemo } from 'react';
import { Link } from '@inertiajs/react';
import ActionDropdown, {
  buildDocumentPreviewMenuItems,
  buildEditStageMenuItems,
  buildPdfDownloadMenuItems,
} from './ActionDropdown';
import DischargeIntimationModal from './DischargeIntimationModal';
import Pagination from './Pagination';
import { formatDate, parseWallClockParts } from '../utils/dateFormatter';
import { resolveMrdNo } from '../utils/inputNormalizers';

export interface AdmissionItem {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  mrd_no?: string;
  age?: string;
  sex?: string;
  format: string;
  surgery_type: string;
  diagnosis?: string;
  procedure_name?: string;
  procedure_done?: string;
  status: string;
  admission_at: string;
  procedure_at?: string;
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
    superseded?: number;
    in_admission?: number;
  };
  filters?: {
    search?: string;
    status?: string;
    format?: string;
    surgery_type?: string;
    consultant?: string;
    date_from?: string;
    date_to?: string;
    per_page?: string | number;
  };
  auth: {
    user: {
      can_edit?: boolean;
      can_generate_pdf?: boolean;
    };
  };
  mode?: 'directory' | 'archive';
  title?: string;
  subtitle?: string;
}

type SortField = 'patient_name' | 'uhid' | 'admission_at' | 'discharge_at' | 'consultant' | 'format' | 'status' | 'id';
type SortDirection = 'asc' | 'desc';

function extractIsoCalendarDate(raw?: string | null): string {
  const parts = parseWallClockParts(raw);
  if (!parts) return '';
  return `${parts.year}-${String(parts.month).padStart(2, '0')}-${String(parts.day).padStart(2, '0')}`;
}

export default function AdmissionsDataTable({
  admissions,
  counts,
  filters = {},
  auth,
  mode = 'directory',
  title = 'Inpatient Admissions Directory',
  subtitle = 'Interactive live clinical dossier tracker',
}: AdmissionsDataTableProps) {
  const isArchive = mode === 'archive';

  // Dynamic client-side controls
  const [searchQuery, setSearchQuery] = useState(filters.search || '');
  const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
  const [formatFilter, setFormatFilter] = useState(filters.format || 'all');
  const [workflowFilter, setWorkflowFilter] = useState(filters.surgery_type || 'all');
  const [consultantFilter, setConsultantFilter] = useState(filters.consultant || 'all');
  const [dateFrom, setDateFrom] = useState(filters.date_from || '');
  const [dateTo, setDateTo] = useState(filters.date_to || '');
  const [showDateFilters, setShowDateFilters] = useState<boolean>(Boolean(filters.date_from || filters.date_to));
  const [sortField, setSortField] = useState<SortField>('id');
  const [sortDirection, setSortDirection] = useState<SortDirection>('desc');
  const [pageSize, setPageSize] = useState<number>(isArchive ? 25 : 10);
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [intimationAdmission, setIntimationAdmission] = useState<any>(null);

  const inAdmissionCount =
    counts.in_admission ?? admissions.data.filter((item) => !item.discharge_at || String(item.discharge_at).trim() === '').length;

  const consultantOptions = useMemo(() => {
    const names = new Set<string>();
    for (const item of admissions.data) {
      const c = item.consultant?.trim();
      if (c) names.add(c);
    }
    return Array.from(names).sort((a, b) => a.localeCompare(b));
  }, [admissions.data]);

  // Status configuration
  const statusTabs = [
    { key: 'all', label: 'All Records', count: counts.total, icon: 'bi-grid' },
    { key: 'ready_for_review', label: 'Review Pending', count: counts.ready_for_review, icon: 'bi-clock-history', badgeBg: '#fffbeb', badgeColor: '#d97706' },
    { key: 'approved', label: 'Approved', count: counts.approved, icon: 'bi-check2-circle', badgeBg: '#f0fdf4', badgeColor: '#16a34a' },
    { key: 'generated', label: 'Generated', count: counts.generated, icon: 'bi-file-earmark-check', badgeBg: '#eff6ff', badgeColor: '#2563eb' },
    { key: 'draft', label: 'Drafts', count: counts.draft, icon: 'bi-pencil-square', badgeBg: '#f8fafc', badgeColor: '#64748b' },
    { key: 'returned_for_correction', label: 'Returned', count: counts.returned_for_correction, icon: 'bi-arrow-return-left', badgeBg: '#fef2f2', badgeColor: '#dc2626' },
    { key: 'in_admission', label: 'In Admission', count: inAdmissionCount, icon: 'bi-person-check', badgeBg: '#ecfdf5', badgeColor: '#059669' },
    ...((counts.superseded ?? 0) > 0 || statusFilter === 'superseded'
      ? [{ key: 'superseded', label: 'Superseded', count: counts.superseded ?? 0, icon: 'bi-archive', badgeBg: '#f1f5f9', badgeColor: '#475569' }]
      : []),
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
    setConsultantFilter('all');
    setDateFrom('');
    setDateTo('');
    setSortField('id');
    setSortDirection('desc');
    setCurrentPage(1);
  };

  const isFiltered =
    searchQuery.trim() !== '' ||
    statusFilter !== 'all' ||
    formatFilter !== 'all' ||
    workflowFilter !== 'all' ||
    consultantFilter !== 'all' ||
    dateFrom !== '' ||
    dateTo !== '';

  // 1. Dynamic filtering
  const filteredItems = useMemo(() => {
    let items = admissions.data;

    // Status / admission state filter
    if (statusFilter === 'in_admission') {
      items = items.filter((item) => !item.discharge_at || String(item.discharge_at).trim() === '');
    } else if (statusFilter !== 'all') {
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

    // Consultant filter
    if (consultantFilter !== 'all') {
      const targetDoc = consultantFilter.toLowerCase();
      items = items.filter((item) => (item.consultant || '').trim().toLowerCase() === targetDoc);
    }

    // Admission Date Range filter (wall-clock safe)
    if (dateFrom) {
      items = items.filter((item) => {
        const d = extractIsoCalendarDate(item.admission_at);
        return d !== '' && d >= dateFrom;
      });
    }
    if (dateTo) {
      items = items.filter((item) => {
        const d = extractIsoCalendarDate(item.admission_at);
        return d !== '' && d <= dateTo;
      });
    }

    // Search query
    if (searchQuery.trim() !== '') {
      const q = searchQuery.toLowerCase().trim();
      items = items.filter((item) => {
        const mrd = resolveMrdNo(item.mrd_no, item.ip_no);
        return (
          item.patient_name?.toLowerCase().includes(q) ||
          item.uhid?.toLowerCase().includes(q) ||
          item.ip_no?.toLowerCase().includes(q) ||
          mrd?.toLowerCase().includes(q) ||
          item.consultant?.toLowerCase().includes(q) ||
          item.department?.toLowerCase().includes(q) ||
          item.ward_room_no?.toLowerCase().includes(q) ||
          item.diagnosis?.toLowerCase().includes(q) ||
          item.procedure_done?.toLowerCase().includes(q) ||
          item.procedure_name?.toLowerCase().includes(q) ||
          item.status?.toLowerCase().includes(q) ||
          item.format?.toLowerCase().includes(q) ||
          item.surgery_type?.toLowerCase().includes(q)
        );
      });
    }

    return items;
  }, [admissions.data, statusFilter, formatFilter, workflowFilter, consultantFilter, dateFrom, dateTo, searchQuery]);

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

  // Calculate length of stay duration without browser timezone drift
  const getStayDuration = (admAt?: string, disAt?: string) => {
    const p1 = parseWallClockParts(admAt);
    const p2 = parseWallClockParts(disAt);
    if (!p1 || !p2) return null;
    const utc1 = Date.UTC(p1.year, p1.month - 1, p1.day, p1.hours24 ?? 0, p1.minutes ?? 0);
    const utc2 = Date.UTC(p2.year, p2.month - 1, p2.day, p2.hours24 ?? 0, p2.minutes ?? 0);
    const diffDays = Math.ceil(Math.abs(utc2 - utc1) / (1000 * 60 * 60 * 24));
    return diffDays <= 1 ? '1 Day' : `${diffDays} Days`;
  };

  // Export current filtered dataset to CSV
  const exportToCSV = () => {
    if (sortedItems.length === 0) return;
    const csvCell = (val?: string | number | null) => `"${String(val ?? '').replace(/"/g, '""')}"`;
    const headers = [
      'ID',
      'Patient Name',
      'Age',
      'Sex',
      'UHID',
      'IP No',
      'MRD No',
      'Admission Date',
      'Discharge Date',
      'Stay',
      'Consultant Doctor',
      'Department',
      'Diagnosis',
      'Procedure',
      'Format',
      'Workflow',
      'Status',
    ];
    const rows = sortedItems.map((item) => [
      item.id,
      csvCell(item.patient_name),
      csvCell(item.age),
      csvCell(item.sex),
      csvCell(item.uhid),
      csvCell(item.ip_no),
      csvCell(resolveMrdNo(item.mrd_no, item.ip_no)),
      csvCell(item.admission_at ? formatDate(item.admission_at) : ''),
      csvCell(item.discharge_at ? formatDate(item.discharge_at) : ''),
      csvCell(getStayDuration(item.admission_at, item.discharge_at) || ''),
      csvCell(item.consultant),
      csvCell(item.department),
      csvCell(item.diagnosis),
      csvCell(item.procedure_done || item.procedure_name),
      csvCell(item.format),
      csvCell(item.surgery_type),
      csvCell(item.status),
    ]);
    const csvContent =
      'data:text/csv;charset=utf-8,' +
      [headers.join(','), ...rows.map((e) => e.join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `inpatient_admissions_${mode}_${new Date().toISOString().slice(0, 10)}.csv`);
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
                {title}
              </h5>
              <div className="text-muted small" style={{ fontSize: '12px' }}>
                {subtitle} &bull; {totalEntries} {totalEntries === 1 ? 'patient' : 'patients'} matching
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
        <div className="d-flex flex-wrap fg-mobile-scroll-tabs gap-2 pt-2 border-top border-light-subtle">
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
          {/* Entries per page & Workflow / Format / Doctor filters */}
          <div className="col-12 col-xl-8 d-flex flex-wrap align-items-center gap-2">
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
              className="form-select form-select-sm shadow-none fg-filter-select"
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
              className="form-select form-select-sm shadow-none fg-filter-select"
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

            {/* Doctor / Consultant Filter */}
            {consultantOptions.length > 0 && (
              <select
                className="form-select form-select-sm shadow-none fg-filter-select"
                value={consultantFilter}
                onChange={(e) => {
                  setConsultantFilter(e.target.value);
                  setCurrentPage(1);
                }}
                style={{ width: '155px', borderRadius: '8px', fontSize: '12.5px' }}
                aria-label="Filter by Consultant Doctor"
              >
                <option value="all">All Doctors</option>
                {consultantOptions.map((doc) => (
                  <option key={doc} value={doc}>
                    {doc}
                  </option>
                ))}
              </select>
            )}

            {/* Toggle Date Range Filter */}
            <button
              type="button"
              onClick={() => setShowDateFilters((prev) => !prev)}
              className={`btn btn-sm d-inline-flex align-items-center gap-1 shadow-none ${
                showDateFilters || dateFrom || dateTo
                  ? 'btn-outline-primary'
                  : 'btn-outline-secondary'
              }`}
              style={{ borderRadius: '8px', fontSize: '12.5px', padding: '5px 11px' }}
              title="Toggle Admission Date Range Filter"
            >
              <i className="bi bi-calendar-range"></i>
              <span>Dates</span>
              {(dateFrom || dateTo) && (
                <span className="badge bg-primary rounded-pill" style={{ fontSize: '9.5px' }}>
                  1
                </span>
              )}
            </button>
          </div>

          {/* Dynamic Live Search Input */}
          <div className="col-12 col-xl-4">
            <div className="fg-search-input-wrap">
              <i className="bi bi-search fg-search-icon"></i>
              <input
                type="text"
                className="form-control form-control-sm shadow-none"
                placeholder="Live search patient, UHID, IP, doctor, diagnosis..."
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

        {/* Collapsible Admission Date Range Bar */}
        {showDateFilters && (
          <div className="row g-2 align-items-end pt-2 mt-2 border-top border-light-subtle">
            <div className="col-6 col-md-3">
              <label className="form-label mb-1 small text-muted" style={{ fontSize: '11px' }}>
                Admission From
              </label>
              <input
                type="date"
                className="form-control form-control-sm shadow-none"
                value={dateFrom}
                onChange={(e) => {
                  setDateFrom(e.target.value);
                  setCurrentPage(1);
                }}
                style={{ borderRadius: '8px' }}
              />
            </div>
            <div className="col-6 col-md-3">
              <label className="form-label mb-1 small text-muted" style={{ fontSize: '11px' }}>
                Admission To
              </label>
              <input
                type="date"
                className="form-control form-control-sm shadow-none"
                value={dateTo}
                onChange={(e) => {
                  setDateTo(e.target.value);
                  setCurrentPage(1);
                }}
                style={{ borderRadius: '8px' }}
              />
            </div>
            <div className="col-12 col-md-6 d-flex flex-wrap gap-2">
              <button
                type="button"
                className="btn btn-sm btn-outline-secondary"
                style={{ borderRadius: '8px', fontSize: '12px' }}
                onClick={() => {
                  const today = extractIsoCalendarDate(new Date().toISOString()) || new Date().toISOString().slice(0, 10);
                  setDateFrom(today);
                  setDateTo(today);
                  setCurrentPage(1);
                }}
              >
                Today
              </button>
              <button
                type="button"
                className="btn btn-sm btn-outline-secondary"
                style={{ borderRadius: '8px', fontSize: '12px' }}
                onClick={() => {
                  const end = new Date();
                  const start = new Date();
                  start.setDate(end.getDate() - 6);
                  setDateFrom(start.toISOString().slice(0, 10));
                  setDateTo(end.toISOString().slice(0, 10));
                  setCurrentPage(1);
                }}
              >
                Last 7 Days
              </button>
              {(dateFrom || dateTo) && (
                <button
                  type="button"
                  className="btn btn-sm btn-outline-danger"
                  style={{ borderRadius: '8px', fontSize: '12px' }}
                  onClick={() => {
                    setDateFrom('');
                    setDateTo('');
                    setCurrentPage(1);
                  }}
                >
                  Clear Dates
                </button>
              )}
            </div>
          </div>
        )}
      </div>

      {/* 3A. Mobile Card View (< 768px) */}
      <div className="d-md-none p-2 bg-light-subtle">
        {paginatedItems.length === 0 ? (
          <div className="text-center py-4 text-muted">
            <i className="bi bi-search-heart text-secondary fs-1 d-block mb-2 opacity-50"></i>
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
        ) : (
          paginatedItems.map((item) => {
            const isFemale = item.sex?.toLowerCase() === 'female';
            const avatarBg = isFemale ? '#fce7f3' : '#e0f2fe';
            const avatarColor = isFemale ? '#db2777' : '#0284c7';
            const initial = item.patient_name ? item.patient_name.trim().charAt(0).toUpperCase() : 'P';
            const stayDuration = getStayDuration(item.admission_at, item.discharge_at);

            return (
              <div key={`mob-${item.id}`} className="card border shadow-none mb-2" style={{ borderRadius: '14px' }}>
                <div className="card-body p-3">
                  <div className="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div className="d-flex align-items-center gap-2 min-w-0">
                      <div
                        className="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                        style={{
                          width: '36px',
                          height: '36px',
                          backgroundColor: avatarBg,
                          color: avatarColor,
                          fontSize: '13.5px',
                        }}
                      >
                        {initial}
                      </div>
                      <div className="min-w-0">
                        <div className="fw-bold text-dark text-truncate" style={{ fontSize: '13.5px' }}>
                          {item.patient_name}
                        </div>
                        <div className="text-muted" style={{ fontSize: '11px' }}>
                          {item.age ? `${item.age} Yrs • ` : ''}{item.sex || 'Patient'}
                        </div>
                      </div>
                    </div>
                    <span className={`badge ${getStatusBadge(item.status)} text-uppercase flex-shrink-0`} style={{ fontSize: '9.5px' }}>
                      {item.status.replace(/_/g, ' ')}
                    </span>
                  </div>

                  <div className="d-flex flex-wrap gap-1 mb-2">
                    <span className="badge bg-light text-dark border font-monospace" style={{ fontSize: '10px' }}>
                      UHID: {item.uhid}
                    </span>
                    <span className="badge bg-light text-primary border font-monospace" style={{ fontSize: '10px' }}>
                      IP: {item.ip_no}
                    </span>
                    <span className="badge bg-light text-dark border text-uppercase" style={{ fontSize: '10px' }}>
                      {item.format}
                    </span>
                    <span
                      className={`badge ${
                        item.surgery_type === 'delivery'
                          ? 'bg-success-subtle text-success border border-success-subtle'
                          : item.surgery_type === 'medical'
                          ? 'bg-info-subtle text-info border border-info-subtle'
                          : 'bg-primary-subtle text-primary border border-primary-subtle'
                      }`}
                      style={{ fontSize: '10px' }}
                    >
                      {item.surgery_type === 'delivery'
                        ? `Delivery (${item.newborns?.length || 0})`
                        : item.surgery_type === 'medical'
                        ? 'Medical'
                        : 'Surgical'}
                    </span>
                  </div>

                  <div className="small text-muted mb-2" style={{ fontSize: '12px', lineHeight: 1.45 }}>
                    <div className="text-truncate text-dark">
                      <i className="bi bi-person-badge text-primary me-1"></i>
                      {item.consultant || '—'}
                      {item.department ? ` • ${item.department}` : ''}
                    </div>
                    <div className="d-flex flex-wrap align-items-center gap-1 mt-0.5">
                      <span>DOA: {item.admission_at ? formatDate(item.admission_at) : '—'}</span>
                      <span>→</span>
                      <span>DOD: {item.discharge_at ? formatDate(item.discharge_at) : '—'}</span>
                      {stayDuration && (
                        <span className="badge bg-light text-secondary border ms-1" style={{ fontSize: '9.5px' }}>
                          {stayDuration}
                        </span>
                      )}
                    </div>
                  </div>

                  <div className="d-flex flex-wrap gap-2 pt-2 border-top border-light-subtle">
                    <Link
                      href={`/admissions/${item.id}/documents/preview`}
                      prefetch
                      className="btn btn-sm btn-outline-secondary flex-grow-1 fw-semibold"
                    >
                      <i className="bi bi-eye me-1"></i> {isArchive ? 'Preview' : 'Review'}
                    </Link>
                    {auth.user?.can_edit && (isArchive || item.status === 'draft' || item.status === 'returned_for_correction') && (
                      <Link
                        href={`/admissions/${item.id}/edit`}
                        prefetch
                        className="btn btn-sm btn-outline-dark fw-semibold"
                      >
                        <i className="bi bi-pencil me-1"></i> Edit
                      </Link>
                    )}
                    {isArchive && (
                      <Link
                        href={`/documents/${item.id}/history`}
                        prefetch
                        className="btn btn-sm btn-outline-secondary fw-semibold"
                      >
                        <i className="bi bi-clock-history me-1"></i> History
                      </Link>
                    )}
                    {(item.status === 'approved' || item.status === 'generated') && (
                      <a
                        href={`/admissions/${item.id}/documents/pdf/discharge-summary`}
                        target="_blank"
                        rel="noreferrer"
                        className="btn btn-sm btn-outline-primary fw-semibold"
                      >
                        <i className="bi bi-file-earmark-pdf me-1"></i> PDF
                      </a>
                    )}
                  </div>
                </div>
              </div>
            );
          })
        )}
      </div>

      {/* 3B. The Dynamic Interactive DataTable (>= 768px) */}
      <div className="table-responsive d-none d-md-block">
        <table className="table table-hover align-middle mb-0 fg-fit-table" style={{ fontSize: '13px' }}>
          <thead className="table-light text-muted small text-uppercase" style={{ letterSpacing: '0.04em', fontSize: '11px' }}>
            <tr>
              <th
                onClick={() => handleSort('patient_name')}
                style={{ cursor: 'pointer', userSelect: 'none', width: isArchive ? '24%' : '26%' }}
                className={sortField === 'patient_name' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between gap-1">
                  <span className="min-w-0">Patient Details</span>
                  {renderSortIcon('patient_name')}
                </div>
              </th>

              <th
                onClick={() => handleSort('admission_at')}
                style={{ cursor: 'pointer', userSelect: 'none', width: isArchive ? '15%' : '18%' }}
                className={sortField === 'admission_at' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between gap-1">
                  <span className="min-w-0">Admission / Stay</span>
                  {renderSortIcon('admission_at')}
                </div>
              </th>

              <th
                onClick={() => handleSort('consultant')}
                style={{ cursor: 'pointer', userSelect: 'none', width: isArchive ? '17%' : '19%' }}
                className={sortField === 'consultant' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between gap-1">
                  <span className="min-w-0">Doctor &amp; Department</span>
                  {renderSortIcon('consultant')}
                </div>
              </th>

              <th
                onClick={() => handleSort('format')}
                style={{ cursor: 'pointer', userSelect: 'none', width: isArchive ? '13%' : '15%' }}
                className={sortField === 'format' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between gap-1">
                  <span className="min-w-0">Format &amp; Workflow</span>
                  {renderSortIcon('format')}
                </div>
              </th>

              <th
                onClick={() => handleSort('status')}
                style={{ cursor: 'pointer', userSelect: 'none', width: '10%' }}
                className={sortField === 'status' ? 'bg-primary-subtle text-primary' : ''}
              >
                <div className="d-flex align-items-center justify-content-between gap-1">
                  <span className="min-w-0">Status</span>
                  {renderSortIcon('status')}
                </div>
              </th>

              <th className="text-end pe-3" style={{ width: isArchive ? '21%' : '12%', userSelect: 'none' }}>
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
                        <div className="min-w-0 flex-grow-1">
                          <div className="fw-bold text-dark">
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
                          {isArchive && item.diagnosis && (
                            <div className="text-muted mt-0.5" style={{ fontSize: '11px', lineHeight: 1.3 }} title={item.diagnosis}>
                              {item.diagnosis}
                            </div>
                          )}
                        </div>
                      </div>
                    </td>

                    {/* Admission / Stay */}
                    <td>
                      <div className="d-flex align-items-center gap-1 text-dark fw-medium" style={{ fontSize: '12.5px' }}>
                        <i className="bi bi-box-arrow-in-right text-success" style={{ fontSize: '12px' }}></i>
                        <span>{item.admission_at ? formatDate(item.admission_at) : '—'}</span>
                      </div>
                      <div className="d-flex align-items-center gap-1 text-muted small mt-0.5 flex-wrap" style={{ fontSize: '11.5px' }}>
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
                      <div className="text-dark fw-medium" style={{ fontSize: '12.5px' }}>
                        <i className="bi bi-person-badge text-primary me-1" style={{ fontSize: '12px' }}></i>
                        {item.consultant || '—'}
                      </div>
                      <div className="text-muted small mt-0.5" style={{ fontSize: '11.5px' }}>
                        {item.department ? `${item.department}` : ''}
                        {item.ward_room_no && (
                          <> &bull; {item.ward_room_no}</>
                        )}
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
                              ? 'bg-success-subtle text-success border border-success-subtle'
                              : item.surgery_type === 'medical'
                              ? 'bg-info-subtle text-info border border-info-subtle'
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
                      <div className="d-inline-flex flex-wrap justify-content-end align-items-center gap-1">
                        <ActionDropdown
                          toggleTitle="Preview separate documents"
                          fontSize="12px"
                          items={buildDocumentPreviewMenuItems(item.id)}
                          primaryAction={
                            <Link href={`/admissions/${item.id}/documents/preview`} prefetch className="btn btn-outline-secondary">
                              <i className="bi bi-eye me-1"></i> {isArchive ? 'Preview' : 'Review'}
                            </Link>
                          }
                        />
                        {auth.user?.can_edit && isArchive ? (
                          <ActionDropdown
                            toggleTitle="Jump to clinical stage"
                            fontSize="12px"
                            items={buildEditStageMenuItems(item.id)}
                            primaryAction={
                              <Link href={`/admissions/${item.id}/edit`} prefetch className="btn btn-outline-secondary" title="Edit Admission Stages">
                                <i className="bi bi-pencil"></i>
                              </Link>
                            }
                          />
                        ) : (
                          auth.user?.can_edit && (item.status === 'draft' || item.status === 'returned_for_correction') && (
                            <Link href={`/admissions/${item.id}/edit`} prefetch className="btn btn-sm btn-outline-secondary" title="Edit Clinical Workflow">
                              <i className="bi bi-pencil"></i>
                            </Link>
                          )
                        )}
                        {isArchive && (
                          <Link href={`/documents/${item.id}/history`} prefetch className="btn btn-sm btn-outline-secondary" title="Document Audit Trail & History">
                            <i className="bi bi-clock-history"></i>
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
                                href={`/admissions/${item.id}/documents/pdf/discharge-summary`}
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
      <div className="card-footer bg-white p-3 border-0 d-flex flex-wrap align-items-center justify-content-center justify-content-md-between gap-3">
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

      {/* Server Batch Pagination (when total records exceed current server page size) */}
      {admissions.last_page > 1 && (
        <>
          <div className="border-top bg-light-subtle px-3 py-2 text-center small text-muted">
            Archive Batch {admissions.current_page} of {admissions.last_page} (Total {admissions.total} records in database)
          </div>
          <Pagination links={admissions.links} />
        </>
      )}

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
