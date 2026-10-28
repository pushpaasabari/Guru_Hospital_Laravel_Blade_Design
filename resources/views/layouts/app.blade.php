<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Guru Hospital — Clinical Document Workflow')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background:#f4f7fb; color:#172b4d; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        .hospital-brand { color:#17365d; font-weight:800; letter-spacing:.03em; font-size:1.25rem; }
        .card { border:0; box-shadow:0 2px 10px rgba(23,54,93,.06); border-radius: 8px; }
        .card-header { border-bottom: 1px solid #edf2f7; font-weight: 600; }
        .required:after { content:" *"; color:#dc3545; }
        .nav-link.active { font-weight: 600; color: #17365d !important; border-bottom: 2px solid #17365d; }
        .badge-draft { background-color: #6c757d; }
        .badge-ready_for_review { background-color: #0dcaf0; color: #000; }
        .badge-returned_for_correction { background-color: #dc3545; }
        .badge-approved { background-color: #198754; }
        .badge-generated { background-color: #0d6efd; }
        .badge-superseded { background-color: #ffc107; color: #000; }
        @media print { .no-print { display:none!important } body { background:#fff } }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4 no-print shadow-sm">
    <div class="container">
        <a class="navbar-brand hospital-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <i class="bi bi-hospital me-2 text-primary fs-4"></i>
            <span>GURU HOSPITAL</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            @auth
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admissions.index') ? 'active' : '' }}" href="{{ route('admissions.index') }}">
                        <i class="bi bi-folder2-open me-1"></i> Admissions
                    </a>
                </li>
                @if(auth()->user()->canEditAdmissions())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admissions.create') ? 'active' : '' }}" href="{{ route('admissions.create') }}">
                        <i class="bi bi-plus-circle me-1"></i> New Admission
                    </a>
                </li>
                @endif
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}" href="{{ route('templates.index') }}">
                        <i class="bi bi-file-earmark-code me-1"></i> Templates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-people me-1"></i> Staff Users
                    </a>
                </li>
                @endif
                @if(auth()->user()->canViewAuditLogs())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                        <i class="bi bi-shield-check me-1"></i> Audit Trail
                    </a>
                </li>
                @endif
            </ul>

            <div class="d-flex align-items-center">
                <div class="me-3 text-end">
                    <div class="fw-bold small">{{ auth()->user()->name }}</div>
                    <span class="badge bg-secondary text-uppercase" style="font-size:0.68rem;">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </div>
</nav>

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
