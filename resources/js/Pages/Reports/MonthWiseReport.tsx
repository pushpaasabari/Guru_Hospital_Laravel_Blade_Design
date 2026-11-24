import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { formatDate } from '../../utils/dateFormatter';

interface MonthlyStat {
  month_num: number;
  month_name: string;
  total_admissions: number;
  deliveries_count: number;
  surgeries_count: number;
  medical_count: number;
  discharged_count: number;
  insurance_count: number;
  cash_count: number;
}

interface AdmissionItem {
  id: number;
  uhid: string;
  ip_no: string;
  patient_name: string;
  age?: string;
  sex?: string;
  admission_at?: string;
  discharge_at?: string;
  format?: string;
  surgery_type?: string;
  consultant?: string;
  department?: string;
  ward_room_no?: string;
  status: string;
}

interface Props {
  monthlyStats: MonthlyStat[];
  yearSummary: {
    year: number;
    total_admissions: number;
    total_deliveries: number;
    total_surgeries: number;
    total_medical: number;
    total_discharged: number;
  };
  admissions: {
    data: AdmissionItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    current_page: number;
    last_page: number;
  };
  filters: {
    year: number;
    month: string;
    search?: string;
    surgery_type?: string;
    status?: string;
    per_page?: number;
  };
  availableYears: number[];
}

