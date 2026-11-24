import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import AdmissionsDataTable, { AdmissionItem } from '../Components/AdmissionsDataTable';

interface PaginationProps {
  data: AdmissionItem[];
  current_page: number;
  last_page: number;
  total: number;
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
  counts: {
    total: number;
    draft: number;
    ready_for_review: number;
    returned_for_correction: number;
    approved: number;
    generated: number;
  };
  admissions: PaginationProps;
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

export default function Dashboard({ counts, admissions, filters = {}, auth }: Props) {
  // Dynamic greeting based on current time
  const currentHour = new Date().getHours();
  const greeting = currentHour < 12 ? 'Good Morning!' : currentHour < 18 ? 'Good Afternoon!' : 'Good Evening!';

  return (
    <AppLayout>
      <Head title="Clinical Document Workflow Dashboard — Guru Hospital" />

      {/* Top Greeting Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            {greeting}
          </h1>
          <p className="text-muted mb-0" style={{ fontSize: '13.5px' }}>
            Here's what's happening in your organization today.
          </p>
        </div>
        {auth.user?.can_edit && (
          <Link href="/admissions/create" className="btn btn-primary shadow-sm" style={{ borderRadius: '10px' }}>
            <i className="bi bi-plus-circle me-1"></i> New Admission
          </Link>
        )}
      </div>

      {/* 4 Metric Stats Cards */}
      <div className="row g-3 mb-4">
        <div className="col-6 col-lg-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '18px' }}>
            <div className="d-flex align-items-center justify-content-between mb-2">
              <span className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px', letterSpacing: '0.05em' }}>
                Total Patients
              </span>
              <span className="p-2 rounded-circle" style={{ background: '#eff6ff', color: '#2563eb' }}>
                <i className="bi bi-people-fill"></i>
              </span>
            </div>
            <div className="h3 fw-bold mb-1 text-dark">{counts.total}</div>
            <div className="text-muted small" style={{ fontSize: '12px' }}>Total Inpatient Records</div>
          </div>
        </div>

        <div className="col-6 col-lg-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '18px' }}>
            <div className="d-flex align-items-center justify-content-between mb-2">
              <span className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px', letterSpacing: '0.05em' }}>
                Review Pending
              </span>
              <span className="p-2 rounded-circle" style={{ background: '#fffbeb', color: '#d97706' }}>
                <i className="bi bi-clock-history"></i>
              </span>
            </div>
            <div className="h3 fw-bold mb-1 text-dark">{counts.ready_for_review}</div>
            <div className="text-muted small" style={{ fontSize: '12px' }}>Awaiting Doctor Approval</div>
          </div>
        </div>

        <div className="col-6 col-lg-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '18px' }}>
            <div className="d-flex align-items-center justify-content-between mb-2">
              <span className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px', letterSpacing: '0.05em' }}>
                Approved &amp; Ready
              </span>
              <span className="p-2 rounded-circle" style={{ background: '#f0fdf4', color: '#16a34a' }}>
                <i className="bi bi-check2-circle"></i>
              </span>
            </div>
            <div className="h3 fw-bold mb-1 text-dark">{counts.approved + counts.generated}</div>
            <div className="text-muted small" style={{ fontSize: '12px' }}>Clinically Signed Packs</div>
          </div>
        </div>

        <div className="col-6 col-lg-3">
          <div className="card h-100 border-0 shadow-sm p-3" style={{ borderRadius: '18px' }}>
            <div className="d-flex align-items-center justify-content-between mb-2">
              <span className="text-muted small text-uppercase fw-semibold" style={{ fontSize: '11px', letterSpacing: '0.05em' }}>
                Drafts Inactive
              </span>
              <span className="p-2 rounded-circle" style={{ background: '#f8fafc', color: '#64748b' }}>
                <i className="bi bi-pencil-square"></i>
              </span>
            </div>
            <div className="h3 fw-bold mb-1 text-dark">{counts.draft}</div>
            <div className="text-muted small" style={{ fontSize: '12px' }}>Incomplete Clinical Intake</div>
          </div>
        </div>
      </div>

      {/* Dynamic Inpatient Admissions DataTable */}
      <AdmissionsDataTable
        admissions={admissions}
        counts={counts}
        filters={filters}
        auth={auth}
      />
    </AppLayout>
  );
}
