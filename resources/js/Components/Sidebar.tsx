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

interface SidebarProps {
  collapsed?: boolean;
  onToggleCollapse?: () => void;
}

export default function Sidebar({ collapsed = false, onToggleCollapse }: SidebarProps) {
  const { auth } = usePage<{ auth: { user: AuthUser | null } }>().props;
  const user = auth.user;
  const [mobileOpen, setMobileOpen] = useState(false);

  const handleLogout = (e: React.FormEvent) => {
    e.preventDefault();
    router.post('/logout');
  };

  const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

  const isLinkActive = (path: string, exact: boolean = false) => {
    if (exact) return currentPath === path;
    if (path === '/admissions') {
      return currentPath.startsWith('/admissions') && currentPath !== '/admissions/create';
    }
    return currentPath.startsWith(path);
  };

  const closeMobile = () => setMobileOpen(false);

  return (
    <>
      {/* Mobile Topbar for screens < 992px */}
      <div className="fg-mobile-topbar no-print">
        <Link href="/dashboard" className="d-flex align-items-center text-decoration-none">
          <div className="fg-brand-mark">
            <span className="bar bar-1"></span>
            <span className="bar bar-2"></span>
            <span className="bar bar-3"></span>
          </div>
          <span className="fw-bold text-dark ms-1" style={{ fontSize: '1rem', letterSpacing: '-0.02em' }}>
            Guru Hospital
          </span>
        </Link>

        <button
          className="btn btn-sm btn-outline-secondary"
          type="button"
          onClick={() => setMobileOpen(!mobileOpen)}
          aria-label="Toggle navigation"
        >
          <i className={`bi ${mobileOpen ? 'bi-x-lg' : 'bi-list'} fs-5`}></i>
        </button>
      </div>

      {/* Mobile Backdrop Overlay */}
      {mobileOpen && (
        <div
          className="fg-sidebar-backdrop no-print"
          onClick={closeMobile}
          aria-hidden="true"
        ></div>
      )}

      {/* Left Sidebar (Desktop + Mobile Drawer) */}
      <aside className={`fg-sidebar no-print ${mobileOpen ? 'mobile-open' : ''}`}>
        <div>
          {/* Sidebar Header: Hospital Brand with FinGM 3-Bar Logo */}
          <div className="fg-sidebar-header">
            <Link
              href="/dashboard"
              className="d-flex align-items-center text-decoration-none"
              onClick={closeMobile}
              title="Guru Hospital Dashboard"
            >
              <div className="fg-brand-mark">
                <span className="bar bar-1"></span>
                <span className="bar bar-2"></span>
                <span className="bar bar-3"></span>
              </div>
              <div className="fg-sidebar-brand-text">
                <div className="hospital-brand-title" style={{ fontSize: '1.05rem', fontWeight: 700, letterSpacing: '-0.02em' }}>
                  Guru Hospital
                </div>
                <div className="hospital-brand-subtitle" style={{ fontSize: '9.5px', color: '#94a3b8' }}>
                  Clinical Workflow
                </div>
              </div>
            </Link>
          </div>

          {/* Navigation Links (With full titles for Icon-Only mode) */}
          <div className="fg-sidebar-body">
            {/* Group 1: Clinical Management */}
            <div className="fg-sidebar-group">
              <div className="fg-sidebar-group-title">Clinical Management</div>

              <Link
                href="/dashboard"
                className={`fg-sidebar-link ${isLinkActive('/dashboard', true) ? 'active' : ''}`}
                onClick={closeMobile}
                title="Dashboard"
              >
                <i className="bi bi-grid icon-dashboard"></i>
                <span>Dashboard</span>
              </Link>

              <Link
                href="/admissions"
                className={`fg-sidebar-link ${isLinkActive('/admissions') ? 'active' : ''}`}
                onClick={closeMobile}
                title="Admissions Archive"
              >
                <i className="bi bi-folder2-open icon-admissions"></i>
                <span>Admissions</span>
              </Link>

              {user?.can_edit && (
                <Link
                  href="/admissions/create"
                  className={`fg-sidebar-link ${isLinkActive('/admissions/create', true) ? 'active' : ''}`}
                  onClick={closeMobile}
                  title="New Admission"
                >
                  <i className="bi bi-plus-circle icon-create"></i>
                  <span>New Admission</span>
                </Link>
              )}
            </div>

            {/* Group 2: Governance & Administration */}
            {(user?.is_admin || user?.can_view_audit) && (
              <div className="fg-sidebar-group">
                <div className="fg-sidebar-group-title">Administration</div>

                {user.is_admin && (
                  <>
                    <Link
                      href="/templates"
                      className={`fg-sidebar-link ${isLinkActive('/templates') ? 'active' : ''}`}
                      onClick={closeMobile}
                      title="Document Templates"
                    >
                      <i className="bi bi-file-earmark-code icon-templates"></i>
                      <span>Templates</span>
                    </Link>

                    <Link
                      href="/users"
                      className={`fg-sidebar-link ${isLinkActive('/users') ? 'active' : ''}`}
                      onClick={closeMobile}
                      title="Staff Users Management"
                    >
                      <i className="bi bi-people icon-users"></i>
                      <span>Staff Users</span>
                    </Link>
                  </>
                )}

                {user.can_view_audit && (
                  <Link
                    href="/audit-logs"
                    className={`fg-sidebar-link ${isLinkActive('/audit-logs') ? 'active' : ''}`}
                    onClick={closeMobile}
                    title="Audit Trail Logs"
                  >
                    <i className="bi bi-shield-check icon-audit"></i>
                    <span>Audit Trail</span>
                  </Link>
                )}
              </div>
            )}
          </div>
        </div>

        {/* Sidebar Footer: Staff User Card & Logout */}
        {user && (
          <div className="fg-sidebar-footer">
            <div className="fg-sidebar-user-card" title={`${user.name} (${user.role?.replace(/_/g, ' ')})`}>
              <span className="fg-sidebar-user-avatar">
                {user.name.charAt(0).toUpperCase()}
              </span>
              <div className="fg-sidebar-user-meta">
                <div className="fg-sidebar-user-name" title={user.name}>
                  {user.name}
                </div>
                <div className="fg-sidebar-user-role">
                  {user.role?.replace(/_/g, ' ')}
                </div>
                {user.doctor_reg_no && (
                  <div
                    className="text-muted font-monospace"
                    style={{ fontSize: '9.5px', marginTop: '1px' }}
                  >
                    {user.doctor_reg_no}
                  </div>
                )}
              </div>
            </div>

            <form onSubmit={handleLogout}>
              <button type="submit" className="fg-sidebar-logout-btn" title="Sign Out">
                <i className="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </button>
            </form>
          </div>
        )}
      </aside>
    </>
  );
}
