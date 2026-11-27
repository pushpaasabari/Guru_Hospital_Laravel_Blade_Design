import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { formatDate, formatDateTime } from '../../utils/dateFormatter';

interface SurgeryItem {
  id: number;
  uhid: string;
  ip_no: string;
  patient_name: string;
  age?: string;
  sex?: string;
  admission_at?: string;
  procedure_at?: string;
  discharge_at?: string;
  procedure_done?: string;
  procedure_name?: string;
  incision?: string;
  surgeon?: string;
  assisted_surgeon?: string;
  anaesthetist?: string;
  anaesthesia_type?: string;
  blood_loss_ml?: number | string;
  findings?: string;
  drain?: string;
  stent_removal_date?: string;
  status: string;
}

interface Props {
  surgeries: {
    data: SurgeryItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    current_page: number;
    last_page: number;
  };
  metrics: {
    total_surgeries: number;
    avg_blood_loss_ml: number;
    stent_placed_count: number;
    top_surgeons: Record<string, number>;
    anesthesia_breakdown: Record<string, number>;
  };
  filters: {
    date_from?: string;
    date_to?: string;
    search?: string;
    surgeon?: string;
    anaesthetist?: string;
    anaesthesia_type?: string;
    format?: string;
    per_page?: number;
  };
  clinicalStaff: Array<{ id: number; name: string; category?: string }>;
  anesthesiaTypes: Array<{ id: number; name: string; code?: string }>;
}

