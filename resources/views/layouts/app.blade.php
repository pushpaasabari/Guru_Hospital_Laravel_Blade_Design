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
<div class="fg-app-shell">
    @auth
    <!-- Mobile Topbar for screens < 992px -->
    <div class="fg-mobile-topbar no-print">
        <a class="hospital-brand-logo" href="{{ route('dashboard') }}">
            <span class="hospital-brand-badge">
                <i class="bi bi-hospital"></i>
            </span>
            <div>
                <div class="hospital-brand-title" style="font-size: 0.95rem;">GURU HOSPITAL</div>
                <div class="hospital-brand-subtitle">Clinical Workflow</div>
            </div>
        </a>

        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSidebar" aria-label="Toggle navigation">
            <i class="bi bi-list fs-5"></i>
        </button>
    </div>

    <!-- Left Sidebar -->
    <aside class="fg-sidebar no-print collapse d-lg-flex" id="mobileSidebar">
        <div>
            <div class="fg-sidebar-header">
                <a class="hospital-brand-logo" href="{{ route('dashboard') }}">
                    <span class="hospital-brand-badge">
                        <i class="bi bi-hospital"></i>
                    </span>
                    <div>
                        <div class="hospital-brand-title">GURU HOSPITAL</div>
                        <div class="hospital-brand-subtitle">Clinical Workflow</div>
                    </div>
                </a>
                <button class="btn btn-sm btn-close d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSidebar" aria-label="Close"></button>
            </div>

            <div class="fg-sidebar-body">
                <div class="fg-sidebar-group">
                    <div class="fg-sidebar-group-title">Clinical Management</div>
                    <a class="fg-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="fg-sidebar-link {{ request()->routeIs('admissions.index') ? 'active' : '' }}" href="{{ route('admissions.index') }}">
                        <i class="bi bi-folder2-open"></i>
                        <span>Admissions</span>
                    </a>
                    @if(auth()->user()->canEditAdmissions())
                    <a class="fg-sidebar-link {{ request()->routeIs('admissions.create') ? 'active' : '' }}" href="{{ route('admissions.create') }}">
                        <i class="bi bi-plus-circle"></i>
                        <span>New Admission</span>
                    </a>
                    @endif
                </div>

                @if(auth()->user()->isAdmin() || auth()->user()->canViewAuditLogs())
                <div class="fg-sidebar-group">
                    <div class="fg-sidebar-group-title">Administration</div>
                    @if(auth()->user()->isAdmin())
                    <a class="fg-sidebar-link {{ request()->routeIs('templates.*') ? 'active' : '' }}" href="{{ route('templates.index') }}">
                        <i class="bi bi-file-earmark-code"></i>
                        <span>Templates</span>
                    </a>
                    <a class="fg-sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-people"></i>
                        <span>Staff Users</span>
                    </a>
                    @endif
                    @if(auth()->user()->canViewAuditLogs())
                    <a class="fg-sidebar-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                        <i class="bi bi-shield-check"></i>
                        <span>Audit Trail</span>
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="fg-sidebar-footer">
            <div class="fg-sidebar-user-card">
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
                <button type="submit" class="fg-sidebar-logout-btn">
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
@yield('scripts')
</body>
</html>