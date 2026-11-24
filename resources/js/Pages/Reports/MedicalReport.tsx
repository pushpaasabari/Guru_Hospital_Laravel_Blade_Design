import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { formatDate } from '../../utils/dateFormatter';

interface MedicalItem {
  id: number;
  uhid: string;
  ip_no: string;
  patient_name: string;
  age?: string;
  sex?: string;
  admission_at?: string;
  discharge_at?: string;
  diagnosis?: string;
  consultant?: string;
  department?: string;
  ward_room_no?: string;
  discharge_condition?: string;
  discharge_bp?: string;
  discharge_pulse?: string;
  discharge_spo2?: string;
  payment_mode?: string;
  status: string;
}

interface Props {
  medicalCases: {
    data: MedicalItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    current_page: number;
    last_page: number;
  };
  metrics: {
    total_medical_cases: number;
    active_inpatient_count: number;
    discharged_count: number;
    avg_stay_days: number;
    top_departments: Record<string, number>;
  };
  filters: {
    date_from?: string;
    date_to?: string;
    search?: string;
    department?: string;
    consultant?: string;
    status?: string;
    payment_mode?: string;
    per_page?: number;
  };
  clinicalStaff: Array<{ id: number; name: string; category?: string }>;
}

export default function MedicalReport({ medicalCases, metrics, filters, clinicalStaff }: Props) {
  const [search, setSearch] = useState(filters.search || '');
  const [dateFrom, setDateFrom] = useState(filters.date_from || '');
  const [dateTo, setDateTo] = useState(filters.date_to || '');
  const [department, setDepartment] = useState(filters.department || '');
  const [consultant, setConsultant] = useState(filters.consultant || '');
  const [status, setStatus] = useState(filters.status || '');

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      '/reports/medical',
      { search, date_from: dateFrom, date_to: dateTo, department, consultant, status },
      { preserveState: true }
    );
  };

  const handleReset = () => {
    setSearch('');
    setDateFrom('');
    setDateTo('');
    setDepartment('');
    setConsultant('');
    setStatus('');
    router.get('/reports/medical');
  };

  const handleExportCsv = () => {
    const params = new URLSearchParams({
      search,
      date_from: dateFrom,
      date_to: dateTo,
      department,
      consultant,
      status,
      export: 'csv',
    });
    window.location.href = `/reports/medical?${params.toString()}`;
  };

  const getStayDays = (adm?: string, dis?: string) => {
    if (!adm || !dis) return 'Active';
    try {
      const d1 = new Date(adm);
      const d2 = new Date(dis);
      const diff = Math.ceil(Math.abs(d2.getTime() - d1.getTime()) / (1000 * 60 * 60 * 24));
      return diff <= 1 ? '1 Day' : `${diff} Days`;
    } catch {
      return '—';
    }
  };

  return (
    <AppLayout>
      <Head title="Medical Case Report — Guru Hospital" />

      {/* Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <div className="d-flex align-items-center gap-2 mb-1">
            <span className="p-2 rounded-circle" style={{ background: '#dcfce7', color: '#16a34a' }}>
              <i className="bi bi-heart-pulse fs-5"></i>
            </span>
            <h1 className="h3 fw-bold mb-0 text-dark" style={{ letterSpacing: '-0.02em' }}>
              Medical Case Report
            </h1>
          </div>
          <p className="text-muted mb-0" style={{ fontSize: '13.5px' }}>
            Conservative &amp; non-surgical inpatient medical management census &amp; outcomes
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
              Total Medical Cases
            </div>
            <div className="h3 fw-bold text-dark mt-2 mb-0">{metrics.total_medical_cases}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Conservative management
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Active Inpatients
            </div>
            <div className="h3 fw-bold text-primary mt-2 mb-0">{metrics.active_inpatient_count}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Currently admitted
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Discharged Patients
            </div>
            <div className="h3 fw-bold text-success mt-2 mb-0">{metrics.discharged_count}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Discharge completed
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Avg. Length of Stay
            </div>
            <div className="h3 fw-bold text-info mt-2 mb-0">
              {metrics.avg_stay_days > 0 ? `${metrics.avg_stay_days} Days` : '—'}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Mean hospital stay
            </div>
          </div>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="card border-0 shadow-sm mb-4" style={{ borderRadius: '16px' }}>
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-end">
            <div className="col-12 col-sm-6 col-md-3">
              <label className="form-label small text-muted mb-1">Search Patient / UHID / Diagnosis</label>
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="Name, UHID, Diagnosis..."
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
              <label className="form-label small text-muted mb-1">Consultant Doctor</label>
              <select
                className="form-select form-select-sm"
                value={consultant}
                onChange={(e) => setConsultant(e.target.value)}
              >
                <option value="">All Consultants</option>
                {clinicalStaff.map((doc) => (
                  <option key={doc.id} value={doc.name}>
                    {doc.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="col-6 col-sm-4 col-md-3">
              <label className="form-label small text-muted mb-1">Department</label>
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="e.g. General Medicine"
                value={department}
                onChange={(e) => setDepartment(e.target.value)}
              />
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

      {/* Medical Cases Table */}
      <div className="card border-0 shadow-sm" style={{ borderRadius: '18px' }}>
        <div className="card-header bg-white py-3 px-4 border-0 d-flex align-items-center justify-content-between">
          <div className="d-flex align-items-center gap-2">
            <span className="p-2 rounded-circle" style={{ color: '#16a34a', backgroundColor: '#dcfce7' }}>
              <i className="bi bi-file-medical"></i>
            </span>
            <div>
              <h5 className="card-title mb-0 fw-bold text-dark" style={{ fontSize: '15.5px' }}>
                Medical Cases &amp; Clinical Management Register
              </h5>
              <div className="text-muted small" style={{ fontSize: '12px' }}>
                Showing {medicalCases.data.length} of {medicalCases.total} medical cases
              </div>
            </div>
          </div>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0" style={{ fontSize: '13px' }}>
            <thead className="table-light text-muted small text-uppercase" style={{ fontSize: '11px', letterSpacing: '0.04em' }}>
              <tr>
                <th className="ps-4">Patient Details</th>
                <th>Admission / Stay</th>
                <th>Diagnosis</th>
                <th>Consultant &amp; Ward</th>
                <th>Condition at Discharge</th>
                <th>Status</th>
                <th className="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              {medicalCases.data.length === 0 ? (
                <tr>
                  <td colSpan={7} className="text-center py-5 text-muted">
                    <i className="bi bi-folder-x fs-2 d-block mb-2 text-muted"></i>
                    No medical inpatient records found for the selected criteria.
                  </td>
                </tr>
              ) : (
                medicalCases.data.map((item) => (
                  <tr key={item.id}>
                    <td className="ps-4">
                      <div className="fw-bold text-dark">{item.patient_name}</div>
                      <div className="text-muted small">
                        UHID: <span className="font-monospace text-dark">{item.uhid}</span> &bull; IP: <span className="font-monospace text-dark">{item.ip_no}</span>
                        {item.sex && ` &bull; ${item.sex.toUpperCase()}`}
                        {item.age && ` &bull; ${item.age} Yrs`}
                      </div>
                    </td>
                    <td>
                      <div>{item.admission_at ? formatDate(item.admission_at) : '—'}</div>
                      <div className="text-muted small" style={{ fontSize: '11.5px' }}>
                        Stay: <span className="badge bg-light text-dark border">{getStayDays(item.admission_at, item.discharge_at)}</span>
                      </div>
                    </td>
                    <td>
                      <div className="fw-semibold text-dark text-truncate" style={{ maxWidth: '240px' }} title={item.diagnosis}>
                        {item.diagnosis || 'Medical Evaluation'}
                      </div>
                    </td>
                    <td>
                      <div className="text-dark fw-medium" style={{ fontSize: '12.5px' }}>
                        {item.consultant || '—'}
                      </div>
                      <div className="text-muted small" style={{ fontSize: '11.5px' }}>
                        {item.department || 'General Medicine'} {item.ward_room_no && `(${item.ward_room_no})`}
                      </div>
                    </td>
                    <td>
                      {item.discharge_condition ? (
                        <div>
                          <div className="small text-dark fw-medium">{item.discharge_condition}</div>
                          {(item.discharge_bp || item.discharge_pulse || item.discharge_spo2) && (
                            <div className="text-muted small" style={{ fontSize: '10.5px' }}>
                              BP: {item.discharge_bp || '—'} | P: {item.discharge_pulse || '—'} | SpO2: {item.discharge_spo2 || '—'}%
                            </div>
                          )}
                        </div>
                      ) : (
                        <span className="text-muted small italic">Admitted / In Treatment</span>
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
          <Pagination links={medicalCases.links} />
        </div>
      </div>
    </AppLayout>
  );
}
