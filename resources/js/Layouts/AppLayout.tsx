import { ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import Sidebar from '../Components/Sidebar';

interface Props {
  children: ReactNode;
}

export default function AppLayout({ children }: Props) {
  const { flash, errors } = usePage<{
    flash: { status?: string; error?: string };
    errors: Record<string, string>;
  }>().props;

  const hasErrors = Object.keys(errors || {}).length > 0;

  return (
    <div className="fg-app-shell">
      <Sidebar />

      <div className="fg-main-wrapper">
        <main className="fg-main-content">
          {flash?.status && (
            <div
              className="alert alert-success alert-dismissible fade show no-print shadow-sm"
              role="alert"
            >
              <i className="bi bi-check-circle-fill me-2"></i> {flash.status}
            </div>
          )}

          {flash?.error && (
            <div
              className="alert alert-danger alert-dismissible fade show no-print shadow-sm"
              role="alert"
            >
              <i className="bi bi-exclamation-triangle-fill me-2"></i> {flash.error}
            </div>
          )}

          {hasErrors && (
            <div className="alert alert-danger no-print shadow-sm">
              <strong className="d-flex align-items-center mb-1">
                <i className="bi bi-x-octagon-fill me-2"></i> Please review and correct the following:
              </strong>
              <ul className="mb-0 ps-3">
                {Object.values(errors).map((err, i) => (
                  <li key={i}>{err}</li>
                ))}
              </ul>
            </div>
          )}

          {children}
        </main>
      </div>
    </div>
  );
}
