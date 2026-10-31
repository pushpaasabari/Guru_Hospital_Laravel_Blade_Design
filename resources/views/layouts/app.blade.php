<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Guru Hospital — Clinical Document Workflow')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/guru-theme.css') }}">
</head>
<body>
<div class="fg-app-shell" id="appShell">
    @auth
    <!-- Mobile Topbar for screens < 992px -->
    <div class="fg-mobile-topbar no-print">
        <a class="d-flex align-items-center text-decoration-none" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/guru-hospital-logo.png') }}" alt="Guru Hospital" style="height: 36px; width: auto; object-fit: contain;">
        </a>

        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSidebar" aria-label="Toggle navigation">
            <i class="bi bi-list fs-5"></i>
        </button>
    </div>

    <!-- Left Sidebar (Desktop Icon/Full + Mobile Drawer) -->
    <aside class="fg-sidebar no-print collapse d-lg-flex" id="mobileSidebar">
        <div>
            <div class="fg-sidebar-header">
                <a class="d-flex align-items-center justify-content-center text-decoration-none fg-sidebar-brand" href="{{ route('dashboard') }}" title="Guru Hospital Dashboard">
                    <img src="{{ asset('images/guru-hospital-logo.png') }}" alt="Guru Hospital" class="fg-sidebar-logo-full">
                    <img src="{{ asset('images/guru-logo-emblem.png') }}" alt="Guru Hospital" class="fg-sidebar-logo-emblem">
                </a>
                <div class="d-flex align-items-center">
                    <!-- Left Menu Hide / Expand Button -->
                    <button class="fg-sidebar-hide-btn" id="hideSidebarBtn" type="button" title="Collapse to icon menu" aria-label="Collapse to icon menu">
                        <i class="bi bi-layout-sidebar-inset" id="hideSidebarIcon"></i>
                    </button>
                </div>
            </div>

            <div class="fg-sidebar-body">
                <div class="fg-sidebar-group">
                    <div class="fg-sidebar-group-title">Clinical Management</div>
                    <a class="fg-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Dashboard">
                        <i class="bi bi-grid icon-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="fg-sidebar-link {{ request()->routeIs('admissions.index') ? 'active' : '' }}" href="{{ route('admissions.index') }}" title="Admissions Archive">
                        <i class="bi bi-folder2-open icon-admissions"></i>
                        <span>Admissions</span>
                    </a>
                    @if(auth()->user()->canEditAdmissions())
                    <a class="fg-sidebar-link {{ request()->routeIs('admissions.create') ? 'active' : '' }}" href="{{ route('admissions.create') }}" title="New Admission">
                        <i class="bi bi-plus-circle icon-create"></i>
                        <span>New Admission</span>
                    </a>
                    @endif
                </div>

                @if(auth()->user()->isAdmin() || auth()->user()->canViewAuditLogs())
                <div class="fg-sidebar-group">
                    <div class="fg-sidebar-group-title">Administration</div>
                    @if(auth()->user()->isAdmin())
                    <a class="fg-sidebar-link {{ request()->routeIs('templates.*') ? 'active' : '' }}" href="{{ route('templates.index') }}" title="Document Templates">
                        <i class="bi bi-file-earmark-code icon-templates"></i>
                        <span>Templates</span>
                    </a>
                    <a class="fg-sidebar-link {{ request()->routeIs('clinical-staff.*') ? 'active' : '' }}" href="{{ route('clinical-staff.index') }}" title="Clinical Staff & Doctors">
                        <i class="bi bi-heart-pulse icon-clinical-staff"></i>
                        <span>Clinical Staff</span>
                    </a>
                    <a class="fg-sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" title="Staff Users Management">
                        <i class="bi bi-people icon-users"></i>
                        <span>Staff Users</span>
                    </a>
                    @endif
                    @if(auth()->user()->canViewAuditLogs())
                    <a class="fg-sidebar-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}" title="Audit Trail Logs">
                        <i class="bi bi-shield-check icon-audit"></i>
                        <span>Audit Trail</span>
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="fg-sidebar-footer">
            <div class="fg-sidebar-user-card" title="{{ auth()->user()->name }} ({{ str_replace('_', ' ', auth()->user()->role) }})">
                <span class="fg-sidebar-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="fg-sidebar-user-meta">
                    <div class="fg-sidebar-user-name" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</div>
                    <div class="fg-sidebar-user-role">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                    @if(auth()->user()->doctor_reg_no)
                        <div class="text-muted font-monospace" style="font-size: 9.5px; margin-top: 1px;">{{ auth()->user()->doctor_reg_no }}</div>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="fg-sidebar-logout-btn" title="Sign Out">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>
    @endauth

    <div class="fg-main-wrapper">
        <main class="fg-main-content">
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show no-print shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show no-print shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger no-print shadow-sm">
                    <strong class="d-flex align-items-center mb-1"><i class="bi bi-x-octagon-fill me-2"></i> Please review and correct the following:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function() {
        const shell = document.getElementById('appShell');
        const hideBtn = document.getElementById('hideSidebarBtn');
        const hideIcon = document.getElementById('hideSidebarIcon');
        if (!shell || !hideBtn || !hideIcon) return;

        function updateUI(collapsed) {
            if (collapsed) {
                shell.classList.add('sidebar-collapsed');
                hideIcon.className = 'bi bi-chevron-right';
                hideBtn.title = 'Expand sidebar menu';
            } else {
                shell.classList.remove('sidebar-collapsed');
                hideIcon.className = 'bi bi-layout-sidebar-inset';
                hideBtn.title = 'Collapse to icon menu';
            }
        }

        const isCollapsed = localStorage.getItem('guru_sidebar_collapsed') === 'true';
        updateUI(isCollapsed);

        hideBtn.addEventListener('click', function() {
            const next = !shell.classList.contains('sidebar-collapsed');
            localStorage.setItem('guru_sidebar_collapsed', String(next));
            updateUI(next);
        });
    })();
</script>
@yield('scripts')
</body>
</html>