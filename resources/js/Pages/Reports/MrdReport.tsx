import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { formatDate } from '../../utils/dateFormatter';
import { formatPaymentSummary } from '../../utils/dischargeFormatter';
import { resolveMrdNo } from '../../utils/inputNormalizers';
import { downloadReportFile } from '../../utils/reportDownload';

interface MrdRecord {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  mrd_no?: string | null;
  resolved_mrd_no?: string | null;
  age?: string | null;
  sex?: string | null;
  admission_at?: string | null;
  discharge_at?: string | null;
  format?: string | null;
  surgery_type: string;
  diagnosis?: string | null;
  icd_code?: string | null;
  procedure_done?: string | null;
  procedure_name?: string | null;
  consultant?: string | null;
  department?: string | null;
  payment_mode?: string | null;
  payment_summary?: string | null;
  insurance_type?: string | null;
  insurance_company?: string | null;
  policy_number?: string | null;
  pre_auth_no?: string | null;
  pre_auth_status?: string | null;
  handover_name?: string | null;
  floor_incharge_name?: string | null;
  mrd_incharge_name?: string | null;
  status: string;
}

interface Props {
  records: {
    data: MrdRecord[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    total: number;
    current_page: number;
    per_page: number;
    last_page: number;
  };
  metrics: {
    total_files: number;
    discharged_count: number;
    icd_coded_count: number;
    mrd_signed_count: number;
    generated_count: number;
    insurance_count: number;
    cash_count: number;
  };
  filters: {
    date_from?: string;
    date_to?: string;
    date_basis?: string;
    search?: string;
    payment_mode?: string;
    surgery_type?: string;
    status?: string;
    handover_status?: string;
    per_page?: number;
  };
}

export default function MrdReport({ records, metrics, filters }: Props) {
  const [dateFrom, setDateFrom] = useState(filters.date_from || '');
  const [dateTo, setDateTo] = useState(filters.date_to || '');
  const [dateBasis, setDateBasis] = useState(filters.date_basis || 'admission');
  const [search, setSearch] = useState(filters.search || '');
  const [paymentMode, setPaymentMode] = useState(filters.payment_mode || '');
  const [surgeryType, setSurgeryType] = useState(filters.surgery_type || '');
  const [status, setStatus] = useState(filters.status || '');
  const [handoverStatus, setHandoverStatus] = useState(filters.handover_status || '');
  const [downloadingFormat, setDownloadingFormat] = useState<'image' | 'pdf' | null>(null);

  const buildQueryParams = () => ({
    date_from: dateFrom,
    date_to: dateTo,
    date_basis: dateBasis,
    search,
    payment_mode: paymentMode,
    surgery_type: surgeryType,
    status,
    handover_status: handoverStatus,
  });

  const handleFilter = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/reports/mrd', buildQueryParams(), { preserveState: true });
  };

  const handleReset = () => {
    setDateFrom('');
    setDateTo('');
    setDateBasis('admission');
    setSearch('');
    setPaymentMode('');
    setSurgeryType('');
    setStatus('');
    setHandoverStatus('');
    router.get('/reports/mrd');
  };

  const buildExportUrl = (formatType: 'csv' | 'image' | 'pdf') => {
    const params = new URLSearchParams();
    Object.entries(buildQueryParams()).forEach(([k, v]) => {
      if (v !== '' && v !== undefined && v !== null) {
        params.set(k, String(v));
      }
    });
    params.set('export', formatType);
    return `/reports/mrd?${params.toString()}`;
  };

  const handleExportCsv = () => {
    window.location.href = buildExportUrl('csv');
  };

  const handleDownloadBinary = async (formatType: 'image' | 'pdf') => {
    setDownloadingFormat(formatType);
    try {
      const ext = formatType === 'pdf' ? 'pdf' : 'png';
      await downloadReportFile(buildExportUrl(formatType), `mrd_report.${ext}`);
    } catch {
      alert(`Failed to generate MRD Report ${formatType.toUpperCase()}. Please try again.`);
    } finally {
      setDownloadingFormat(null);
    }
  };