export default function MonthWiseReport({
  monthlyStats,
  yearSummary,
  admissions,
  filters,
  availableYears,
}: Props) {
  const [selectedYear, setSelectedYear] = useState<number>(filters.year);
  const [selectedMonth, setSelectedMonth] = useState<string>(filters.month || 'all');
  const [search, setSearch] = useState<string>(filters.search || '');
  const [surgeryType, setSurgeryType] = useState<string>(filters.surgery_type || '');

  const handleYearChange = (year: number) => {
    setSelectedYear(year);
    router.get(
      '/reports/month-wise',
      { year, month: selectedMonth, search, surgery_type: surgeryType },
      { preserveState: true }
    );
  };

  const handleMonthSelect = (m: string) => {
    setSelectedMonth(m);
    router.get(
      '/reports/month-wise',
      { year: selectedYear, month: m, search, surgery_type: surgeryType },
      { preserveState: true }
    );
  };

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      '/reports/month-wise',
      { year: selectedYear, month: selectedMonth, search, surgery_type: surgeryType },
      { preserveState: true }
    );
  };

  const handleReset = () => {
    setSearch('');
    setSurgeryType('');
    setSelectedMonth('all');
    router.get('/reports/month-wise', { year: selectedYear });
  };

  const handleExportCsv = () => {
    const params = new URLSearchParams({
      year: String(selectedYear),
      month: selectedMonth,
      search,
      surgery_type: surgeryType,
      export: 'csv',
    });
    window.location.href = `/reports/month-wise?${params.toString()}`;
  };

  return (
    <AppLayout>
      <Head title={`Month Wise Report (${selectedYear}) — Guru Hospital`} />

      {/* Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <div className="d-flex align-items-center gap-2 mb-1">
            <span className="p-2 rounded-circle" style={{ background: '#e0e7ff', color: '#4f46e5' }}>
              <i className="bi bi-calendar3-range fs-5"></i>
            </span>
            <h1 className="h3 fw-bold mb-0 text-dark" style={{ letterSpacing: '-0.02em' }}>
              Month Wise Report
            </h1>
          </div>
          <p className="text-muted mb-0" style={{ fontSize: '13.5px' }}>
            Comprehensive annual hospital census, clinical admissions breakdown &amp; monthly metrics
          </p>
        </div>

        <div className="d-flex align-items-center gap-2">
          {/* Year Picker Dropdown */}
          <div className="d-flex align-items-center gap-1 bg-white border p-1 rounded-3 shadow-sm">
            <span className="text-muted small ps-2 fw-semibold" style={{ fontSize: '12px' }}>
              Year:
            </span>
            <select
              className="form-select form-select-sm border-0 shadow-none fw-bold text-primary"
              style={{ width: '90px', fontSize: '13px' }}
              value={selectedYear}
              onChange={(e) => handleYearChange(Number(e.target.value))}
            >
              {availableYears.map((yr) => (
                <option key={yr} value={yr}>
                  {yr}
                </option>
              ))}
            </select>
          </div>

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

      {/* 5 Annual Metric Cards for Selected Year */}
      <div className="row g-3 mb-4">
        <div className="col-6 col-md-4 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Annual Inpatients
            </div>
            <div className="h3 fw-bold text-dark mt-2 mb-0">{yearSummary.total_admissions}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Admissions in {yearSummary.year}
            </div>
          </div>
        </div>

        <div className="col-6 col-md-4 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Annual Deliveries
            </div>
            <div className="h3 fw-bold text-pink mt-2 mb-0" style={{ color: '#db2777' }}>
              {yearSummary.total_deliveries}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Maternal cases
            </div>
          </div>
        </div>

        <div className="col-6 col-md-4 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Annual Surgeries
            </div>
            <div className="h3 fw-bold text-warning mt-2 mb-0" style={{ color: '#d97706' }}>
              {yearSummary.total_surgeries}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              OT procedures
            </div>
          </div>
        </div>

        <div className="col-6 col-md-6 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Medical Cases
            </div>
            <div className="h3 fw-bold text-success mt-2 mb-0" style={{ color: '#16a34a' }}>
              {yearSummary.total_medical}
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Non-surgical management
            </div>
          </div>
        </div>

        <div className="col-12 col-md-6 col-lg">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Total Discharged
            </div>
            <div className="h3 fw-bold text-primary mt-2 mb-0">{yearSummary.total_discharged}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Discharged patients
            </div>
          </div>
        </div>
      </div>

      {/* Month-by-Month Statistical Table */}
      <div className="card border-0 shadow-sm mb-4" style={{ borderRadius: '18px' }}>
        <div className="card-header bg-white py-3 px-4 border-0 d-flex align-items-center justify-content-between">
          <div className="d-flex align-items-center gap-2">
            <span className="p-2 rounded-circle" style={{ color: '#4f46e5', backgroundColor: '#e0e7ff' }}>
              <i className="bi bi-bar-chart-steps"></i>
            </span>
            <div>
              <h5 className="card-title mb-0 fw-bold text-dark" style={{ fontSize: '15.5px' }}>
                Month-by-Month Census Summary ({selectedYear})
              </h5>
              <div className="text-muted small" style={{ fontSize: '12px' }}>
                Click any month to inspect individual patient dossiers
              </div>
            </div>
          </div>

          <button
            type="button"
            onClick={() => handleMonthSelect('all')}
            className={`btn btn-sm ${selectedMonth === 'all' ? 'btn-primary' : 'btn-outline-secondary'}`}
            style={{ borderRadius: '8px', fontSize: '12.5px' }}
          >
            Show All Months
          </button>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0 text-center" style={{ fontSize: '13px' }}>
            <thead className="table-light text-muted small text-uppercase" style={{ fontSize: '11px', letterSpacing: '0.04em' }}>
              <tr>
                <th className="text-start ps-4">Month</th>
                <th>Total Inpatients</th>
                <th>Deliveries</th>
                <th>Surgeries</th>
                <th>Medical Cases</th>
                <th>Discharged</th>
                <th>Insurance / Cash</th>
                <th className="text-end pe-4">Action</th>
              </tr>
            </thead>
            <tbody>
              {monthlyStats.map((item) => {
                const isSelected = selectedMonth === String(item.month_num);
                return (
                  <tr
                    key={item.month_num}
                    className={isSelected ? 'table-primary bg-primary-subtle' : ''}
                    style={{ cursor: 'pointer' }}
                    onClick={() => handleMonthSelect(String(item.month_num))}
                  >
                    <td className="text-start ps-4 fw-bold text-dark">
                      <span className="me-2">
                        {isSelected ? (
                          <i className="bi bi-check-circle-fill text-primary"></i>
                        ) : (
                          <i className="bi bi-calendar-month text-muted"></i>
                        )}
                      </span>
                      {item.month_name}
                    </td>
                    <td>
                      <span className="badge bg-light text-dark border px-2 py-1" style={{ fontSize: '12px' }}>
                        {item.total_admissions}
                      </span>
                    </td>
                    <td>
                      <span className="text-pink fw-semibold" style={{ color: '#db2777' }}>
                        {item.deliveries_count}
                      </span>
                    </td>
                    <td>
                      <span className="text-warning fw-semibold" style={{ color: '#d97706' }}>
                        {item.surgeries_count}
                      </span>
                    </td>
                    <td>
                      <span className="text-success fw-semibold" style={{ color: '#16a34a' }}>
                        {item.medical_count}
                      </span>
                    </td>
                    <td>
                      <span className="text-secondary fw-semibold">{item.discharged_count}</span>
                    </td>
                    <td>
                      <span className="small text-muted">
                        {item.insurance_count} Ins / {item.cash_count} Cash
                      </span>
                    </td>
                    <td className="text-end pe-4">
                      <button
                        type="button"
                        className={`btn btn-sm ${isSelected ? 'btn-primary' : 'btn-outline-primary'}`}
                        style={{ borderRadius: '6px', fontSize: '11.5px', padding: '3px 10px' }}
                      >
                        {isSelected ? 'Viewing' : 'View Patients'}
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* Selected Month / Year Detailed Dossiers Section */}
      <div className="card border-0 shadow-sm" style={{ borderRadius: '18px' }}>
        <div className="card-header bg-white py-3 px-4 border-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div className="d-flex align-items-center gap-2">
            <span className="p-2 rounded-circle bg-light text-dark">
              <i className="bi bi-people"></i>
            </span>
            <div>
              <h5 className="card-title mb-0 fw-bold text-dark" style={{ fontSize: '15.5px' }}>
                {selectedMonth !== 'all'
                  ? `${monthlyStats[Number(selectedMonth) - 1]?.month_name || ''} ${selectedYear} Inpatients`
                  : `All Admissions for ${selectedYear}`}
              </h5>
              <div className="text-muted small" style={{ fontSize: '12px' }}>
                Showing {admissions.data.length} of {admissions.total} matching dossiers
              </div>
            </div>
          </div>

          {/* Quick Search and Workflow Filter */}
          <form onSubmit={handleFilter} className="d-flex align-items-center gap-2">
            <input
              type="text"
              className="form-control form-control-sm shadow-none"
              placeholder="Search name, UHID, IP..."
              style={{ width: '200px', borderRadius: '8px' }}
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
            <select
              className="form-select form-select-sm shadow-none"
              style={{ width: '130px', borderRadius: '8px' }}
              value={surgeryType}
              onChange={(e) => setSurgeryType(e.target.value)}
            >
              <option value="">All Types</option>
              <option value="delivery">Delivery</option>
              <option value="other">Other Surgery</option>
              <option value="medical">Medical</option>
            </select>
            <button type="submit" className="btn btn-sm btn-primary" style={{ borderRadius: '8px' }}>
              <i className="bi bi-search"></i>
            </button>
            {(search || surgeryType || selectedMonth !== 'all') && (
              <button
                type="button"
                onClick={handleReset}
                className="btn btn-sm btn-light border"
                style={{ borderRadius: '8px' }}
                title="Reset filters"
              >
                Reset
              </button>
            )}
          </form>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0" style={{ fontSize: '13px' }}>
            <thead className="table-light text-muted small text-uppercase" style={{ fontSize: '11px', letterSpacing: '0.04em' }}>
              <tr>
                <th className="ps-4">Patient Details</th>
                <th>Admission / Discharge</th>
                <th>Format &amp; Workflow</th>
                <th>Consultant &amp; Ward</th>
                <th>Status</th>
                <th className="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              {admissions.data.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center py-5 text-muted">
                    <i className="bi bi-folder-x fs-2 d-block mb-2 text-muted"></i>
                    No patient records found for this period.
                  </td>
                </tr>
              ) : (
                admissions.data.map((item) => (
                  <tr key={item.id}>
                    <td className="ps-4">
                      <div className="fw-bold text-dark">{item.patient_name}</div>
                      <div className="text-muted small">
                        UHID: <span className="font-monospace text-dark">{item.uhid}</span> &bull; IP: <span className="font-monospace text-dark">{item.ip_no}</span>
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
                          ? 'Delivery'
                          : item.surgery_type === 'medical'
                            ? 'Medical'
                            : 'Other Surgery'}
                      </span>
                    </td>
                    <td>
                      <div className="text-dark fw-medium" style={{ fontSize: '12.5px' }}>
                        {item.consultant || '—'}
                      </div>
                      <div className="text-muted small" style={{ fontSize: '11.5px' }}>
                        {item.department || '—'} {item.ward_room_no && `(${item.ward_room_no})`}
                      </div>
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
          <Pagination links={admissions.links} />
        </div>
      </div>
    </AppLayout>
  );
}