export default function SurgeryReport({ surgeries, metrics, filters, clinicalStaff, anesthesiaTypes }: Props) {
  const [search, setSearch] = useState(filters.search || '');
  const [dateFrom, setDateFrom] = useState(filters.date_from || '');
  const [dateTo, setDateTo] = useState(filters.date_to || '');
  const [surgeon, setSurgeon] = useState(filters.surgeon || '');
  const [anaesthetist, setAnaesthetist] = useState(filters.anaesthetist || '');
  const [anaesthesiaType, setAnaesthesiaType] = useState(filters.anaesthesia_type || '');

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      '/reports/surgery',
      { search, date_from: dateFrom, date_to: dateTo, surgeon, anaesthetist, anaesthesia_type: anaesthesiaType },
      { preserveState: true }
    );
  };

  const handleReset = () => {
    setSearch('');
    setDateFrom('');
    setDateTo('');
    setSurgeon('');
    setAnaesthetist('');
    setAnaesthesiaType('');
    router.get('/reports/surgery');
  };

  const handleExportCsv = () => {
    const params = new URLSearchParams({
      search,
      date_from: dateFrom,
      date_to: dateTo,
      surgeon,
      anaesthetist,
      anaesthesia_type: anaesthesiaType,
      export: 'csv',
    });
    window.location.href = `/reports/surgery?${params.toString()}`;
  };

  return (
    <AppLayout>
      <Head title="Surgery Report — Guru Hospital" />

      {/* Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <div className="d-flex align-items-center gap-2 mb-1">
            <span className="p-2 rounded-circle" style={{ background: '#fef3c7', color: '#d97706' }}>
              <i className="bi bi-bandaid fs-5"></i>
            </span>
            <h1 className="h3 fw-bold mb-0 text-dark" style={{ letterSpacing: '-0.02em' }}>
              Surgery Report
            </h1>
          </div>
          <p className="text-muted mb-0" style={{ fontSize: '13.5px' }}>
            Operative surgical logs, OT surgeon performance &amp; anesthesia analytics
          </p>
        </div>

        <div className="d-flex align-items-center gap-2">
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
              Total Surgeries
            </div>
            <div className="h3 fw-bold text-dark mt-2 mb-0">{metrics.total_surgeries}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              OT procedures performed
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Avg. Blood Loss
            </div>
            <div className="h3 fw-bold text-danger mt-2 mb-0">
              {metrics.avg_blood_loss_ml > 0 ? `${metrics.avg_blood_loss_ml} ml` : '—'}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Mean estimated loss
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Stent Insertions
            </div>
            <div className="h3 fw-bold text-warning mt-2 mb-0">{metrics.stent_placed_count}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Cases with stent removal date
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Active Surgeons
            </div>
            <div className="h3 fw-bold text-primary mt-2 mb-0">
              {Object.keys(metrics.top_surgeons || {}).length}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Consultant operating doctors
            </div>
          </div>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="card border-0 shadow-sm mb-4" style={{ borderRadius: '16px' }}>
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-end">
            <div className="col-12 col-sm-6 col-md-3">
              <label className="form-label small text-muted mb-1">Search Patient / UHID / Procedure</label>
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="Name, UHID, Procedure..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>

            <div className="col-6 col-sm-3 col-md-2">
              <label className="form-label small text-muted mb-1">From Date</label>
              <input
                type="date"
                className="form-control form-control-sm"
                value={dateFrom}
                onChange={(e) => setDateFrom(e.target.value)}
              />
            </div>

            <div className="col-6 col-sm-3 col-md-2">
              <label className="form-label small text-muted mb-1">To Date</label>
              <input
                type="date"
                className="form-control form-control-sm"
                value={dateTo}
                onChange={(e) => setDateTo(e.target.value)}
              />
            </div>

            <div className="col-6 col-sm-4 col-md-2">
              <label className="form-label small text-muted mb-1">Surgeon</label>
              <select
                className="form-select form-select-sm"
                value={surgeon}
                onChange={(e) => setSurgeon(e.target.value)}
              >
                <option value="">All Surgeons</option>
                {clinicalStaff.map((doc) => (
                  <option key={doc.id} value={doc.name}>
                    {doc.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="col-6 col-sm-4 col-md-3">
              <label className="form-label small text-muted mb-1">Anesthesia Type</label>
              <select
                className="form-select form-select-sm"
                value={anaesthesiaType}
                onChange={(e) => setAnaesthesiaType(e.target.value)}
              >
                <option value="">All Anesthesia</option>
                {anesthesiaTypes.map((an) => (
                  <option key={an.id} value={an.name}>
                    {an.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="col-12 d-flex justify-content-end gap-2 mt-2">
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
          </form>
        </div>
      </div>

      {/* Surgeries Table */}
      <div className="card border-0 shadow-sm" style={{ borderRadius: '18px' }}>
        <div className="card-header bg-white py-3 px-4 border-0 d-flex align-items-center justify-content-between">
          <div className="d-flex align-items-center gap-2">
            <span className="p-2 rounded-circle" style={{ color: '#d97706', backgroundColor: '#fef3c7' }}>
              <i className="bi bi-journal-medical"></i>
            </span>
            <div>
              <h5 className="card-title mb-0 fw-bold text-dark" style={{ fontSize: '15.5px' }}>
                Operative Procedures Register
              </h5>
              <div className="text-muted small" style={{ fontSize: '12px' }}>
                Showing {surgeries.data.length} of {surgeries.total} surgical cases
              </div>
            </div>
          </div>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0 fg-fit-table fg-table-compact fg-mobile-stack-table" style={{ fontSize: '12.5px' }}>
            <thead className="table-light text-muted small text-uppercase" style={{ fontSize: '11px', letterSpacing: '0.04em' }}>
              <tr>
                <th className="ps-4" style={{ width: '19%' }}>Patient Details</th>
                <th style={{ width: '13%' }}>Surgery Date &amp; Time</th>
                <th style={{ width: '18%' }}>Procedure Done &amp; Incision</th>
                <th style={{ width: '17%' }}>Surgical &amp; Anesthesia Team</th>
                <th style={{ width: '14%' }}>Operative Details</th>
                <th style={{ width: '9%' }}>Status</th>
                <th className="text-end pe-4" style={{ width: '10%' }}>Actions</th>
              </tr>
            </thead>
            <tbody>
              {surgeries.data.length === 0 ? (
                <tr className="fg-empty-row">
                  <td colSpan={7} className="text-center py-5 text-muted">
                    <i className="bi bi-folder-x fs-2 d-block mb-2 text-muted"></i>
                    No surgical records found for the selected criteria.
                  </td>
                </tr>
              ) : (
                surgeries.data.map((item) => (
                  <tr key={item.id}>
                    <td className="ps-4" data-label="Patient Details">
                      <div className="fw-bold text-dark">{item.patient_name}</div>
                      <div className="text-muted small">
                        UHID: <span className="font-monospace text-dark">{item.uhid}</span> &bull; IP: <span className="font-monospace text-dark">{item.ip_no}</span>
                        {item.sex && <> &bull; {item.sex.toUpperCase()}</>}
                        {item.age && <> &bull; {item.age} Yrs</>}
                      </div>
                    </td>
                    <td data-label="Surgery Date & Time">
                      <div>{item.procedure_at ? formatDateTime(item.procedure_at) : (item.admission_at ? formatDate(item.admission_at) : '—')}</div>
                      {item.discharge_at && (
                        <div className="text-muted small" style={{ fontSize: '11.5px' }}>
                          Discharged: {formatDate(item.discharge_at)}
                        </div>
                      )}
                    </td>
                    <td data-label="Procedure Done & Incision">
                      <div className="fw-semibold text-dark">
                        {item.procedure_done || item.procedure_name || 'Surgical Procedure'}
                      </div>
                      {item.incision && (
                        <div className="text-muted small" style={{ fontSize: '11.5px' }}>
                          Incision: {item.incision}
                        </div>
                      )}
                    </td>
                    <td data-label="Surgical & Anesthesia Team">
                      <div className="text-dark fw-medium" style={{ fontSize: '12.5px' }}>
                        Surgeon: {item.surgeon || '—'}
                      </div>
                      <div className="text-muted small" style={{ fontSize: '11.5px' }}>
                        Anaesthetist: {item.anaesthetist || '—'}
                        {item.anaesthesia_type && ` (${item.anaesthesia_type})`}
                      </div>
                    </td>
                    <td data-label="Operative Details">
                      <div className="d-flex flex-wrap gap-1 align-items-center">
                        {item.blood_loss_ml && (
                          <span className="badge bg-light text-danger border" style={{ fontSize: '10.5px' }}>
                            Loss: {item.blood_loss_ml} ml
                          </span>
                        )}
                        {item.drain && (
                          <span className="badge bg-light text-secondary border" style={{ fontSize: '10.5px' }}>
                            Drain: {item.drain}
                          </span>
                        )}
                        {item.stent_removal_date && (
                          <span className="badge bg-warning-subtle text-warning border border-warning-subtle" style={{ fontSize: '10.5px' }}>
                            Stent: {formatDate(item.stent_removal_date)}
                          </span>
                        )}
                      </div>
                    </td>
                    <td data-label="Status">
                      <span className="badge bg-light text-secondary border text-uppercase" style={{ fontSize: '10px' }}>
                        {item.status.replace(/_/g, ' ')}
                      </span>
                    </td>
                    <td className="text-end pe-4" data-label="Actions">
                      <Link
                        href={`/admissions/${item.id}/documents/preview`}
                        className="btn btn-sm btn-outline-secondary"
                        style={{ borderRadius: '8px', fontSize: '12px', padding: '4px 8px' }}
                      >
                        <i className="bi bi-eye me-1"></i> Review
                      </Link>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <div className="p-3 border-top">
          <Pagination links={surgeries.links} />
        </div>
      </div>
    </AppLayout>
  );
}
