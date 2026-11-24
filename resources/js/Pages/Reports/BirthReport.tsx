import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { formatDate, formatDateTime } from '../../utils/dateFormatter';

interface Newborn {
  id: number;
  sex: string;
  birth_at?: string;
  birth_weight_kg?: number | string;
  apgar_1_min?: string;
  apgar_5_min?: string;
  neonatal_care?: string;
}

interface DeliveryItem {
  id: number;
  uhid: string;
  ip_no: string;
  patient_name: string;
  age?: string;
  admission_at?: string;
  procedure_at?: string;
  discharge_at?: string;
  procedure_done?: string;
  procedure_name?: string;
  surgeon?: string;
  consultant?: string;
  pediatrician_name?: string;
  status: string;
  newborns: Newborn[];
}

interface Props {
  deliveries: {
    data: DeliveryItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    current_page: number;
    last_page: number;
  };
  metrics: {
    total_deliveries: number;
    total_babies: number;
    male_babies: number;
    female_babies: number;
    avg_weight_kg: number;
    lscs_count: number;
    normal_count: number;
  };
  filters: {
    date_from?: string;
    date_to?: string;
    search?: string;
    delivery_type?: string;
    doctor?: string;
    baby_sex?: string;
    per_page?: number;
  };
  clinicalStaff: Array<{ id: number; name: string; category?: string }>;
}

