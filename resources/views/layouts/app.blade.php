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
<header class="fg-floating-nav-wrap no-print">
    <nav class="fg-floating-nav">
        <a class="hospital-brand-logo" href="{{ route('dashboard') }}">
            <span class="hospital-brand-badge">
                <i class="bi bi-hospital"></i>
            </span>
            <div>
                <div class="hospital-brand-title">GURU HOSPITAL</div>
                <div class="hospital-brand-subtitle">Clinical Workflow</div>
            </div>
        </a>

        @auth
        <ul class="fg-nav-links d-none d-lg-flex">
            <li>
                <a class="fg-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid"></i> Dashboard
                </a>
            </li>
            <li>
                <a class="fg-nav-link {{ request()->routeIs('admissions.index') ? 'active' : '' }}" href="{{ route('admissions.index') }}">
                    <i class="bi bi-folder2-open"></i> Admissions
                </a>
            </li>
            @if(auth()->user()->canEditAdmissions())
            <li>
                <a class="fg-nav-link {{ request()->routeIs('admissions.create') ? 'active' : '' }}" href="{{ route('admissions.create') }}">
                    <i class="bi bi-plus-circle"></i> New Admission
                </a>
            </li>
            @endif
            @if(auth()->user()->isAdmin())
            <li>
                <a class="fg-nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}" href="{{ route('templates.index') }}">
                    <i class="bi bi-file-earmark-code"></i> Templates
                </a>
            </li>
            <li>
                <a class="fg-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="bi bi-people"></i> Staff Users
                </a>
            </li>
            @endif
            @if(auth()->user()->canViewAuditLogs())
            <li>
                <a class="fg-nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                    <i class="bi bi-shield-check"></i> Audit Trail
                </a>
            </li>
            @endif
        </ul>

        <div class="d-flex align-items-center gap-2">
            <div class="fg-user-chip d-none d-sm-inline-flex">
                <span class="fg-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="fg-user-info">
                    <div class="fg-user-name">{{ auth()->user()->name }}</div>
                    <div class="fg-user-role">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Logout">
                    <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span>
                </button>
            </form>
            <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-label="Toggle navigation">
                <i class="bi bi-list fs-6"></i>
            </button>
        </div>
        @endauth
    </nav>
    @auth
    <div class="collapse d-lg-none mt-2 card p-3 shadow-sm border" id="mobileNav">
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('dashboard') ? 'active fw-bold' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('admissions.index') ? 'active fw-bold' : '' }}" href="{{ route('admissions.index') }}">
                    <i class="bi bi-folder2-open me-2"></i> Admissions
                </a>
            </li>
            @if(auth()->user()->canEditAdmissions())
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('admissions.create') ? 'active fw-bold' : '' }}" href="{{ route('admissions.create') }}">
                    <i class="bi bi-plus-circle me-2"></i> New Admission
                </a>
            </li>
            @endif
            @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('templates.*') ? 'active fw-bold' : '' }}" href="{{ route('templates.index') }}">
                    <i class="bi bi-file-earmark-code me-2"></i> Templates
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('users.*') ? 'active fw-bold' : '' }}" href="{{ route('users.index') }}">
                    <i class="bi bi-people me-2"></i> Staff Users
                </a>
            </li>
            @endif
            @if(auth()->user()->canViewAuditLogs())
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('audit.*') ? 'active fw-bold' : '' }}" href="{{ route('audit.index') }}">
                    <i class="bi bi-shield-check me-2"></i> Audit Trail
                </a>
            </li>
            @endif
        </ul>
    </div>
    @endauth
</header>

<main class="container pb-5">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
