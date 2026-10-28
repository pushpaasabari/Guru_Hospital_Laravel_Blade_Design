<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guru Hospital — Staff Authentication</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/guru-theme.css') }}">
</head>
<body class="fg-auth-page">

<header class="fg-auth-top-header">
    <div class="d-flex align-items-center gap-2">
        <span class="hospital-brand-badge">
            <i class="bi bi-hospital"></i>
        </span>
        <div>
            <div class="hospital-brand-title">GURU HOSPITAL</div>
            <div class="hospital-brand-subtitle">Clinical Document System</div>
        </div>
    </div>
    <div class="d-none d-sm-block text-muted small">
        <i class="bi bi-shield-lock-fill text-success me-1"></i> NABH / HIPAA Compliant Gateway
    </div>
</header>

<main class="fg-auth-main">
    <div class="fg-orbital-wrapper">
        <!-- Orbital Rings, Arcs and Ambient Cyan/Blue Glow -->
        <div class="fg-orbital-rings-bg" aria-hidden="true">
            <div class="fg-ambient-glow"></div>
            <div class="fg-orbit-ring ring-1"></div>
            <div class="fg-orbit-ring ring-2"></div>
            <div class="fg-orbit-ring ring-3"></div>
            <div class="fg-orbit-ring ring-4"></div>

            <svg class="fg-orbit-arcs-svg" viewBox="0 0 1000 1000" fill="none">
                <circle cx="500" cy="500" r="280" stroke="#245dff" stroke-width="1.5" stroke-dasharray="30 450" stroke-linecap="round" opacity="0.6" />
                <circle cx="500" cy="500" r="390" stroke="#38bdf8" stroke-width="1.5" stroke-dasharray="45 600" stroke-linecap="round" opacity="0.7" />
                <circle cx="500" cy="500" r="480" stroke="#245dff" stroke-width="1.5" stroke-dasharray="60 800" stroke-linecap="round" opacity="0.5" />
            </svg>

            <!-- Floating Healthcare & Governance Badges -->
            <div class="fg-orbit-badge badge-top-left" style="color:#0d9488;" title="Clinical Diagnosis">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <div class="fg-orbit-badge badge-top-right" style="color:#2563eb;" title="Operation Theatre">
                <i class="bi bi-hospital"></i>
            </div>
            <div class="fg-orbit-badge badge-mid-left" style="color:#16a34a;" title="Discharge Summary">
                <i class="bi bi-file-earmark-medical"></i>
            </div>
            <div class="fg-orbit-badge badge-mid-right" style="color:#7c3aed;" title="MRD Records">
                <i class="bi bi-folder-check"></i>
            </div>
            <div class="fg-orbit-badge badge-bottom-left" style="color:#e11d48;" title="Tamper-Resistant Audit">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="fg-orbit-badge badge-bottom-right" style="color:#f59e0b;" title="Bilingual Formats">
                <i class="bi bi-translate"></i>
            </div>
        </div>

        <!-- Centered Floating White Auth Card -->
        <div class="fg-auth-card">
            <div class="fg-auth-badge-top">
                <i class="bi bi-hospital"></i>
            </div>

            <h1 class="fg-auth-title">Staff Authentication</h1>
            <p class="fg-auth-subtitle">Sign in to manage patient admissions, discharge summaries, OT notes, and MRD archives.</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Hospital Email Address</label>
                    <div class="fg-auth-input-group">
                        <i class="bi bi-envelope fg-auth-input-icon"></i>
                        <input type="email" name="email" id="emailInput" class="form-control" value="{{ old('email', 'admin@guruhospital.local') }}" required autofocus placeholder="staff@guruhospital.local">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Password</label>
                        <span class="text-muted" style="font-size:11px;">Restricted Access</span>
                    </div>
                    <div class="fg-auth-input-group">
                        <i class="bi bi-shield-lock fg-auth-input-icon"></i>
                        <input type="password" name="password" id="passwordInput" class="form-control" value="Admin@Guru2026" required placeholder="••••••••">
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label small text-muted" for="rememberMe">Remember my login</label>
                </div>

                <button type="submit" class="btn-auth-black">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Secure Staff Sign In
                </button>
            </form>

            <hr class="my-4" style="border-color: #f1f5f9;">

            <div>
                <div class="text-muted small fw-bold mb-2 text-center text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em;">Switch Demo Role Credentials</div>
                <div class="d-flex flex-wrap gap-1 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCreds('admin@guruhospital.local', 'Admin@Guru2026')">Admin</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCreds('dr.karthik@guruhospital.local', 'Doctor@Guru2026')">Doctor (Surgeon)</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCreds('dr.priya@guruhospital.local', 'Doctor@Guru2026')">Doctor (OBGYN)</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCreds('nurse.anitha@guruhospital.local', 'Nurse@Guru2026')">Nurse</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCreds('mrd.kumar@guruhospital.local', 'MrdStaff@Guru2026')">MRD Staff</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCreds('auditor.saravanan@guruhospital.local', 'Auditor@Guru2026')">Auditor</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function fillCreds(email, password) {
    document.getElementById('emailInput').value = email;
    document.getElementById('passwordInput').value = password;
}
</script>
</body>
</html>
