import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import AdmissionsDataTable, { AdmissionItem } from '../../Components/AdmissionsDataTable';

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
    superseded?: number;
    in_admission?: number;
  };
  admissions: PaginationProps;
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
}

export default function AdmissionsIndex({ counts, admissions, filters = {}, auth }: Props) {
  return (
    <AppLayout>
      <Head title="Inpatient Admissions Archive — Guru Hospital" />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Inpatient Admissions Archive
          </h1>
          <p className="text-muted mb-0" style={{ fontSize: '13.5px' }}>
            Search, filter, sort, export CSV, and manage clinical dossiers &amp; MRD records.
          </p>
        </div>
        {auth.user?.can_edit && (
          <Link href="/admissions/create" className="btn btn-primary shadow-sm" style={{ borderRadius: '10px' }}>
            <i className="bi bi-plus-circle me-1"></i> New Admission
          </Link>
        )}
      </div>

      <AdmissionsDataTable
        admissions={admissions}
        counts={counts}
        filters={filters}
        auth={auth}
        mode="archive"
        title="Admissions Registry Archive"
        subtitle="Advanced live archive with multi-field filters, column sorting, audit history &amp; direct PDFs"
      />
    </AppLayout>
  );
}
