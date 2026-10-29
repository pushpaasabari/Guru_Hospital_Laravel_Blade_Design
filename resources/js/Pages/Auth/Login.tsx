import React from 'react';
import { useForm, Head } from '@inertiajs/react';
import OrbitalBackground from '../../Components/OrbitalBackground';

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: 'admin@guruhospital.local',
    password: 'Admin@Guru2026',
    remember: false,
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/login');
  };

  const fillCreds = (email: string, pass: string) => {
    setData({
      ...data,
      email,
      password: pass,
    });
  };

  return (
    <div className="fg-auth-page">
      <Head title="Staff Authentication — Guru Hospital" />

      <header className="fg-auth-top-header">
        <div className="d-flex align-items-center gap-2">
          <span className="hospital-brand-badge">
            <i className="bi bi-hospital"></i>
          </span>
          <div>
            <div className="hospital-brand-title">GURU HOSPITAL</div>
            <div className="hospital-brand-subtitle">Clinical Document System</div>
          </div>
        </div>
        <div className="d-none d-sm-block text-muted small">
          <i className="bi bi-shield-lock-fill text-success me-1"></i> NABH / HIPAA Compliant Gateway
        </div>
      </header>

      <main className="fg-auth-main">
        <div className="fg-orbital-wrapper">
          <OrbitalBackground />

          <div className="fg-auth-card">
            <div className="fg-auth-badge-top">
              <i className="bi bi-hospital"></i>
            </div>

            <h1 className="fg-auth-title">Staff Authentication</h1>
            <p className="fg-auth-subtitle">
              Sign in to manage patient admissions, discharge summaries, OT notes, and MRD archives.
            </p>

            {errors.email && (
              <div className="alert alert-danger py-2 small mb-3">
                <i className="bi bi-exclamation-circle-fill me-1"></i> {errors.email}
              </div>
            )}

            <form onSubmit={submit} id="loginForm">
              <div className="mb-3">
                <label className="form-label" htmlFor="emailInput">Hospital Email Address</label>
                <div className="fg-auth-input-group">
                  <i className="bi bi-envelope fg-auth-input-icon"></i>
                  <input
                    type="email"
                    name="email"
                    id="emailInput"
                    className="form-control"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    required
                    autoFocus
                    placeholder="staff@guruhospital.local"
                  />
                </div>
              </div>

              <div className="mb-3">
                <div className="d-flex justify-content-between align-items-center mb-1">
                  <label className="form-label mb-0" htmlFor="passwordInput">Password</label>
                  <span className="text-muted" style={{ fontSize: '11px' }}>Restricted Access</span>
                </div>
                <div className="fg-auth-input-group">
                  <i className="bi bi-shield-lock fg-auth-input-icon"></i>
                  <input
                    type="password"
                    name="password"
                    id="passwordInput"
                    className="form-control"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    required
                    placeholder="••••••••"
                  />
                </div>
              </div>

              <div className="form-check mb-3">
                <input
                  className="form-check-input"
                  type="checkbox"
                  name="remember"
                  id="rememberMe"
                  checked={data.remember}
                  onChange={(e) => setData('remember', e.target.checked)}
                />
                <label className="form-check-label small text-muted" htmlFor="rememberMe">
                  Remember my login
                </label>
              </div>

              <button type="submit" className="btn-auth-black" disabled={processing}>
                <i className="bi bi-box-arrow-in-right me-1"></i> {processing ? 'Signing in…' : 'Secure Staff Sign In'}
              </button>
            </form>

            <hr className="my-4" style={{ borderColor: '#f1f5f9' }} />

            <div>
              <div
                className="text-muted small fw-bold mb-2 text-center text-uppercase"
                style={{ fontSize: '0.72rem', letterSpacing: '0.05em' }}
              >
                Switch Demo Role Credentials
              </div>
              <div className="d-flex flex-wrap gap-1 justify-content-center">
                <button
                  type="button"
                  className="btn btn-sm btn-outline-secondary"
                  onClick={() => fillCreds('admin@guruhospital.local', 'Admin@Guru2026')}
                >
                  Admin
                </button>
                <button
                  type="button"
                  className="btn btn-sm btn-outline-secondary"
                  onClick={() => fillCreds('dr.karthik@guruhospital.local', 'Doctor@Guru2026')}
                >
                  Doctor (Surgeon)
                </button>
                <button
                  type="button"
                  className="btn btn-sm btn-outline-secondary"
                  onClick={() => fillCreds('dr.priya@guruhospital.local', 'Doctor@Guru2026')}
                >
                  Doctor (OBGYN)
                </button>
                <button
                  type="button"
                  className="btn btn-sm btn-outline-secondary"
                  onClick={() => fillCreds('nurse.anitha@guruhospital.local', 'Nurse@Guru2026')}
                >
                  Nurse
                </button>
                <button
                  type="button"
                  className="btn btn-sm btn-outline-secondary"
                  onClick={() => fillCreds('mrd.kumar@guruhospital.local', 'MrdStaff@Guru2026')}
                >
                  MRD Staff
                </button>
                <button
                  type="button"
                  className="btn btn-sm btn-outline-secondary"
                  onClick={() => fillCreds('auditor.saravanan@guruhospital.local', 'Auditor@Guru2026')}
                >
                  Auditor
                </button>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
