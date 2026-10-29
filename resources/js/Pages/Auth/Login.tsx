import { useState } from 'react';
import { useForm, Head } from '@inertiajs/react';
import OrbitalBackground from '../../Components/OrbitalBackground';

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: 'admin@guruhospital.local',
    password: 'Admin@Guru2026',
    remember: false,
  });

  const [showPassword, setShowPassword] = useState(false);

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
    <div className="fg-auth-page" style={{ minHeight: '100vh', background: 'var(--fg-page, #f4f5f7)', display: 'flex', flexDirection: 'column' }}>
      <Head title="Welcome back — Guru Hospital" />

      {/* Top Header matching FinGM Screenshot */}
      <header
        className="d-flex align-items-center justify-content-between px-4 py-3"
        style={{ width: '100%', maxWidth: '1300px', margin: '0 auto' }}
      >
        <div className="d-flex align-items-center">
          <div className="fg-brand-mark">
            <span className="bar bar-1"></span>
            <span className="bar bar-2"></span>
            <span className="bar bar-3"></span>
          </div>
          <span className="fw-bold text-dark ms-1" style={{ fontSize: '1.15rem', letterSpacing: '-0.02em' }}>
            Guru Hospital
          </span>
        </div>

        <a href="/login" className="text-decoration-none text-muted small fw-medium">
          &larr; Back to home
        </a>
      </header>

      {/* Main Centered Hero with Orbital Canvas */}
      <main
        className="flex-grow-1 d-flex align-items-center justify-content-center position-relative py-5 px-3"
        style={{ overflow: 'hidden' }}
      >
        <div className="fg-orbital-wrapper position-relative d-flex align-items-center justify-content-center w-100">
          <OrbitalBackground />

          <div className="fg-auth-card">
            {/* Top Green Icon Badge Overlapping Card Top Border */}
            <div className="fg-auth-badge-top" title="Hospital Portal">
              <i className="bi bi-bank2" style={{ color: '#16a34a' }}></i>
            </div>

            <h1 className="fg-auth-title">Welcome back</h1>
            <p className="fg-auth-subtitle">
              Sign in to your organization account and continue your work.
            </p>

            {errors.email && (
              <div className="alert alert-danger py-2 small mb-3 border-0" style={{ borderRadius: '10px' }}>
                <i className="bi bi-exclamation-circle-fill me-1"></i> {errors.email}
              </div>
            )}

            <form onSubmit={submit} id="loginForm">
              <div className="mb-3">
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
                    placeholder="Email or Phone"
                  />
                </div>
              </div>

              <div className="mb-2">
                <div className="fg-auth-input-group position-relative">
                  <i className="bi bi-lock fg-auth-input-icon"></i>
                  <input
                    type={showPassword ? 'text' : 'password'}
                    name="password"
                    id="passwordInput"
                    className="form-control"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    required
                    placeholder="Password"
                  />
                  <button
                    type="button"
                    className="fg-auth-input-toggle"
                    onClick={() => setShowPassword(!showPassword)}
                    tabIndex={-1}
                    aria-label="Toggle password visibility"
                  >
                    <i className={`bi ${showPassword ? 'bi-eye-slash' : 'bi-eye'}`}></i>
                  </button>
                </div>
              </div>

              <div className="d-flex justify-content-end mb-3">
                <span className="text-muted small" style={{ fontSize: '12px', cursor: 'pointer' }}>
                  Forgot Password?
                </span>
              </div>

              <button
                type="submit"
                className="btn-auth-black"
                disabled={processing}
              >
                {processing ? 'Signing in...' : 'Sign In'}
              </button>

              <div className="text-center mt-4">
                <span className="text-muted small">Don't have an organization account? </span>
                <span
                  className="fw-semibold small"
                  style={{ color: '#2563eb', cursor: 'pointer' }}
                  onClick={() => fillCreds('admin@guruhospital.local', 'Admin@Guru2026')}
                >
                  Contact Administrator &rarr;
                </span>
              </div>

              {/* Demo Credentials Quick-Fill Pills */}
              <div className="mt-4 pt-3 border-top text-center">
                <div className="text-muted small mb-2" style={{ fontSize: '11px', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                  Quick Staff Sign-In:
                </div>
                <div className="d-flex flex-wrap justify-content-center gap-1">
                  <button
                    type="button"
                    className="btn btn-sm btn-light border py-1 px-2"
                    style={{ fontSize: '11.5px', borderRadius: '8px' }}
                    onClick={() => fillCreds('doctor@guruhospital.local', 'Secret123!')}
                  >
                    Dr. Ambika MS
                  </button>
                  <button
                    type="button"
                    className="btn btn-sm btn-light border py-1 px-2"
                    style={{ fontSize: '11.5px', borderRadius: '8px' }}
                    onClick={() => fillCreds('nurse@guruhospital.local', 'Secret123!')}
                  >
                    Nurse Anitha
                  </button>
                  <button
                    type="button"
                    className="btn btn-sm btn-light border py-1 px-2"
                    style={{ fontSize: '11.5px', borderRadius: '8px' }}
                    onClick={() => fillCreds('admin@guruhospital.local', 'Admin@Guru2026')}
                  >
                    Administrator
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  );
}
