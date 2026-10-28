<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guru Hospital — Staff Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #0e2a47 0%, #17365d 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .login-card { width: 100%; max-width: 440px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); background: #fff; overflow: hidden; }
        .login-header { background: #0c233c; padding: 28px 24px; text-align: center; color: #fff; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <i class="bi bi-hospital fs-1 text-info mb-2 d-inline-block"></i>
        <h4 class="fw-bold mb-1 letter-spacing-1">GURU HOSPITAL</h4>
        <p class="text-white-50 small mb-0">Discharge Summary, OT Notes & MRD Automation</p>
    </div>
    <div class="p-4">
        @if($errors->any())
            <div class="alert alert-danger py-2 small mb-3">
                <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">Hospital Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="emailInput" class="form-control" value="{{ old('email', 'admin@guruhospital.local') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control" value="Admin@Guru2026" required>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                <label class="form-check-label small text-muted" for="rememberMe">Remember my login</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Secure Staff Sign In
            </button>
        </form>

        <hr class="my-4">

        <div>
            <div class="text-muted small fw-bold mb-2 text-center text-uppercase" style="font-size:0.75rem;">Switch Demo Role Credentials</div>
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

<script>
function fillCreds(email, password) {
    document.getElementById('emailInput').value = email;
    document.getElementById('passwordInput').value = password;
}
</script>
</body>
</html>