export default function BirthReport({ deliveries, metrics, filters, clinicalStaff }: Props) {
  const [search, setSearch] = useState(filters.search || '');
  const [dateFrom, setDateFrom] = useState(filters.date_from || '');
  const [dateTo, setDateTo] = useState(filters.date_to || '');
  const [deliveryType, setDeliveryType] = useState(filters.delivery_type || '');
  const [doctor, setDoctor] = useState(filters.doctor || '');
  const [babySex, setBabySex] = useState(filters.baby_sex || '');

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      '/reports/birth',
      { search, date_from: dateFrom, date_to: dateTo, delivery_type: deliveryType, doctor, baby_sex: babySex },
      { preserveState: true }
    );
  };

  const handleReset = () => {
    setSearch('');
    setDateFrom('');
    setDateTo('');
    setDeliveryType('');
    setDoctor('');
    setBabySex('');
    router.get('/reports/birth');
  };

  const handleExportCsv = () => {
    const params = new URLSearchParams({
      search,
      date_from: dateFrom,
      date_to: dateTo,
      delivery_type: deliveryType,
      doctor,
      baby_sex: babySex,
      export: 'csv',
    });
    window.location.href = `/reports/birth?${params.toString()}`;
  };

  return (
    <AppLayout>
      <Head title="Birth Report — Guru Hospital" />

      {/* Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <div className="d-flex align-items-center gap-2 mb-1">
            <span className="p-2 rounded-circle" style={{ background: '#fce7f3', color: '#db2777' }}>
              <i className="bi bi-gender-ambiguous fs-5"></i>
            </span>
            <h1 className="h3 fw-bold mb-0 text-dark" style={{ letterSpacing: '-0.02em' }}>
              Birth Report
            </h1>
          </div>
          <p className="text-muted mb-0" style={{ fontSize: '13.5px' }}>
            Maternal delivery outcomes, live births register &amp; neonatal metrics
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

      {/* 5 Summary Metric Cards */}
      <div className="row g-3 mb-4">
        <div className="col-6 col-md-4 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Total Deliveries
            </div>
            <div className="h3 fw-bold text-dark mt-2 mb-0">{metrics.total_deliveries}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Maternal cases
            </div>
          </div>
        </div>

        <div className="col-6 col-md-4 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Total Live Births
            </div>
            <div className="h3 fw-bold text-primary mt-2 mb-0">{metrics.total_babies}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Newborn infants
            </div>
          </div>
        </div>

        <div className="col-6 col-md-4 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Gender Ratio
            </div>
            <div className="d-flex align-items-baseline gap-2 mt-2 mb-0">
              <span className="fw-bold text-info" style={{ fontSize: '1.25rem' }}>
                <i className="bi bi-gender-male"></i> {metrics.male_babies}
              </span>
              <span className="text-muted">&bull;</span>
              <span className="fw-bold text-danger" style={{ fontSize: '1.25rem' }}>
                <i className="bi bi-gender-female"></i> {metrics.female_babies}
              </span>
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Male / Female
            </div>
          </div>
        </div>

        <div className="col-6 col-md-6 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Mode of Delivery
            </div>
            <div className="d-flex align-items-baseline gap-2 mt-2 mb-0">
              <span className="fw-bold text-dark" style={{ fontSize: '1.25rem' }}>
                {metrics.lscs_count} <span className="small text-muted" style={{ fontSize: '12px' }}>LSCS</span>
              </span>
              <span className="text-muted">/</span>
              <span className="fw-bold text-success" style={{ fontSize: '1.25rem' }}>
                {metrics.normal_count} <span className="small text-muted" style={{ fontSize: '12px' }}>Normal</span>
              </span>
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Caesarean vs Vaginal
            </div>
          </div>
        </div>

        <div className="col-12 col-md-6 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Avg. Birth Weight
            </div>
            <div className="h3 fw-bold text-dark mt-2 mb-0">
              {metrics.avg_weight_kg > 0 ? `${metrics.avg_weight_kg} kg` : '—'}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Newborn mean weight
            </div>
          </div>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="card border-0 shadow-sm mb-4" style={{ borderRadius: '16px' }}>
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-end">
            <div className="col-12 col-sm-6 col-md-3">
              <label className="form-label small text-muted mb-1">Search Mother / UHID / IP</label>
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="Name, UHID, IP..."
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
              <label className="form-label small text-muted mb-1">Baby Gender</label>
              <select
                className="form-select form-select-sm"
                value={babySex}
                onChange={(e) => setBabySex(e.target.value)}
              >
                <option value="">All Genders</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>

            <div className="col-6 col-sm-4 col-md-3">
              <label className="form-label small text-muted mb-1">Doctor / Obstetrician</label>
              <select
                className="form-select form-select-sm"
                value={doctor}
                onChange={(e) => setDoctor(e.target.value)}
              >
                <option value="">All Doctors</option>
                {clinicalStaff.map((doc) => (
                  <option key={doc.id} value={doc.name}>
                    {doc.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="col-12 col-sm-4 col-md-12 d-flex justify-content-end gap-2 mt-2">
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

      {/* Deliveries & Newborns Table */}
      <div className="card border-0 shadow-sm" style={{ borderRadius: '18px' }}>
        <div className="card-header bg-white py-3 px-4 border-0 d-flex align-items-center justify-content-between">
          <div className="d-flex align-items-center gap-2">
            <span className="p-2 rounded-circle bg-pink-subtle" style={{ color: '#db2777', backgroundColor: '#fce7f3' }}>
              <i className="bi bi-calendar2-heart"></i>
            </span>
            <div>
              <h5 className="card-title mb-0 fw-bold text-dark" style={{ fontSize: '15.5px' }}>
                Delivery Records &amp; Newborn Register
              </h5>
              <div className="text-muted small" style={{ fontSize: '12px' }}>
                Showing {deliveries.data.length} of {deliveries.total} records
              </div>
            </div>
          </div>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0" style={{ fontSize: '13px' }}>
            <thead className="table-light text-muted small text-uppercase" style={{ fontSize: '11px', letterSpacing: '0.04em' }}>
              <tr>
                <th className="ps-4">Mother Details</th>
                <th>Admission / Delivery</th>
                <th>Procedure / Obstetrician</th>
                <th>Newborn Baby Details</th>
                <th>Status</th>
                <th className="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              {deliveries.data.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center py-5 text-muted">
                    <i className="bi bi-folder-x fs-2 d-block mb-2 text-muted"></i>
                    No delivery records found for the selected criteria.
                  </td>
                </tr>
              ) : (
                deliveries.data.map((item) => (
                  <tr key={item.id}>
                    <td className="ps-4">
                      <div className="fw-bold text-dark">{item.patient_name}</div>
                      <div className="text-muted small">
                        UHID: <span className="font-monospace text-dark">{item.uhid}</span> &bull; IP: <span className="font-monospace text-dark">{item.ip_no}</span>
                        {item.age && ` &bull; ${item.age} Yrs`}
                      </div>
                    </td>
                    <td>
                      <div>{item.procedure_at ? formatDateTime(item.procedure_at) : (item.admission_at ? formatDate(item.admission_at) : '—')}</div>
                      {item.discharge_at && (
                        <div className="text-muted small" style={{ fontSize: '11.5px' }}>
                          Discharged: {formatDate(item.discharge_at)}
                        </div>
                      )}
                    </td>
                    <td>
                      <div className="fw-semibold text-dark">
                        {item.procedure_done || item.procedure_name || 'Delivery'}
                      </div>
                      <div className="text-muted small" style={{ fontSize: '11.5px' }}>
                        Obs: {item.surgeon || item.consultant || '—'}
                        {item.pediatrician_name && ` | Ped: ${item.pediatrician_name}`}
                      </div>
                    </td>
                    <td>
                      {item.newborns && item.newborns.length > 0 ? (
                        <div className="d-flex flex-column gap-1">
                          {item.newborns.map((b, idx) => (
                            <div key={b.id || idx} className="d-flex align-items-center gap-2">
                              <span
                                className={`badge ${
                                  b.sex === 'male' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'
                                }`}
                                style={{ fontSize: '11px', textTransform: 'capitalize' }}
                              >
                                {b.sex === 'male' ? 'Boy' : 'Girl'}
                              </span>
                              <span className="small text-dark fw-medium">
                                {b.birth_weight_kg ? `${b.birth_weight_kg} kg` : '—'}
                              </span>
                              {(b.apgar_1_min || b.apgar_5_min) && (
                                <span className="text-muted small" style={{ fontSize: '10.5px' }}>
                                  (APGAR: {b.apgar_1_min || '—'}/{b.apgar_5_min || '—'})
                                </span>
                              )}
                            </div>
                          ))}
                        </div>
                      ) : (
                        <span className="text-muted italic small">No newborn record</span>
                      )}
                    </td>
                    <td>
                      <span className="badge bg-light text-secondary border text-uppercase" style={{ fontSize: '10px' }}>
                        {item.status.replace(/_/g, ' ')}
                      </span>
                    </td>
                    <td className="text-end pe-4">
                      <Link
                        href={`/admissions/${item.id}/documents/preview`}
                        className="btn btn-sm btn-outline-secondary"
                        style={{ borderRadius: '8px', fontSize: '12px' }}
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
          <Pagination links={deliveries.links} />
        </div>
      </div>
    </AppLayout>
  );
}