  const startIndex = ((records.current_page || 1) - 1) * (records.per_page || 50);

  return (
    <AppLayout>
      <Head title="MRD Report — Guru Hospital" />

      {/* Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <div className="d-flex align-items-center gap-2 mb-1">
            <span className="p-2 rounded-circle" style={{ background: '#dcfce7', color: '#15803d' }}>
              <i className="bi bi-folder-check fs-5"></i>
            </span>
            <h1 className="h3 fw-bold mb-0 text-dark" style={{ letterSpacing: '-0.02em' }}>
              MRD Report
            </h1>
          </div>
          <p className="text-muted mb-0" style={{ fontSize: '13.5px' }}>
            Medical Records Department inpatient file index, ICD-10 coding status, payment mode &amp; handover tracking
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
              Total MRD Files
            </div>
            <div className="h3 fw-bold text-dark mt-2 mb-0">{metrics.total_files}</div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Matching inpatient records
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Discharged / ICD Coded
            </div>
            <div className="h3 fw-bold mt-2 mb-0" style={{ color: '#0f5c63' }}>
              {metrics.discharged_count}{' '}
              <span className="fs-6 fw-normal text-muted">/ {metrics.icd_coded_count} ICD</span>
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Completed discharge &amp; ICD coding
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              MRD Signed / Archived
            </div>
            <div className="h3 fw-bold mt-2 mb-0" style={{ color: '#16a34a' }}>
              {metrics.mrd_signed_count}{' '}
              <span className="fs-6 fw-normal text-muted">/ {metrics.generated_count} PDF</span>
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              MRD incharge verified &amp; generated
            </div>
          </div>
        </div>

        <div className="col-6 col-md-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '16px' }}>
            <div className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px' }}>
              Insurance / Cash
            </div>
            <div className="h3 fw-bold text-primary mt-2 mb-0">
              {metrics.insurance_count}{' '}
              <span className="fs-6 fw-normal text-muted">Ins / {metrics.cash_count} Cash</span>
            </div>
            <div className="text-muted small mt-1" style={{ fontSize: '11.5px' }}>
              Billing &amp; scheme breakdown
            </div>
          </div>
        </div>
      </div>

      {/* Filter Bar */}
      <div className="card border-0 shadow-sm mb-4 no-print" style={{ borderRadius: '16px' }}>
        <div className="card-body p-3">
          <form onSubmit={handleFilter} className="row g-2 align-items-end">
            <div className="col-12 col-md-3">
              <label className="form-label small text-muted mb-1">
                Search MRD / Name / UHID / IP / ICD
              </label>
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="MRD-..., Name, UHID, ICD-10..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">From Date</label>
              <input
                type="date"
                className="form-control form-control-sm"
                value={dateFrom}
                onChange={(e) => setDateFrom(e.target.value)}
              />
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">To Date</label>
              <input
                type="date"
                className="form-control form-control-sm"
                value={dateTo}
                onChange={(e) => setDateTo(e.target.value)}
              />
            </div>

            <div className="col-6 col-md-2">
              <label className="form-label small text-muted mb-1">Date Basis</label>
              <select
                className="form-select form-select-sm"
                value={dateBasis}
                onChange={(e) => setDateBasis(e.target.value)}
              >
                <option value="admission">Admission Date (DOA)</option>
                <option value="discharge">Discharge Date (DOD)</option>
              </select>
            </div>

            <div className="col-6 col-md-3">
              <label className="form-label small text-muted mb-1">Payment Mode</label>
              <select
                className="form-select form-select-sm"
                value={paymentMode}
                onChange={(e) => setPaymentMode(e.target.value)}
              >
                <option value="">All Payment Modes</option>
                <option value="cash">Cash / Self</option>
                <option value="insurance">Insurance / CMCHISTN</option>
              </select>
            </div>

            <div className="col-6 col-md-3">
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
              <label className="form-label small text-muted mb-1">MRD Sign-Off</label>
              <select
                className="form-select form-select-sm"
                value={handoverStatus}
                onChange={(e) => setHandoverStatus(e.target.value)}
              >
                <option value="">All Files</option>
                <option value="signed">MRD Incharge Signed</option>
                <option value="pending">Pending MRD Signature</option>
              </select>
            </div>

            <div className="col-6 col-md-3">
              <label className="form-label small text-muted mb-1">Dossier Status</label>
              <select
                className="form-select form-select-sm"
                value={status}
                onChange={(e) => setStatus(e.target.value)}
              >
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="approved">Approved</option>
                <option value="generated">Generated</option>
                <option value="returned">Returned</option>
              </select>
            </div>

            <div className="col-6 col-md-3 d-flex justify-content-end gap-2 mt-2">
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

      {/* MRD Register Table */}
      <div className="card border-0 shadow-sm" style={{ borderRadius: '18px' }}>
        <div className="card-header bg-white py-3 px-4 border-0 d-flex align-items-center justify-content-between">
          <div className="d-flex align-items-center gap-2">
            <span className="p-2 rounded-circle" style={{ color: '#15803d', backgroundColor: '#dcfce7' }}>
              <i className="bi bi-archive"></i>
            </span>
            <div>
              <h5 className="card-title mb-0 fw-bold text-dark" style={{ fontSize: '15.5px' }}>
                Medical Records Department (MRD) Register
              </h5>
              <div className="text-muted small" style={{ fontSize: '12px' }}>
                Showing {records.data.length} of {records.total} MRD records
              </div>
            </div>
          </div>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-top mb-0" style={{ fontSize: '12.5px' }}>
            <thead className="table-light text-muted small text-uppercase" style={{ fontSize: '11px', letterSpacing: '0.04em' }}>
              <tr>
                <th className="ps-3 text-center" style={{ width: '50px' }}>S.No</th>
                <th style={{ minWidth: '130px' }}>MRD File No.</th>
                <th style={{ minWidth: '180px' }}>Patient / UHID / IP No.</th>
                <th style={{ minWidth: '135px' }}>DOA &amp; DOD (LOS)</th>
                <th style={{ minWidth: '190px' }}>Diagnosis &amp; ICD Code</th>
                <th style={{ minWidth: '175px' }}>Procedure / Consultant</th>
                <th style={{ minWidth: '185px' }}>Payment / Insurance</th>
                <th style={{ minWidth: '180px' }}>MRD File Handover &amp; Sign</th>
                <th>Status</th>
                <th className="text-end pe-3 no-print">Actions</th>
              </tr>
            </thead>
            <tbody>
              {records.data.length === 0 ? (
                <tr>
                  <td colSpan={10} className="text-center py-5 text-muted">
                    <i className="bi bi-folder-x fs-2 d-block mb-2 text-muted"></i>
                    No MRD records matched the selected filter criteria.
                  </td>
                </tr>
              ) : (
                records.data.map((item, idx) => {
                  const mrdNo = item.resolved_mrd_no || resolveMrdNo(item.mrd_no, item.ip_no);
                  const paymentText = item.payment_summary || formatPaymentSummary(item);
                  const procLabel =
                    item.procedure_done || item.procedure_name || item.surgery_type.toUpperCase();
                  let losDays: number | null = null;
                  if (item.admission_at && item.discharge_at) {
                    const diff = Math.round(
                      (new Date(item.discharge_at).getTime() - new Date(item.admission_at).getTime()) /
                        86400000
                    );
                    losDays = Math.max(1, diff);
                  }

                  return (
                    <tr key={item.id}>
                      <td className="ps-3 text-center fw-semibold text-muted">
                        {startIndex + idx + 1}
                      </td>

                      <td>
                        <div className="font-monospace fw-bold text-dark">{mrdNo || '—'}</div>
                        {item.format && (
                          <span className="badge bg-light text-secondary border text-uppercase mt-1" style={{ fontSize: '10px' }}>
                            {item.format}
                          </span>
                        )}
                      </td>

                      <td>
                        <div className="fw-bold text-dark">{item.patient_name}</div>
                        <div className="text-muted small">
                          UHID: <span className="font-monospace text-dark">{item.uhid}</span>
                        </div>
                        <div className="text-muted small">
                          IP No: <span className="font-monospace text-dark">{item.ip_no}</span>
                        </div>
                        {(item.age || item.sex) && (
                          <div className="text-muted" style={{ fontSize: '11px' }}>
                            {[item.age ? `${item.age} Yrs` : null, item.sex].filter(Boolean).join(' • ')}
                          </div>
                        )}
                      </td>

                      <td className="small">
                        <div>
                          <span className="text-muted fw-semibold">DOA:</span>{' '}
                          {item.admission_at ? formatDate(item.admission_at) : '—'}
                        </div>
                        <div className="mt-1">
                          <span className="text-muted fw-semibold">DOD:</span>{' '}
                          {item.discharge_at ? (
                            <span className="text-dark fw-medium">{formatDate(item.discharge_at)}</span>
                          ) : (
                            <span className="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                              In Admission
                            </span>
                          )}
                        </div>
                        {losDays !== null && (
                          <div className="text-muted mt-1" style={{ fontSize: '11px' }}>
                            Stay: <strong>{losDays} Day(s)</strong>
                          </div>
                        )}
                      </td>

                      <td>
                        <div className="fw-medium text-dark">{item.diagnosis || '—'}</div>
                        {item.icd_code && (
                          <span className="badge bg-primary-subtle text-primary border border-primary-subtle mt-1" style={{ fontSize: '10.5px' }}>
                            ICD: {item.icd_code}
                          </span>
                        )}
                      </td>

                      <td>
                        <div className="fw-semibold text-dark">{procLabel}</div>
                        {item.consultant && (
                          <div className="text-muted small mt-1">{item.consultant}</div>
                        )}
                        {item.department && (
                          <div className="text-muted" style={{ fontSize: '11px' }}>
                            {item.department}
                          </div>
                        )}
                      </td>

                      <td className="small">
                        <div className="fw-medium text-dark">{paymentText}</div>
                      </td>

                      <td className="small">
                        <div>
                          <span className="text-muted fw-semibold">Handover:</span>{' '}
                          {item.handover_name || '—'}
                        </div>
                        <div>
                          <span className="text-muted fw-semibold">Floor Incharge:</span>{' '}
                          {item.floor_incharge_name || '—'}
                        </div>
                        <div>
                          <span className="text-muted fw-semibold">MRD Incharge:</span>{' '}
                          <span className={item.mrd_incharge_name ? 'text-success fw-semibold' : ''}>
                            {item.mrd_incharge_name || '—'}
                          </span>
                        </div>
                      </td>

                      <td>
                        <span
                          className={`badge border text-uppercase ${
                            item.status === 'generated'
                              ? 'bg-success-subtle text-success border-success-subtle'
                              : 'bg-light text-secondary'
                          }`}
                          style={{ fontSize: '10px' }}
                        >
                          {item.status.replace(/_/g, ' ')}
                        </span>
                      </td>

                      <td className="text-end pe-3 text-nowrap no-print">
                        <div className="d-inline-flex gap-1">
                          <a
                            href={`/admissions/${item.id}/documents/pdf/mrd-form`}
                            className="btn btn-sm btn-outline-success"
                            title="Download MRD Inpatient Form PDF"
                            style={{ borderRadius: '8px', fontSize: '12px' }}
                          >
                            <i className="bi bi-file-earmark-pdf me-1"></i>MRD Form
                          </a>
                          <Link
                            href={`/admissions/${item.id}/documents/preview`}
                            className="btn btn-sm btn-outline-secondary"
                            style={{ borderRadius: '8px', fontSize: '12px' }}
                          >
                            <i className="bi bi-eye me-1"></i>Review
                          </Link>
                        </div>
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
