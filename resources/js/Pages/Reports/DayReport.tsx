import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import DayReportSectionTable, { DayRecord, DaySectionKey } from '../../Components/DayReportSectionTable';
import { formatDate } from '../../utils/dateFormatter';
import { downloadReportFile } from '../../utils/reportDownload';

interface Props {
  records: {
    data: DayRecord[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    current_page: number;
    per_page: number;
    last_page: number;
  };
  sections: {
    admissions: DayRecord[];
    discharges: DayRecord[];
    inpatients: DayRecord[];
  };
  metrics: {
    total_cases: number;
    admissions_count: number;
    discharges_section_count: number;
    inpatients_count: number;
    census_date?: string;
    surgeries_count: number;
    deliveries_count: number;
    babies_count: number;
    census_deliveries_count?: number;
    census_babies_count?: number;
    medical_count: number;
    discharges_count: number;
  };
  filters: {
    date?: string;
    date_from?: string;
    date_to?: string;
    date_basis?: string;
    section?: string;
    all_dates?: boolean;
    search?: string;
    surgery_type?: string;
    format?: string;
    surgeon?: string;
    per_page?: number;
  };
  clinicalStaff: Array<{ id: number; name: string; category?: string }>;
}

export default function DayReport({ sections, metrics, filters, clinicalStaff }: Props) {
  const [selectedDate, setSelectedDate] = useState(filters.date || '');
  const [dateFrom, setDateFrom] = useState(filters.date_from || '');
  const [dateTo, setDateTo] = useState(filters.date_to || '');
  const [sectionFilter, setSectionFilter] = useState<string>(filters.section || 'all');
  const [allDates, setAllDates] = useState(Boolean(filters.all_dates));
  const [search, setSearch] = useState(filters.search || '');
  const [surgeryType, setSurgeryType] = useState(filters.surgery_type || '');
  const [format, setFormat] = useState(filters.format || '');
  const [surgeon, setSurgeon] = useState(filters.surgeon || '');
  const [downloadingFormat, setDownloadingFormat] = useState<'image' | 'pdf' | null>(null);

  const toLocalIso = (d: Date) =>
    `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

  const buildQueryParams = (overrides: Record<string, any> = {}) => ({
    date: (overrides.all_dates ?? allDates) ? 'all' : (overrides.date ?? selectedDate),
    date_from: overrides.date_from ?? dateFrom,
    date_to: overrides.date_to ?? dateTo,
    section: overrides.section ?? sectionFilter,
    all_dates: (overrides.all_dates ?? allDates) ? 1 : '',
    search: overrides.search ?? search,
    surgery_type: overrides.surgery_type ?? surgeryType,
    format: overrides.format ?? format,
    surgeon: overrides.surgeon ?? surgeon,
  });

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/reports/day', buildQueryParams(), { preserveState: true });
  };

  const handleQuickDate = (targetDate: string) => {
    setAllDates(false);
    setDateFrom('');
    setDateTo('');
    setSelectedDate(targetDate);
    router.get(
      '/reports/day',
      buildQueryParams({ date: targetDate, date_from: '', date_to: '', all_dates: false }),
      { preserveState: true }
    );
  };

  const handleAllDatesToggle = (checked: boolean) => {
    setAllDates(checked);
    if (checked) {
      setDateFrom('');
      setDateTo('');
      router.get(
        '/reports/day',
        buildQueryParams({ date: 'all', date_from: '', date_to: '', all_dates: true }),
        { preserveState: true }
      );
    } else {
      const today = toLocalIso(new Date());
      setSelectedDate(today);
      router.get(
        '/reports/day',
        buildQueryParams({ date: today, all_dates: false }),
        { preserveState: true }
      );
    }
  };

  const handleSectionTab = (nextSection: string) => {
    setSectionFilter(nextSection);
    router.get('/reports/day', buildQueryParams({ section: nextSection }), { preserveState: true });
  };

  const handleReset = () => {
    const today = toLocalIso(new Date());
    setSelectedDate(today);
    setDateFrom('');
    setDateTo('');
    setSectionFilter('all');
    setAllDates(false);
    setSearch('');
    setSurgeryType('');
    setFormat('');
    setSurgeon('');
    router.get('/reports/day');
  };

  const buildExportUrl = (formatType: 'csv' | 'image' | 'pdf') => {
    const raw = buildQueryParams();
    const params = new URLSearchParams();
    Object.entries(raw).forEach(([k, v]) => {
      if (v !== '' && v !== undefined && v !== null) {
        params.set(k, String(v));
      }
    });
    params.set('export', formatType);
    return `/reports/day?${params.toString()}`;
  };

  const handleExportCsv = () => {
    window.location.href = buildExportUrl('csv');
  };

  const handleDownloadBinary = async (formatType: 'image' | 'pdf') => {
    setDownloadingFormat(formatType);
    try {
      const ext = formatType === 'pdf' ? 'pdf' : 'png';
      await downloadReportFile(
        buildExportUrl(formatType),
        `day_report_${selectedDate || 'complete'}.${ext}`
      );
    } catch {
      alert(`Failed to generate Day Report ${formatType.toUpperCase()}. Please try again.`);
    } finally {
      setDownloadingFormat(null);
    }
  };

  const todayIso = toLocalIso(new Date());
  const yesterdayIso = toLocalIso(new Date(Date.now() - 86400000));

  const visibleSections: DaySectionKey[] =
    sectionFilter === 'admissions' || sectionFilter === 'discharges' || sectionFilter === 'inpatients'
      ? [sectionFilter]
      : ['admissions', 'discharges', 'inpatients'];

  const safeSections = sections || { admissions: [], discharges: [], inpatients: [] };
  const periodText = allDates
    ? 'All Dates (Live Ward Census)'
    : dateFrom || dateTo
    ? `${dateFrom ? formatDate(dateFrom) : 'Start'} to ${dateTo ? formatDate(dateTo) : 'End'}`
    : `Date: ${formatDate(selectedDate)}`;

  return (
    <AppLayout>
      <Head title="Day Report — Guru Hospital" />

      {/* Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <div className="d-flex align-items-center gap-2 mb-1">
            <span className="p-2 rounded-circle" style={{ background: '#e0f2fe', color: '#0284c7' }}>
              <i className="bi bi-calendar-day fs-5"></i>
            </span>
            <h1 className="h3 fw-bold mb-0 text-dark" style={{ letterSpacing: '-0.02em' }}>
              Day Report
            </h1>
          </div>
          <p className="text-muted mb-0" style={{ fontSize: '13.5px' }}>
            Complete daily hospital register covering <strong>1. Admissions</strong>, <strong>2. Discharges</strong>, and{' '}
            <strong>3. Inpatients (Active Bed Census)</strong>
          </p>
        </div>

        <div className="d-flex flex-wrap align-items-center gap-2 no-print">
          <button
            type="button"
            onClick={() => handleDownloadBinary('pdf')}
            disabled={downloadingFormat !== null}
            className="btn btn-danger d-flex align-items-center gap-1 shadow-sm"
            style={{ borderRadius: '10px', fontSize: '13px' }}
          >
            {downloadingFormat === 'pdf' ? (
              <>
                <span className="spinner-border spinner-border-sm me-1" role="status"></span>
                Generating PDF...
              </>
            ) : (
              <>
                <i className="bi bi-file-earmark-pdf"></i> Download PDF
              </>
            )}
          </button>
          <button
            type="button"
            onClick={() => handleDownloadBinary('image')}
            disabled={downloadingFormat !== null}
            className="btn btn-primary d-flex align-items-center gap-1 shadow-sm"
            style={{ borderRadius: '10px', fontSize: '13px' }}
          >
            {downloadingFormat === 'image' ? (
              <>
                <span className="spinner-border spinner-border-sm me-1" role="status"></span>
                Generating Image...
              </>
            ) : (
              <>
                <i className="bi bi-file-earmark-image"></i> Download Image
              </>
            )}
          </button>
          <button
            type="button"
            onClick={handleExportCsv}
            className="btn btn-outline-secondary d-flex align-items-center gap-1 shadow-sm"
            style={{ borderRadius: '10px', fontSize: '13px' }}
          >
            <i className="bi bi-download"></i> Export CSV
          </button>
          <button
            type="button"
            onClick={() => window.print()}
            className="btn btn-light border d-flex align-items-center gap-1 shadow-sm"
            style={{ borderRadius: '10px', fontSize: '13px' }}
          >
            <i className="bi bi-printer"></i> Print
          </button>
        </div>
      </div>

      {/* 4 Summary Metric Cards */}
      <div className="row g-3 mb-4">
        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              1. Admissions
            </div>
            <div className="h3 fw-bold mt-2 mb-0" style={{ color: '#0369a1' }}>
              {metrics.admissions_count ?? safeSections.admissions.length}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              {periodText}
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              2. Discharges
            </div>
            <div className="h3 fw-bold mt-2 mb-0" style={{ color: '#15803d' }}>
              {metrics.discharges_section_count ?? metrics.discharges_count ?? safeSections.discharges.length}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Completed discharges in period
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              3. Active Inpatients
            </div>
            <div className="h3 fw-bold mt-2 mb-0" style={{ color: '#b45309' }}>
              {metrics.inpatients_count ?? safeSections.inpatients.length}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Ward census {metrics.census_date ? `as of ${formatDate(metrics.census_date)}` : 'active'}
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              OT, Deliveries &amp; Babies
            </div>
            <div className="h3 fw-bold text-dark mt-2 mb-0">
              {metrics.deliveries_count}{' '}
              <span className="fs-6 fw-normal text-muted">
                ({metrics.babies_count} babies)
              </span>
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              {metrics.surgeries_count} Surgical OT &bull; {metrics.medical_count} Medical
            </div>
          </div>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="card border-0 shadow-sm mb-3 no-print" style={{ borderRadius: '16px' }}>
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-end">
            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">Day Date</label>
              <input
                type="date"
                className="form-control form-control-sm"
                value={allDates ? '' : selectedDate}
                disabled={allDates || Boolean(dateFrom) || Boolean(dateTo)}
                onChange={(e) => {
                  setAllDates(false);
                  setSelectedDate(e.target.value);
                }}
              />
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">Report Section</label>
              <select
                className="form-select form-select-sm"
                value={sectionFilter}
                onChange={(e) => setSectionFilter(e.target.value)}
              >
                <option value="all">All 3 Sections</option>
                <option value="admissions">1. Admissions Only</option>
                <option value="discharges">2. Discharges Only</option>
                <option value="inpatients">3. Inpatients Only</option>
              </select>
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">Workflow</label>
              <select
                className="form-select form-select-sm"
                value={surgeryType}
                onChange={(e) => setSurgeryType(e.target.value)}
              >
                <option value="">All Workflows</option>
                <option value="delivery">Delivery</option>
                <option value="other">Surgical</option>
                <option value="medical">Medical Case</option>
              </select>
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">Format</label>
              <select
                className="form-select form-select-sm"
                value={format}
                onChange={(e) => setFormat(e.target.value)}
              >
                <option value="">All Formats</option>
                <option value="general">General</option>
                <option value="cmchistn">CMCHISTN</option>
                <option value="private">Private / TPA</option>
                <option value="employee">Employee</option>
              </select>
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">Surgeon / Consultant</label>
              <select
                className="form-select form-select-sm"
                value={surgeon}
                onChange={(e) => setSurgeon(e.target.value)}
              >
                <option value="">All Doctors</option>
                {clinicalStaff.map((doc) => (
                  <option key={doc.id} value={doc.name}>
                    {doc.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">Search Patient / ID</label>
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="Name, UHID, IP No..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">Range From (Optional)</label>
              <input
                type="date"
                className="form-control form-control-sm"
                value={dateFrom}
                onChange={(e) => {
                  setAllDates(false);
                  setDateFrom(e.target.value);
                }}
              />
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">Range To (Optional)</label>
              <input
                type="date"
                className="form-control form-control-sm"
                value={dateTo}
                onChange={(e) => {
                  setAllDates(false);
                  setDateTo(e.target.value);
                }}
              />
            </div>

            <div className="col-12 col-md-8 d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
              <div className="d-flex flex-wrap align-items-center gap-2">
                <button
                  type="button"
                  onClick={() => handleQuickDate(todayIso)}
                  className={`btn btn-sm ${!allDates && !dateFrom && !dateTo && selectedDate === todayIso ? 'btn-primary' : 'btn-outline-secondary'}`}
                >
                  Today
                </button>
                <button
                  type="button"
                  onClick={() => handleQuickDate(yesterdayIso)}
                  className={`btn btn-sm ${!allDates && !dateFrom && !dateTo && selectedDate === yesterdayIso ? 'btn-primary' : 'btn-outline-secondary'}`}
                >
                  Yesterday
                </button>
                <div className="form-check form-check-inline ms-2 mb-0">
                  <input
                    id="dayReportAllDates"
                    type="checkbox"
                    className="form-check-input"
                    checked={allDates}
                    onChange={(e) => handleAllDatesToggle(e.target.checked)}
                  />
                  <label htmlFor="dayReportAllDates" className="form-check-label small text-dark" style={{ cursor: 'pointer' }}>
                    Show All Dates
                  </label>
                </div>
              </div>

              <div className="d-flex gap-2">
                <button type="submit" className="btn btn-sm btn-primary px-3" style={{ borderRadius: '8px' }}>
                  <i className="bi bi-funnel me-1"></i> Apply Filter
                </button>
                <button
                  type="button"
                  onClick={handleReset}
                  className="btn btn-sm btn-light border px-3"
                  style={{ borderRadius: '8px' }}
                >
                  Reset
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      {/* Section Switcher Tabs */}
      <div className="d-flex flex-wrap gap-2 mb-3 no-print">
        {[
          {
            key: 'all',
            label: 'All 3 Sections',
            count: safeSections.admissions.length + safeSections.discharges.length + safeSections.inpatients.length,
          },
          { key: 'admissions', label: '1. Admissions', count: safeSections.admissions.length },
          { key: 'discharges', label: '2. Discharges', count: safeSections.discharges.length },
          { key: 'inpatients', label: '3. Inpatients', count: safeSections.inpatients.length },
        ].map((tab) => {
          const active = sectionFilter === tab.key;
          return (
            <button
              key={tab.key}
              type="button"
              onClick={() => handleSectionTab(tab.key)}
              className={`btn btn-sm d-flex align-items-center gap-2 ${active ? 'btn-dark shadow-sm' : 'btn-white bg-white border text-dark'}`}
              style={{ borderRadius: '10px', padding: '6px 14px', fontSize: '12.5px', fontWeight: 600 }}
            >
              <span>{tab.label}</span>
              <span className={`badge ${active ? 'bg-light text-dark' : 'bg-secondary-subtle text-dark'}`}>
                {tab.count}
              </span>
            </button>
          );
        })}
      </div>

      {/* Dedicated Complete Detail Sections */}
      {visibleSections.map((key) => (
        <DayReportSectionTable
          key={key}
          sectionKey={key}
          items={safeSections[key] || []}
          censusDate={metrics.census_date}
        />
      ))}
    </AppLayout>
  );
}
