import { useEffect, useRef, useState } from 'react';
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
  const { url } = usePage();
  const [mobileOpen, setMobileOpen] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);

  const handleLogout = (e: React.FormEvent) => {
    e.preventDefault();
    router.post('/logout');
  };

  const currentPath = (url || (typeof window !== 'undefined' ? window.location.pathname : '')).split('?')[0];

  const isLinkActive = (path: string, exact: boolean = false) => {
    if (exact) return currentPath === path;
    if (path === '/admissions') {
      return currentPath.startsWith('/admissions') && currentPath !== '/admissions/create';
    }
    return currentPath.startsWith(path);
  };

  const adminPaths = ['/templates', '/clinical-staff', '/anesthesia-types', '/users', '/backups', '/audit-logs'];
  const isAnyAdminActive = adminPaths.some((p) => isLinkActive(p));

  const reportPaths = ['/reports/day', '/reports/mrd', '/reports/birth', '/reports/surgery', '/reports/medical', '/reports/month-wise', '/reports'];
  const isAnyReportActive = reportPaths.some((p) => isLinkActive(p));

  const [adminMenuOpen, setAdminMenuOpen] = useState<boolean>(() => {
    if (isAnyAdminActive) return true;
    if (typeof window !== 'undefined') {
      const saved = localStorage.getItem('guru_admin_menu_open');
      if (saved !== null) return saved === 'true';
    }
    return false;
  });

  const [reportMenuOpen, setReportMenuOpen] = useState<boolean>(() => {
    if (isAnyReportActive) return true;
    if (typeof window !== 'undefined') {
      const saved = localStorage.getItem('guru_report_menu_open');
      if (saved !== null) return saved === 'true';
    }
    return false;
  });

  const reportMenuRef = useRef<HTMLDivElement>(null);

  // Keep expanded whenever an admin sub-item route is active
  useEffect(() => {
    if (isAnyAdminActive) {
      setAdminMenuOpen(true);
    }
  }, [isAnyAdminActive, currentPath]);

  // Keep expanded whenever a report sub-item route is active
  useEffect(() => {
    if (isAnyReportActive) {
      setReportMenuOpen(true);
    }
  }, [isAnyReportActive, currentPath]);

  // Click outside listener to close floating flyouts when collapsed
  useEffect(() => {
    if (!collapsed) return;
    const handleClickOutside = (e: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(e.target as Node)) {
        setAdminMenuOpen(false);
      }
      if (reportMenuRef.current && !reportMenuRef.current.contains(e.target as Node)) {
        setReportMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [collapsed]);

  // Close mobile drawer automatically on route navigation
  useEffect(() => {
    setMobileOpen(false);
  }, [currentPath]);

  // Close mobile drawer on Escape key & prevent background body scroll while open
  useEffect(() => {
    if (!mobileOpen) return;
    const prevOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        setMobileOpen(false);
      }
    };
    document.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = prevOverflow;
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [mobileOpen]);

  const toggleAdminMenu = () => {
    setAdminMenuOpen((prev) => {
      const next = !prev;
      if (typeof window !== 'undefined') {
        localStorage.setItem('guru_admin_menu_open', String(next));
      }
      return next;
    });
  };

  const toggleReportMenu = () => {
    setReportMenuOpen((prev) => {
      const next = !prev;
      if (typeof window !== 'undefined') {
        localStorage.setItem('guru_report_menu_open', String(next));
      }
      return next;
    });
  };

  const closeMobile = () => setMobileOpen(false);

  return (
    <>
      {/* Mobile Topbar for screens < 992px */}
      <div className="fg-mobile-topbar no-print">
        <Link href="/dashboard" className="d-flex align-items-center text-decoration-none">
          <img
            src="/images/guru-hospital-logo.png"
            alt="Guru Hospital"
            style={{ height: '34px', width: 'auto', objectFit: 'contain' }}
          />
        </Link>

        <div className="d-flex align-items-center gap-2">
          {user?.can_edit && (
            <Link
              href="/admissions/create"
              className="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1 px-2"
              style={{ borderRadius: '8px', fontSize: '12.5px' }}
            >
              <i className="bi bi-plus-lg"></i>
              <span>New</span>
            </Link>
          )}
          <button
            className="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center"
            style={{ width: '36px', height: '34px', borderRadius: '8px' }}
            type="button"
            onClick={() => setMobileOpen(!mobileOpen)}
            aria-label="Toggle navigation"
            aria-expanded={mobileOpen}
          >
            <i className={`bi ${mobileOpen ? 'bi-x-lg' : 'bi-list'} fs-5`}></i>
          </button>
        </div>
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
          {/* Sidebar Header: Hospital Brand with Official Logo & Hide Button */}
          <div className="fg-sidebar-header">
            <Link
              href="/dashboard"
              className="d-flex align-items-center justify-content-center text-decoration-none fg-sidebar-brand"
              onClick={closeMobile}
              title="Guru Hospital Dashboard"
            >
              {collapsed ? (
                <img
                  src="/images/guru-logo-emblem.png"
                  alt="Guru Hospital"
                  className="fg-sidebar-logo-emblem"
                  style={{ display: 'block', height: '42px', width: '42px', objectFit: 'contain' }}
                />
              ) : (
                <img
                  src="/images/guru-hospital-logo.png"
                  alt="Guru Hospital"
                  className="fg-sidebar-logo-full"
                  style={{ display: 'block' }}
                />
              )}
            </Link>

            <div className="d-flex align-items-center gap-1">
              {/* Desktop Left Menu Hide / Expand Button */}
              {onToggleCollapse && (
                <button
                  className="fg-sidebar-hide-btn d-none d-lg-inline-flex"
                  type="button"
                  onClick={onToggleCollapse}
                  title={collapsed ? "Expand sidebar menu" : "Collapse to icon menu"}
                  aria-label={collapsed ? "Expand sidebar menu" : "Collapse to icon menu"}
                >
                  <i className={`bi ${collapsed ? 'bi-chevron-right' : 'bi-layout-sidebar-inset'}`}></i>
                </button>
              )}
              {/* Mobile Drawer Close Button */}
              <button
                type="button"
                className="btn btn-sm btn-outline-secondary fg-sidebar-close-btn"
                onClick={closeMobile}
                aria-label="Close navigation menu"
              >
                <i className="bi bi-x-lg"></i>
              </button>
            </div>
          </div>

          {/* Navigation Links (With full titles for Icon-Only mode) */}
          <div className="fg-sidebar-body">
            {/* Group 1: Clinical Management */}
            <div className="fg-sidebar-group">
              <div className="fg-sidebar-group-title">Clinical Management</div>

              <Link
                href="/dashboard"
                prefetch
                className={`fg-sidebar-link ${isLinkActive('/dashboard', true) ? 'active' : ''}`}
                onClick={closeMobile}
                title="Dashboard"
              >
                <i className="bi bi-grid icon-dashboard"></i>
                <span>Dashboard</span>
              </Link>

              <Link
                href="/admissions"
                prefetch
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
                  prefetch
                  className={`fg-sidebar-link ${isLinkActive('/admissions/create', true) ? 'active' : ''}`}
                  onClick={closeMobile}
                  title="New Admission"
                >
                  <i className="bi bi-plus-circle icon-create"></i>
                  <span>New Admission</span>
                </Link>
              )}

              {/* Report Menu with Sub-Items */}
              <div className="fg-sidebar-menu-item-wrapper" ref={reportMenuRef}>
                <button
                  type="button"
                  className={`fg-sidebar-link fg-sidebar-dropdown-btn w-100 ${
                    isAnyReportActive ? 'active has-active-child' : ''
                  }`}
                  onClick={toggleReportMenu}
                  aria-expanded={reportMenuOpen}
                  title="Report"
                >
                  <i className="bi bi-file-earmark-bar-graph icon-reports"></i>
                  <span className="flex-grow-1 text-start">Report</span>
                  <i
                    className={`bi bi-chevron-down fg-submenu-arrow ${
                      reportMenuOpen ? 'open' : ''
                    }`}
                    aria-hidden="true"
                  ></i>
                </button>

                {/* Submenu with 6 Sub-Items */}
                {reportMenuOpen && (
                  <div className="fg-sidebar-submenu" role="menu" aria-label="Report Submenu">
                    <Link
                      href="/reports/day"
                      prefetch
                      className={`fg-sidebar-sublink ${isLinkActive('/reports/day') ? 'active' : ''}`}
                      onClick={closeMobile}
                      title="Day Report"
                    >
                      <i className="bi bi-calendar-day text-info"></i>
                      <span>Day Report</span>
                    </Link>

                    <Link
                      href="/reports/mrd"
                      prefetch
                      className={`fg-sidebar-sublink ${isLinkActive('/reports/mrd') ? 'active' : ''}`}
                      onClick={closeMobile}
                      title="MRD Report"
                    >
                      <i className="bi bi-folder-check text-success"></i>
                      <span>MRD Report</span>
                    </Link>

                    <Link
                      href="/reports/birth"
                      prefetch
                      className={`fg-sidebar-sublink ${isLinkActive('/reports/birth') ? 'active' : ''}`}
                      onClick={closeMobile}
                      title="Birth report"
                    >
                      <i className="bi bi-gender-ambiguous icon-report-birth"></i>
                      <span>Birth report</span>
                    </Link>

                    <Link
                      href="/reports/surgery"
                      prefetch
                      className={`fg-sidebar-sublink ${isLinkActive('/reports/surgery') ? 'active' : ''}`}
                      onClick={closeMobile}
                      title="Surgery Report"
                    >
                      <i className="bi bi-bandaid icon-report-surgery"></i>
                      <span>Surgery Report</span>
                    </Link>

                    <Link
                      href="/reports/medical"
                      prefetch
                      className={`fg-sidebar-sublink ${isLinkActive('/reports/medical') ? 'active' : ''}`}
                      onClick={closeMobile}
                      title="Medical Case Report"
                    >
                      <i className="bi bi-heart-pulse icon-report-medical"></i>
                      <span>Medical Case Report</span>
                    </Link>

                    <Link
                      href="/reports/month-wise"
                      prefetch
                      className={`fg-sidebar-sublink ${isLinkActive('/reports/month-wise') ? 'active' : ''}`}
                      onClick={closeMobile}
                      title="Month Wise report"
                    >
                      <i className="bi bi-calendar3-range icon-report-month"></i>
                      <span>Month Wise report</span>
                    </Link>
                  </div>
                )}
              </div>
            </div>

            {/* Group 2: Administration (Collapsible Single Menu with Sub-Items) */}
            {(user?.is_admin || user?.can_view_audit) && (
              <div className="fg-sidebar-group">
                <div className="fg-sidebar-group-title">Administration</div>

                <div className="fg-sidebar-menu-item-wrapper" ref={menuRef}>
                  <button
                    type="button"
                    className={`fg-sidebar-link fg-sidebar-dropdown-btn w-100 ${
                      isAnyAdminActive ? 'active has-active-child' : ''
                    }`}
                    onClick={toggleAdminMenu}
                    aria-expanded={adminMenuOpen}
                    title="Administration"
                  >
                    <i className="bi bi-gear-wide-connected icon-admin"></i>
                    <span className="flex-grow-1 text-start">Administration</span>
                    <i
                      className={`bi bi-chevron-down fg-submenu-arrow ${
                        adminMenuOpen ? 'open' : ''
                      }`}
                      aria-hidden="true"
                    ></i>
                  </button>

                  {/* Submenu with Sub-Items */}
                  {adminMenuOpen && (
                    <div className="fg-sidebar-submenu" role="menu" aria-label="Administration Submenu">
                      {user.is_admin && (
                        <>
                          <Link
                            href="/templates"
                            prefetch
                            className={`fg-sidebar-sublink ${isLinkActive('/templates') ? 'active' : ''}`}
                            onClick={closeMobile}
                            title="Document Templates"
                          >
                            <i className="bi bi-file-earmark-code icon-templates"></i>
                            <span>Templates</span>
                          </Link>

                          <Link
                            href="/clinical-staff"
                            prefetch
                            className={`fg-sidebar-sublink ${isLinkActive('/clinical-staff') ? 'active' : ''}`}
                            onClick={closeMobile}
                            title="Clinical Staff & Doctors"
                          >
                            <i className="bi bi-heart-pulse icon-clinical-staff"></i>
                            <span>Clinical Staff</span>
                          </Link>

                          <Link
                            href="/anesthesia-types"
                            prefetch
                            className={`fg-sidebar-sublink ${isLinkActive('/anesthesia-types') ? 'active' : ''}`}
                            onClick={closeMobile}
                            title="Anesthesia Types"
                          >
                            <i className="bi bi-capsule icon-anesthesia"></i>
                            <span>Anesthesia Types</span>
                          </Link>

                          <Link
                            href="/users"
                            prefetch
                            className={`fg-sidebar-sublink ${isLinkActive('/users') ? 'active' : ''}`}
                            onClick={closeMobile}
                            title="Staff Users Management"
                          >
                            <i className="bi bi-people icon-users"></i>
                            <span>Staff Users</span>
                          </Link>

                          <Link
                            href="/backups"
                            prefetch
                            className={`fg-sidebar-sublink ${isLinkActive('/backups') ? 'active' : ''}`}
                            onClick={closeMobile}
                            title="Database Backups & Google Drive Sync"
                          >
                            <i className="bi bi-cloud-arrow-up text-success"></i>
                            <span>Database Backups</span>
                          </Link>
                        </>
                      )}

                      {user.can_view_audit && (
                        <Link
                          href="/audit-logs"
                          prefetch
                          className={`fg-sidebar-sublink ${isLinkActive('/audit-logs') ? 'active' : ''}`}
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
            )}
          </div>
        </div>

        {/* Sidebar Footer: Staff User Card & Inline Logout */}
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
              <form onSubmit={handleLogout} className="m-0 p-0 flex-shrink-0">
                <button
                  type="submit"
                  className="fg-sidebar-logout-icon-btn"
                  title="Sign Out"
                  aria-label="Sign Out"
                >
                  <i className="bi bi-box-arrow-right"></i>
                </button>
              </form>
            </div>
          </div>
        )}
      </aside>
    </>
  );
}
