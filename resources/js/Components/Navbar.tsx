import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';

interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: string;
  doctor_reg_no?: string;
  department?: string;
  is_admin?: boolean;
  can_edit?: boolean;
  can_approve?: boolean;
  can_view_audit?: boolean;
  can_generate_pdf?: boolean;
}

export default function Navbar() {
  const { auth } = usePage<{ auth: { user: AuthUser | null } }>().props;
  const user = auth.user;
  const [mobileOpen, setMobileOpen] = useState(false);

  const handleLogout = (e: React.FormEvent) => {
    e.preventDefault();
    router.post('/logout');
  };

  const currentPath = window.location.pathname;

  return (
    <header className="fg-floating-nav-wrap no-print">
      <nav className="fg-floating-nav">
        <Link href="/dashboard" className="hospital-brand-logo">
          <span className="hospital-brand-badge">
            <i className="bi bi-hospital"></i>
          </span>
          <div>
            <div className="hospital-brand-title">GURU HOSPITAL</div>
            <div className="hospital-brand-subtitle">Clinical Workflow</div>
          </div>
        </Link>

        {user && (
          <ul className="fg-nav-links d-none d-lg-flex">
            <li>
              <Link
                href="/dashboard"
                className={`fg-nav-link ${currentPath === '/dashboard' ? 'active' : ''}`}
              >
                <i className="bi bi-grid"></i> Dashboard
              </Link>
            </li>
            <li>
              <Link
                href="/admissions"
                className={`fg-nav-link ${currentPath.startsWith('/admissions') && currentPath !== '/admissions/create' ? 'active' : ''}`}
              >
                <i className="bi bi-folder2-open"></i> Admissions
              </Link>
            </li>
            {user.can_edit && (
              <li>
                <Link
                  href="/admissions/create"
                  className={`fg-nav-link ${currentPath === '/admissions/create' ? 'active' : ''}`}
                >
                  <i className="bi bi-plus-circle"></i> New Admission
                </Link>
              </li>
            )}
            {user.is_admin && (
              <>
                <li>
                  <Link
                    href="/templates"
                    className={`fg-nav-link ${currentPath.startsWith('/templates') ? 'active' : ''}`}
                  >
                    <i className="bi bi-file-earmark-code"></i> Templates
                  </Link>
                </li>
                <li>
                  <Link
                    href="/users"
                    className={`fg-nav-link ${currentPath.startsWith('/users') ? 'active' : ''}`}
                  >
                    <i className="bi bi-people"></i> Staff Users
                  </Link>
                </li>
              </>
            )}
            {user.can_view_audit && (
              <li>
                <Link
                  href="/audit"
                  className={`fg-nav-link ${currentPath.startsWith('/audit') ? 'active' : ''}`}
                >
                  <i className="bi bi-shield-check"></i> Audit Trail
                </Link>
              </li>
            )}
          </ul>
        )}

        {user && (
          <div className="d-flex align-items-center gap-2">
            <div className="fg-user-chip d-none d-sm-inline-flex">
              <span className="fg-user-avatar">{user.name.charAt(0).toUpperCase()}</span>
              <div className="fg-user-info">
                <div className="fg-user-name">{user.name}</div>
                <div className="fg-user-role">{user.role?.replace(/_/g, ' ')}</div>
              </div>
            </div>
            <form onSubmit={handleLogout} className="d-inline">
              <button type="submit" className="btn btn-sm btn-outline-danger" title="Logout">
                <i className="bi bi-box-arrow-right"></i> <span className="d-none d-md-inline">Logout</span>
              </button>
            </form>
            <button
              className="btn btn-sm btn-outline-secondary d-lg-none"
              type="button"
              onClick={() => setMobileOpen(!mobileOpen)}
              aria-label="Toggle navigation"
            >
              <i className="bi bi-list fs-6"></i>
            </button>
          </div>
        )}
      </nav>

      {user && mobileOpen && (
        <div className="d-lg-none mt-2 card p-3 shadow-sm border">
          <ul className="nav flex-column gap-1">
            <li className="nav-item">
              <Link
                href="/dashboard"
                className={`nav-link py-2 ${currentPath === '/dashboard' ? 'active fw-bold' : ''}`}
                onClick={() => setMobileOpen(false)}
              >
                <i className="bi bi-grid me-2"></i> Dashboard
              </Link>
            </li>
            <li className="nav-item">
              <Link
                href="/admissions"
                className={`nav-link py-2 ${currentPath.startsWith('/admissions') && currentPath !== '/admissions/create' ? 'active fw-bold' : ''}`}
                onClick={() => setMobileOpen(false)}
              >
                <i className="bi bi-folder2-open me-2"></i> Admissions
              </Link>
            </li>
            {user.can_edit && (
              <li className="nav-item">
                <Link
                  href="/admissions/create"
                  className={`nav-link py-2 ${currentPath === '/admissions/create' ? 'active fw-bold' : ''}`}
                  onClick={() => setMobileOpen(false)}
                >
                  <i className="bi bi-plus-circle me-2"></i> New Admission
                </Link>
              </li>
            )}
            {user.is_admin && (
              <>
                <li className="nav-item">
                  <Link
                    href="/templates"
                    className={`nav-link py-2 ${currentPath.startsWith('/templates') ? 'active fw-bold' : ''}`}
                    onClick={() => setMobileOpen(false)}
                  >
                    <i className="bi bi-file-earmark-code me-2"></i> Templates
                  </Link>
                </li>
                <li className="nav-item">
                  <Link
                    href="/users"
                    className={`nav-link py-2 ${currentPath.startsWith('/users') ? 'active fw-bold' : ''}`}
                    onClick={() => setMobileOpen(false)}
                  >
                    <i className="bi bi-people me-2"></i> Staff Users
                  </Link>
                </li>
              </>
            )}
            {user.can_view_audit && (
              <li className="nav-item">
                <Link
                  href="/audit"
                  className={`nav-link py-2 ${currentPath.startsWith('/audit') ? 'active fw-bold' : ''}`}
                  onClick={() => setMobileOpen(false)}
                >
                  <i className="bi bi-shield-check me-2"></i> Audit Trail
                </Link>
              </li>
            )}
          </ul>
        </div>
      )}
    </header>
  );
}
