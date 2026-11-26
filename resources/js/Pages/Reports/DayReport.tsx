import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { formatDate, formatDateTime } from '../../utils/dateFormatter';

interface NewbornItem {
  id: number;
  sex?: string | null;
  birth_at?: string | null;
  birth_weight_kg?: number | string | null;
  discharge_weight_kg?: number | string | null;
  apgar_1_min?: number | null;
  apgar_5_min?: number | null;
  neonatal_care?: string | null;
  notes?: string | null;
  birth_vaccines?: string[] | null;
  vaccine_date?: string | null;
}

interface DayRecord {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  age?: string | null;
  sex?: string | null;
  admission_at?: string | null;
  procedure_at?: string | null;
  discharge_at?: string | null;
  format?: string | null;
  surgery_type: string;
  diagnosis?: string | null;
  picme_no?: string | null;
  procedure_done?: string | null;
  procedure_name?: string | null;
  incision?: string | null;
  consultant?: string | null;
  surgeon?: string | null;
  assisted_surgeon?: string | null;
  pediatrician_name?: string | null;
  anaesthetist?: string | null;
  assisted_anaesthetist?: string | null;
  anaesthesia_type?: string | null;
  staff_nurse?: string | null;
  ot_technician?: string | null;
  status: string;
  newborns?: NewbornItem[];
}

interface Props {
  records: {
    data: DayRecord[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    current_page: number;
    per_page: number;
    last_page: number;
  };
  metrics: {
    total_cases: number;
    surgeries_count: number;
    deliveries_count: number;
    babies_count: number;
    medical_count: number;
    discharges_count: number;
  };
  filters: {
    date?: string;
    date_from?: string;
    date_to?: string;
    date_basis?: string;
    all_dates?: boolean;
    search?: string;
    surgery_type?: string;
    format?: string;
    surgeon?: string;
    per_page?: number;
  };
  clinicalStaff: Array<{ id: number; name: string; category?: string }>;
}

export default function DayReport({ records, metrics, filters, clinicalStaff }: Props) {
  const [selectedDate, setSelectedDate] = useState(filters.date || '');
  const [dateFrom, setDateFrom] = useState(filters.date_from || '');
  const [dateTo, setDateTo] = useState(filters.date_to || '');
  const [dateBasis, setDateBasis] = useState(filters.date_basis || 'any');
  const [allDates, setAllDates] = useState(Boolean(filters.all_dates));
  const [search, setSearch] = useState(filters.search || '');
  const [surgeryType, setSurgeryType] = useState(filters.surgery_type || '');
  const [surgeon, setSurgeon] = useState(filters.surgeon || '');

  const toLocalIso = (d: Date) =>
    `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

  const buildQueryParams = (overrides: Record<string, any> = {}) => ({
    date: (overrides.all_dates ?? allDates) ? 'all' : (overrides.date ?? selectedDate),
    date_from: overrides.date_from ?? dateFrom,
    date_to: overrides.date_to ?? dateTo,
    date_basis: overrides.date_basis ?? dateBasis,
    all_dates: (overrides.all_dates ?? allDates) ? 1 : '',
    search: overrides.search ?? search,
    surgery_type: overrides.surgery_type ?? surgeryType,
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

  const handleReset = () => {
    const today = toLocalIso(new Date());
    setSelectedDate(today);
    setDateFrom('');
    setDateTo('');
    setDateBasis('any');
    setAllDates(false);
    setSearch('');
    setSurgeryType('');
    setSurgeon('');
    router.get('/reports/day');
  };

  const handleExportCsv = () => {
    const raw = buildQueryParams();
    const params = new URLSearchParams();
    Object.entries(raw).forEach(([k, v]) => {
      if (v !== '' && v !== undefined && v !== null) {
        params.set(k, String(v));
      }
    });
    params.set('export', 'csv');
    window.location.href = `/reports/day?${params.toString()}`;
  };

  const todayIso = toLocalIso(new Date());
  const yesterdayIso = toLocalIso(new Date(Date.now() - 86400000));
  const startIndex = ((records.current_page || 1) - 1) * (records.per_page || 50);

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
            Daily clinical register covering admissions (DOA), operations (DOO), discharges (DOD), teams, and deliveries
          </p>
        </div>

        <div className="d-flex align-items-center gap-2 no-print">
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
              Total Cases
            </div>
            <div className="h3 fw-bold text-dark mt-2 mb-0">{metrics.total_cases}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              {allDates ? 'Across all dates' : dateFrom || dateTo ? 'Selected range' : `Date: ${formatDate(selectedDate)}`}
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Surgical OT Cases
            </div>
            <div className="h3 fw-bold mt-2 mb-0" style={{ color: '#d97706' }}>
              {metrics.surgeries_count}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Non-obstetric procedures
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Deliveries &amp; Babies
            </div>
            <div className="h3 fw-bold mt-2 mb-0" style={{ color: '#16a34a' }}>
              {metrics.deliveries_count}{' '}
              <span className="fs-6 fw-normal text-muted">({metrics.babies_count} babies)</span>
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Obstetric deliveries
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Discharges / Medical
            </div>
            <div className="h3 fw-bold text-primary mt-2 mb-0">
              {metrics.discharges_count}{' '}
              <span className="fs-6 fw-normal text-muted">/ {metrics.medical_count} Med</span>
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Discharged patients &amp; medical cases
            </div>
          </div>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="card border-0 shadow-sm mb-4 no-print" style={{ borderRadius: '16px' }}>
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
              <label className="form-label small text-muted mb-1">Match Date By</label>
              <select
                className="form-select form-select-sm"
                value={dateBasis}
                onChange={(e) => setDateBasis(e.target.value)}
              >
                <option value="any">Any (DOA / DOO / DOD)</option>
                <option value="admission">DOA Only</option>
                <option value="procedure">DOO Only</option>
                <option value="discharge">DOD Only</option>
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

            <div className="col-6 col-md-3">
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

            <div className="col-12 col-md-3">
              <label className="form-label small text-muted mb-1">Search Name / UHID / IP No</label>
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="Patient name, UHID, IP No..."
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

      {/* Day Register Table */}
      <div className="card border-0 shadow-sm" style={{ borderRadius: '18px' }}>
        <div className="card-header bg-white py-3 px-4 border-0 d-flex align-items-center justify-content-between">
          <div className="d-flex align-items-center gap-2">
            <span className="p-2 rounded-circle" style={{ color: '#0284c7', backgroundColor: '#e0f2fe' }}>
              <i className="bi bi-journal-text"></i>
            </span>
            <div>
              <h5 className="card-title mb-0 fw-bold text-dark" style={{ fontSize: '15.5px' }}>
                Daily Clinical &amp; OT Register
              </h5>
              <div className="text-muted small" style={{ fontSize: '12px' }}>
                Showing {records.data.length} of {records.total} records
              </div>
            </div>
          </div>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-top mb-0" style={{ fontSize: '12.5px' }}>
            <thead className="table-light text-muted small text-uppercase" style={{ fontSize: '11px', letterSpacing: '0.04em' }}>
              <tr>
                <th className="ps-3 text-center" style={{ width: '50px' }}>S.No</th>
                <th style={{ minWidth: '170px' }}>Name / UHID / IPNO</th>
                <th style={{ minWidth: '100px' }}>DOA</th>
                <th style={{ minWidth: '125px' }}>DOO</th>
                <th style={{ minWidth: '100px' }}>DOD</th>
                <th style={{ minWidth: '190px' }}>Procedure Done &amp; Incision</th>
                <th style={{ minWidth: '210px' }}>Surgical &amp; Anesthesia Team</th>
                <th style={{ minWidth: '260px' }}>Delivery &amp; Baby Details (If Delivery)</th>
                <th className="text-end pe-3 no-print">Action</th>
              </tr>
            </thead>
            <tbody>
              {records.data.length === 0 ? (
                <tr>
                  <td colSpan={9} className="text-center py-5 text-muted">
                    <i className="bi bi-calendar-x fs-2 d-block mb-2 text-muted"></i>
                    No clinical records matched the selected day filter.
                  </td>
                </tr>
              ) : (
                records.data.map((item, idx) => {
                  const isDelivery =
                    item.surgery_type === 'delivery' || (item.newborns && item.newborns.length > 0);
                  const procedureLabel =
                    item.procedure_done ||
                    item.procedure_name ||
                    (item.surgery_type === 'medical'
                      ? `Medical Case${item.diagnosis ? `: ${item.diagnosis}` : ''}`
                      : '—');

                  return (
                    <tr key={item.id}>
                      <td className="ps-3 text-center fw-semibold text-muted">
                        {startIndex + idx + 1}
                      </td>

                      <td>
                        <div className="fw-bold text-dark">{item.patient_name}</div>
                        <div className="text-muted small">
                          UHID: <span className="font-monospace text-dark">{item.uhid}</span>
                        </div>
                        <div className="text-muted small">
                          IPNO: <span className="font-monospace text-dark">{item.ip_no}</span>
                        </div>
                        {(item.age || item.sex) && (
                          <div className="text-muted" style={{ fontSize: '11px' }}>
                            {[item.age ? `${item.age} Yrs` : null, item.sex].filter(Boolean).join(' • ')}
                          </div>
                        )}
                      </td>

                      <td className="text-nowrap">
                        {item.admission_at ? formatDate(item.admission_at) : '—'}
                      </td>

                      <td>
                        {item.procedure_at ? formatDateTime(item.procedure_at) : '—'}
                      </td>

                      <td className="text-nowrap">
                        {item.discharge_at ? (
                          <span className="text-dark fw-medium">{formatDate(item.discharge_at)}</span>
                        ) : (
                          <span className="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                            In Admission
                          </span>
                        )}
                      </td>

                      <td>
                        <div className="fw-semibold text-dark">{procedureLabel}</div>
                        {item.incision && (
                          <div className="text-muted small mt-1">
                            <span className="fw-semibold">Incision:</span> {item.incision}
                          </div>
                        )}
                      </td>

                      <td>
                        <div className="d-flex flex-column gap-1 small">
                          {item.surgeon && (
                            <div>
                              <span className="text-muted fw-semibold">Surgeon:</span>{' '}
                              <span className="text-dark">{item.surgeon}</span>
                            </div>
                          )}
                          {item.assisted_surgeon && (
                            <div>
                              <span className="text-muted fw-semibold">Asst. Surgeon:</span>{' '}
                              <span>{item.assisted_surgeon}</span>
                            </div>
                          )}
                          {item.consultant && (
                            <div>
                              <span className="text-muted fw-semibold">Consultant:</span>{' '}
                              <span>{item.consultant}</span>
                            </div>
                          )}
                          {item.pediatrician_name && (
                            <div>
                              <span className="text-muted fw-semibold">Pediatrician:</span>{' '}
                              <span>{item.pediatrician_name}</span>
                            </div>
                          )}
                          {item.anaesthetist && (
                            <div>
                              <span className="text-muted fw-semibold">Anaesthetist:</span>{' '}
                              <span>
                                {item.anaesthetist}
                                {item.assisted_anaesthetist ? ` (Asst: ${item.assisted_anaesthetist})` : ''}
                              </span>
                            </div>
                          )}
                          {item.anaesthesia_type && (
                            <div>
                              <span className="badge bg-light text-dark border">
                                {item.anaesthesia_type}
                              </span>
                            </div>
                          )}
                          {(item.staff_nurse || item.ot_technician) && (
                            <div className="text-muted" style={{ fontSize: '11px' }}>
                              {[
                                item.staff_nurse ? `Nurse: ${item.staff_nurse}` : null,
                                item.ot_technician ? `OT Tech: ${item.ot_technician}` : null,
                              ]
                                .filter(Boolean)
                                .join(' | ')}
                            </div>
                          )}
                          {!item.surgeon && !item.consultant && !item.anaesthetist && (
                            <span className="text-muted">—</span>
                          )}
                        </div>
                      </td>

                      <td>
                        {!isDelivery ? (
                          <span className="text-muted small">—</span>
                        ) : (
                          <div className="d-flex flex-column gap-2 small">
                            <div className="p-2 rounded bg-light border">
                              <div className="fw-semibold text-success">
                                <i className="bi bi-heart-pulse me-1"></i>
                                Delivery: {item.procedure_done || item.procedure_name || 'Obstetric Delivery'}
                              </div>
                              {item.picme_no && (
                                <div className="text-muted" style={{ fontSize: '11.5px' }}>
                                  PICME / RCH ID: <span className="font-monospace text-dark">{item.picme_no}</span>
                                </div>
                              )}
                            </div>

                            {item.newborns && item.newborns.length > 0 ? (
                              item.newborns.map((baby, bIdx) => {
                                const sexLower = (baby.sex || '').trim().toLowerCase();
                                const isMale = sexLower === 'male' || sexLower === 'boy';
                                const isFemale = sexLower === 'female' || sexLower === 'girl';
                                const badgeClass = isMale
                                  ? 'bg-primary-subtle text-primary border border-primary-subtle'
                                  : isFemale
                                  ? 'bg-success-subtle text-success border border-success-subtle'
                                  : 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                const sexLabel = isMale ? 'Boy' : isFemale ? 'Girl' : baby.sex || 'Not Recorded';
                                const vaccines = Array.isArray(baby.birth_vaccines)
                                  ? baby.birth_vaccines.filter(Boolean)
                                  : [];

                                return (
                                  <div key={baby.id || bIdx} className="p-2 rounded border bg-white">
                                    <div className="d-flex flex-wrap align-items-center gap-2 mb-1">
                                      <span className={`badge ${badgeClass}`}>{sexLabel}</span>
                                      {baby.birth_at && (
                                        <span className="text-muted" style={{ fontSize: '11.5px' }}>
                                          {formatDateTime(baby.birth_at)}
                                        </span>
                                      )}
                                    </div>
                                    <div style={{ fontSize: '11.5px' }}>
                                      <strong>Birth Wt:</strong>{' '}
                                      {baby.birth_weight_kg ? `${Number(baby.birth_weight_kg).toFixed(2)} kg` : '—'}
                                      {baby.discharge_weight_kg && (
                                        <>
                                          {' '}| <strong>Disch Wt:</strong>{' '}
                                          {Number(baby.discharge_weight_kg).toFixed(2)} kg
                                        </>
                                      )}
                                      {(baby.apgar_1_min !== null || baby.apgar_5_min !== null) && (
                                        <>
                                          {' '}| <strong>APGAR:</strong> {baby.apgar_1_min ?? '—'}/
                                          {baby.apgar_5_min ?? '—'}
                                        </>
                                      )}
                                    </div>
                                    {baby.neonatal_care && (
                                      <div className="text-muted" style={{ fontSize: '11px' }}>
                                        <strong>Care:</strong> {baby.neonatal_care}
                                      </div>
                                    )}
                                    {baby.notes && (
                                      <div className="text-dark" style={{ fontSize: '11px' }}>
                                        <strong>Notes / Presentation:</strong> {baby.notes}
                                      </div>
                                    )}
                                    {(vaccines.length > 0 || baby.vaccine_date) && (
                                      <div className="text-success" style={{ fontSize: '11px' }}>
                                        <strong>Vaccines:</strong>{' '}
                                        {vaccines.length > 0 ? vaccines.join(', ') : 'Recorded'}
                                        {baby.vaccine_date ? ` (${formatDate(baby.vaccine_date)})` : ''}
                                      </div>
                                    )}
                                  </div>
                                );
                              })
                            ) : (
                              <span className="text-muted fst-italic" style={{ fontSize: '11.5px' }}>
                                Newborn details not recorded
                              </span>
                            )}
                          </div>
                        )}
                      </td>

                      <td className="text-end pe-3 no-print">
                        <Link
                          href={`/admissions/${item.id}/documents/preview`}
                          className="btn btn-sm btn-outline-secondary"
                          style={{ borderRadius: '8px', fontSize: '12px' }}
                        >
                          <i className="bi bi-eye me-1"></i> Review
                        </Link>
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>

        <Pagination links={records.links} />
      </div>
    </AppLayout>
  );
}
