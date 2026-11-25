import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';

interface BackupSettings {
  enabled: boolean;
  auth_mode: 'oauth' | 'service_account';
  schedule_time: string;
  schedule_timezone: string;
  local_retention_days: number;
  drive_retention_days: number;
  google_client_id: string;
  has_client_secret: boolean;
  has_refresh_token: boolean;
  has_service_account_json: boolean;
  google_connected_email: string | null;
  google_folder_id: string;
  google_subfolder_name: string;
  is_drive_configured: boolean;
  last_run_at: string | null;
  last_success_at: string | null;
  last_error: string | null;
}

interface BackupItem {
  id: number;
  file_name: string;
  database_driver: string;
  database_name: string;
  file_size_bytes: number | null;
  human_size: string;
  sha256_checksum: string | null;
  status: 'running' | 'local_only' | 'uploaded' | 'failed';
  trigger_type: string;
  triggered_by_name: string;
  has_local_file: boolean;
  google_drive_file_id: string | null;
  google_drive_web_link: string | null;
  google_drive_deleted_at: string | null;
  local_deleted_at: string | null;
  duration_ms: number | null;
  error_message: string | null;
  started_at: string | null;
  completed_at: string | null;
  created_at: string | null;
}

interface PaginatedData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface BackupStats {
  total_backups: number;
  uploaded_count: number;
  failed_count: number;
  local_storage_human: string;
  database_driver: string;
  next_scheduled_run: string;
  oauth_redirect_uri: string;
  cron_command: string;
}

interface Props {
  settings: BackupSettings;
  backups: PaginatedData<BackupItem>;
  stats: BackupStats;
}

export default function Index({ settings, backups, stats }: Props) {
  const [runningBackup, setRunningBackup] = useState(false);
  const [testingDrive, setTestingDrive] = useState(false);
  const [showAdvancedOAuth, setShowAdvancedOAuth] = useState(false);
  const [showSetupGuide, setShowSetupGuide] = useState(!settings.is_drive_configured);
  const [copiedKey, setCopiedKey] = useState<string | null>(null);

  const { data, setData, put, processing, errors } = useForm({
    enabled: settings.enabled,
    auth_mode: settings.auth_mode || 'oauth',
    schedule_time: settings.schedule_time || '02:00',
    schedule_timezone: settings.schedule_timezone || 'Asia/Kolkata',
    local_retention_days: settings.local_retention_days ?? 7,
    drive_retention_days: settings.drive_retention_days ?? 30,
    google_client_id: settings.google_client_id || '',
    google_client_secret: '',
    google_refresh_token: '',
    google_service_account_json: '',
    google_folder_id: settings.google_folder_id || '',
    google_subfolder_name: settings.google_subfolder_name ?? 'Guru_Hospital_DB_Backups',
  });

  const handleSaveSettings = (e: React.FormEvent) => {
    e.preventDefault();
    put('/backups/settings', {
      preserveScroll: true,
      onSuccess: () => {
        setData((prev) => ({
          ...prev,
          google_client_secret: '',
          google_refresh_token: '',
          google_service_account_json: '',
        }));
      },
    });
  };

  const handleConnectGoogle = () => {
    const hasClientId = data.google_client_id.trim() !== '';
    const hasSecret = data.google_client_secret.trim() !== '' || settings.has_client_secret;
    if (!hasClientId || !hasSecret) {
      alert('Please enter your Google OAuth Client ID and Client Secret first.');
      return;
    }

    // Save current settings before full browser navigation to Google OAuth consent screen
    put('/backups/settings', {
      preserveScroll: true,
      onSuccess: () => {
        window.location.assign('/backups/google/connect');
      },
    });
  };

  const handleDisconnectGoogle = () => {
    if (confirm('Disconnect the stored Google Drive OAuth account?')) {
      router.post('/backups/google/disconnect', {}, { preserveScroll: true });
    }
  };

  const handleRunBackup = (localOnly: boolean) => {
    setRunningBackup(true);
    router.post(
      '/backups/run',
      { local_only: localOnly },
      {
        preserveScroll: true,
        onFinish: () => setRunningBackup(false),
      }
    );
  };

  const handleTestConnection = () => {
    setTestingDrive(true);
    router.post(
      '/backups/test-connection',
      {},
      {
        preserveScroll: true,
        onFinish: () => setTestingDrive(false),
      }
    );
  };

  const handleDeleteBackup = (backup: BackupItem) => {
    const msg = backup.google_drive_file_id && !backup.google_drive_deleted_at
      ? `Delete backup "${backup.file_name}" locally AND permanently remove it from Google Drive?`
      : `Delete local backup record "${backup.file_name}"?`;

    if (!confirm(msg)) return;

    router.delete(`/backups/${backup.id}`, {
      data: { delete_from_drive: Boolean(backup.google_drive_file_id && !backup.google_drive_deleted_at) },
      preserveScroll: true,
    });
  };

  const handleServiceAccountFile = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const text = await file.text();
    setData('google_service_account_json', text);
  };

  const copyText = (key: string, text: string) => {
    navigator.clipboard?.writeText(text).then(() => {
      setCopiedKey(key);
      setTimeout(() => setCopiedKey(null), 2000);
    });
  };

  const formatTimestamp = (iso: string | null) => {
    if (!iso) return '—';
    try {
      return new Date(iso).toLocaleString('en-IN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
      });
    } catch {
      return iso;
    }
  };

  const renderStatusBadge = (status: BackupItem['status']) => {
    switch (status) {
      case 'uploaded':
        return (
          <span className="badge bg-success-subtle text-success border border-success-subtle">
            <i className="bi bi-cloud-check-fill me-1"></i>Uploaded to Drive
          </span>
        );
      case 'local_only':
        return (
          <span className="badge bg-info-subtle text-info-emphasis border border-info-subtle">
            <i className="bi bi-hdd-fill me-1"></i>Local Only
          </span>
        );
      case 'failed':
        return (
          <span className="badge bg-danger-subtle text-danger border border-danger-subtle">
            <i className="bi bi-exclamation-octagon-fill me-1"></i>Failed
          </span>
        );
      default:
        return (
          <span className="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
            <i className="bi bi-arrow-repeat me-1"></i>Running
          </span>
        );
    }
  };

  return (
    <AppLayout>
      <Head title="Database Backups & Google Drive — Guru Hospital" />

      {/* Header */}
      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <div className="d-flex align-items-center gap-2 mb-1">
            <span className="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
              Admin Configuration
            </span>
            <span className="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 text-uppercase">
              Engine: {stats.database_driver}
            </span>
          </div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Database Backups &amp; Google Drive Sync
          </h1>
          <p className="text-muted mb-0">
            Automated daily compressed database dumps (<code>.sql.gz</code>) with encrypted Google Drive archival and retention cleanup.
          </p>
        </div>

        <div className="d-flex flex-wrap gap-2">
          <button
            type="button"
            className="btn btn-outline-secondary"
            onClick={() => handleRunBackup(true)}
            disabled={runningBackup}
          >
            <i className="bi bi-hdd me-1"></i>
            Local Snapshot Only
          </button>
          <button
            type="button"
            className="btn btn-outline-primary"
            onClick={handleTestConnection}
            disabled={testingDrive || !settings.is_drive_configured}
            title={settings.is_drive_configured ? 'Verify Google Drive connection & folder permissions' : 'Configure Google Drive credentials first'}
          >
            {testingDrive ? (
              <>
                <span className="spinner-border spinner-border-sm me-2" role="status"></span>
                Testing...
              </>
            ) : (
              <>
                <i className="bi bi-plug-fill me-1"></i>
                Test Google Drive
              </>
            )}
          </button>
          <button
            type="button"
            className="btn btn-primary"
            onClick={() => handleRunBackup(false)}
            disabled={runningBackup}
          >
            {runningBackup ? (
              <>
                <span className="spinner-border spinner-border-sm me-2" role="status"></span>
                Backing Up...
              </>
            ) : (
              <>
                <i className="bi bi-cloud-arrow-up-fill me-1"></i>
                Run Backup Now
              </>
            )}
          </button>
        </div>
      </div>

      {/* KPI Status Cards */}
      <div className="row g-3 mb-4">
        <div className="col-sm-6 col-xl-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-start mb-2">
                <span className="text-muted small fw-semibold text-uppercase">Daily Schedule</span>
                <span className={`badge ${settings.enabled ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}`}>
                  {settings.enabled ? 'Enabled' : 'Paused'}
                </span>
              </div>
              <div className="h4 fw-bold mb-1 text-dark">
                {settings.schedule_time} <span className="fs-6 fw-normal text-muted">{settings.schedule_timezone}</span>
              </div>
              <div className="small text-muted">
                Next run: <span className="text-dark fw-medium">{stats.next_scheduled_run}</span>
              </div>
            </div>
          </div>
        </div>

        <div className="col-sm-6 col-xl-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-start mb-2">
                <span className="text-muted small fw-semibold text-uppercase">Google Drive Sync</span>
                <span className={`badge ${settings.is_drive_configured ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis'}`}>
                  {settings.is_drive_configured ? 'Connected' : 'Not Connected'}
                </span>
              </div>
              <div className="fw-bold text-dark text-truncate mb-1" title={settings.google_connected_email || ''}>
                {settings.auth_mode === 'service_account'
                  ? (settings.has_service_account_json ? 'Service Account Key Active' : 'No JSON Key')
                  : (settings.google_connected_email || (settings.has_refresh_token ? 'OAuth Token Active' : 'Pending Google Auth'))}
              </div>
              <div className="small text-muted text-truncate">
                Subfolder: <code>{settings.google_subfolder_name || '(Folder Root)'}</code>
              </div>
            </div>
          </div>
        </div>

        <div className="col-sm-6 col-xl-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-start mb-2">
                <span className="text-muted small fw-semibold text-uppercase">Last Backup</span>
                <i className="bi bi-clock-history text-primary"></i>
              </div>
              <div className="fw-bold text-dark mb-1">
                {formatTimestamp(settings.last_success_at)}
              </div>
              <div className="small text-muted">
                Uploaded: <strong className="text-dark">{stats.uploaded_count}</strong> / Total: <strong className="text-dark">{stats.total_backups}</strong>
              </div>
            </div>
          </div>
        </div>

        <div className="col-sm-6 col-xl-3">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-start mb-2">
                <span className="text-muted small fw-semibold text-uppercase">Retention Policy</span>
                <i className="bi bi-archive text-secondary"></i>
              </div>
              <div className="h4 fw-bold mb-1 text-dark">
                {settings.drive_retention_days}d <span className="fs-6 fw-normal text-muted">Drive</span> &middot; {settings.local_retention_days}d <span className="fs-6 fw-normal text-muted">Local</span>
              </div>
              <div className="small text-muted">
                Local cache size: <strong className="text-dark">{stats.local_storage_human}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>

      {settings.last_error && (
        <div className="alert alert-warning d-flex align-items-start gap-2 mb-4 shadow-sm">
          <i className="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
          <div>
            <strong>Last Sync Notice / Error:</strong> {settings.last_error}
          </div>
        </div>
      )}

      {/* Configuration Form */}
      <form onSubmit={handleSaveSettings} className="card border-0 shadow-sm mb-4">
        <div className="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <h2 className="h5 fw-bold mb-0 text-dark">
              <i className="bi bi-sliders me-2 text-primary"></i>Backup Schedule &amp; Google Drive Configuration
            </h2>
          </div>
          <button
            type="button"
            className="btn btn-sm btn-outline-info"
            onClick={() => setShowSetupGuide((prev) => !prev)}
          >
            <i className="bi bi-book me-1"></i>
            {showSetupGuide ? 'Hide Setup Guide' : 'Google Drive & Cron Setup Guide'}
          </button>
        </div>

        <div className="card-body">
          {showSetupGuide && (
            <div className="bg-light border rounded p-3 mb-4">
              <div className="row g-3">
                <div className="col-lg-6">
                  <h6 className="fw-bold text-dark mb-2">
                    <i className="bi bi-google me-2 text-primary"></i>Step 1: Google Cloud OAuth Setup (Works with Gmail &amp; Workspace)
                  </h6>
                  <ol className="small text-muted mb-2 ps-3">
                    <li>Open Google Cloud Console &rarr; create/select a project &rarr; enable <strong>Google Drive API</strong>.</li>
                    <li>Go to <strong>Google Auth Platform / OAuth Consent Screen</strong> &rarr; publish or add your Gmail as a Test User.</li>
                    <li>Create <strong>OAuth Client ID</strong> (Application type: <strong>Web application</strong>) and add this Authorized Redirect URI:</li>
                  </ol>
                  <div className="input-group input-group-sm mb-2">
                    <input
                      type="text"
                      className="form-control font-monospace bg-white"
                      value={stats.oauth_redirect_uri}
                      readOnly
                    />
                    <button
                      type="button"
                      className="btn btn-outline-secondary"
                      onClick={() => copyText('redirect', stats.oauth_redirect_uri)}
                    >
                      <i className={`bi ${copiedKey === 'redirect' ? 'bi-check2 text-success' : 'bi-clipboard'}`}></i>{' '}
                      {copiedKey === 'redirect' ? 'Copied' : 'Copy URI'}
                    </button>
                  </div>
                  <div className="small text-muted">
                    Paste Client ID &amp; Client Secret below, then click <strong>Connect Google Drive Account</strong>.
                  </div>
                </div>

                <div className="col-lg-6">
                  <h6 className="fw-bold text-dark mb-2">
                    <i className="bi bi-terminal me-2 text-success"></i>Step 2: Server Daily Cron (Runs Scheduler at {data.schedule_time} IST)
                  </h6>
                  <p className="small text-muted mb-2">
                    Add this single cron entry on your Linux production server (<code>crontab -e</code>) so Laravel automatically triggers daily backups:
                  </p>
                  <div className="input-group input-group-sm mb-2">
                    <input
                      type="text"
                      className="form-control font-monospace bg-white"
                      value={stats.cron_command}
                      readOnly
                    />
                    <button
                      type="button"
                      className="btn btn-outline-secondary"
                      onClick={() => copyText('cron', stats.cron_command)}
                    >
                      <i className={`bi ${copiedKey === 'cron' ? 'bi-check2 text-success' : 'bi-clipboard'}`}></i>{' '}
                      {copiedKey === 'cron' ? 'Copied' : 'Copy'}
                    </button>
                  </div>
                  <div className="small text-muted">
                    Manual CLI test command: <code>php artisan db:backup</code>
                  </div>
                </div>
              </div>
            </div>
          )}

          <div className="row g-4">
            {/* Left Column: Google Drive Auth & Destination */}
            <div className="col-lg-7">
              <div className="mb-3">
                <label className="form-label fw-semibold">Google Drive Authentication Mode</label>
                <div className="btn-group w-100" role="group">
                  <button
                    type="button"
                    className={`btn ${data.auth_mode === 'oauth' ? 'btn-primary' : 'btn-outline-secondary'}`}
                    onClick={() => setData('auth_mode', 'oauth')}
                  >
                    <i className="bi bi-person-check me-1"></i>
                    OAuth 2.0 (Gmail &amp; Workspace Drive)
                  </button>
                  <button
                    type="button"
                    className={`btn ${data.auth_mode === 'service_account' ? 'btn-primary' : 'btn-outline-secondary'}`}
                    onClick={() => setData('auth_mode', 'service_account')}
                  >
                    <i className="bi bi-key me-1"></i>
                    Service Account JSON (Shared Drive)
                  </button>
                </div>
              </div>

              {data.auth_mode === 'oauth' ? (
                <div className="border rounded p-3 mb-3 bg-body-tertiary">
                  <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <span className="fw-semibold text-dark">OAuth 2.0 Credentials</span>
                    {settings.has_refresh_token ? (
                      <span className="badge bg-success-subtle text-success border border-success-subtle">
                        <i className="bi bi-check-circle-fill me-1"></i>
                        Authorized {settings.google_connected_email ? `(${settings.google_connected_email})` : ''}
                      </span>
                    ) : (
                      <span className="badge bg-secondary-subtle text-secondary">Not yet authorized</span>
                    )}
                  </div>

                  <div className="row g-3">
                    <div className="col-md-6">
                      <label className="form-label small fw-semibold">Google OAuth Client ID</label>
                      <input
                        type="text"
                        className="form-control"
                        placeholder="xxxx.apps.googleusercontent.com"
                        value={data.google_client_id}
                        onChange={(e) => setData('google_client_id', e.target.value)}
                      />
                      {errors.google_client_id && (
                        <div className="text-danger small mt-1">{errors.google_client_id}</div>
                      )}
                    </div>

                    <div className="col-md-6">
                      <label className="form-label small fw-semibold">
                        Google OAuth Client Secret
                        {settings.has_client_secret && (
                          <span className="badge bg-success-subtle text-success ms-2">Saved</span>
                        )}
                      </label>
                      <input
                        type="password"
                        className="form-control"
                        placeholder={settings.has_client_secret ? 'Leave blank to keep existing secret' : 'GOCSPX-...'}
                        value={data.google_client_secret}
                        onChange={(e) => setData('google_client_secret', e.target.value)}
                      />
                    </div>
                  </div>

                  <div className="d-flex flex-wrap align-items-center gap-2 mt-3">
                    <button
                      type="button"
                      className="btn btn-success btn-sm"
                      onClick={handleConnectGoogle}
                      disabled={processing}
                    >
                      <i className="bi bi-google me-1"></i>
                      {settings.has_refresh_token ? 'Reconnect Google Drive Account' : 'Connect Google Drive Account'}
                    </button>

                    {settings.has_refresh_token && (
                      <button
                        type="button"
                        className="btn btn-outline-danger btn-sm"
                        onClick={handleDisconnectGoogle}
                      >
                        <i className="bi bi-x-circle me-1"></i>
                        Disconnect
                      </button>
                    )}

                    <button
                      type="button"
                      className="btn btn-link btn-sm text-decoration-none ms-auto"
                      onClick={() => setShowAdvancedOAuth((prev) => !prev)}
                    >
                      {showAdvancedOAuth ? 'Hide Manual Token' : 'Paste Refresh Token Manually'}
                    </button>
                  </div>

                  {showAdvancedOAuth && (
                    <div className="mt-3">
                      <label className="form-label small fw-semibold">
                        Manual OAuth Refresh Token
                        {settings.has_refresh_token && (
                          <span className="badge bg-success-subtle text-success ms-2">Saved</span>
                        )}
                      </label>
                      <input
                        type="password"
                        className="form-control font-monospace"
                        placeholder="1//0g..."
                        value={data.google_refresh_token}
                        onChange={(e) => setData('google_refresh_token', e.target.value)}
                      />
                    </div>
                  )}
                </div>
              ) : (
                <div className="border rounded p-3 mb-3 bg-body-tertiary">
                  <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <span className="fw-semibold text-dark">Service Account JSON Key</span>
                    {settings.has_service_account_json && (
                      <span className="badge bg-success-subtle text-success border border-success-subtle">
                        <i className="bi bi-check-circle-fill me-1"></i>JSON Key Saved
                      </span>
                    )}
                  </div>
                  <p className="small text-muted mb-2">
                    Note: Service Accounts require a <strong>Google Workspace Shared Drive</strong> folder ID (personal <code>@gmail.com</code> My Drive rejects service account uploads; use OAuth 2.0 mode for personal Gmail).
                  </p>
                  <div className="mb-2">
                    <input
                      type="file"
                      accept=".json,application/json"
                      className="form-control form-control-sm"
                      onChange={handleServiceAccountFile}
                    />
                  </div>
                  <textarea
                    rows={4}
                    className="form-control font-monospace small"
                    placeholder={
                      settings.has_service_account_json
                        ? 'Leave blank to keep the currently saved Service Account JSON, or paste a new JSON key...'
                        : '{ "type": "service_account", "client_email": "...", "private_key": "..." }'
                    }
                    value={data.google_service_account_json}
                    onChange={(e) => setData('google_service_account_json', e.target.value)}
                  />
                  {errors.google_service_account_json && (
                    <div className="text-danger small mt-1">{errors.google_service_account_json}</div>
                  )}
                </div>
              )}

              <div className="row g-3">
                <div className="col-md-6">
                  <label className="form-label small fw-semibold">Target Google Drive Folder ID or URL (Optional)</label>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Folder ID or https://drive.google.com/drive/folders/..."
                    value={data.google_folder_id}
                    onChange={(e) => setData('google_folder_id', e.target.value)}
                  />
                  <div className="form-text">Leave blank to upload inside My Drive root.</div>
                </div>
                <div className="col-md-6">
                  <label className="form-label small fw-semibold">Auto-Created Backup Subfolder Name</label>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Guru_Hospital_DB_Backups"
                    value={data.google_subfolder_name}
                    onChange={(e) => setData('google_subfolder_name', e.target.value)}
                  />
                  <div className="form-text">Created automatically if not already present.</div>
                </div>
              </div>
            </div>

            {/* Right Column: Schedule & Retention */}
            <div className="col-lg-5">
              <div className="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                <div>
                  <h6 className="fw-bold text-dark mb-3">
                    <i className="bi bi-calendar2-check me-2 text-primary"></i>Daily Schedule &amp; Retention
                  </h6>

                  <div className="form-check form-switch mb-3">
                    <input
                      id="backupEnabledSwitch"
                      type="checkbox"
                      className="form-check-input"
                      checked={data.enabled}
                      onChange={(e) => setData('enabled', e.target.checked)}
                    />
                    <label htmlFor="backupEnabledSwitch" className="form-check-label fw-semibold">
                      Enable Automated Daily Backup
                    </label>
                  </div>

                  <div className="row g-3 mb-3">
                    <div className="col-6">
                      <label className="form-label small fw-semibold">Daily Backup Time</label>
                      <input
                        type="time"
                        className="form-control"
                        value={data.schedule_time}
                        onChange={(e) => setData('schedule_time', e.target.value)}
                        required
                      />
                      {errors.schedule_time && (
                        <div className="text-danger small mt-1">{errors.schedule_time}</div>
                      )}
                    </div>
                    <div className="col-6">
                      <label className="form-label small fw-semibold">Timezone</label>
                      <select
                        className="form-select"
                        value={data.schedule_timezone}
                        onChange={(e) => setData('schedule_timezone', e.target.value)}
                      >
                        <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                        <option value="UTC">UTC</option>
                      </select>
                    </div>
                  </div>

                  <div className="row g-3">
                    <div className="col-6">
                      <label className="form-label small fw-semibold">Keep on Google Drive (Days)</label>
                      <input
                        type="number"
                        min={1}
                        max={3650}
                        className="form-control"
                        value={data.drive_retention_days}
                        onChange={(e) => setData('drive_retention_days', Number(e.target.value) || 30)}
                        required
                      />
                    </div>
                    <div className="col-6">
                      <label className="form-label small fw-semibold">Keep Locally (Days)</label>
                      <input
                        type="number"
                        min={1}
                        max={365}
                        className="form-control"
                        value={data.local_retention_days}
                        onChange={(e) => setData('local_retention_days', Number(e.target.value) || 7)}
                        required
                      />
                    </div>
                  </div>
                </div>

                <div className="mt-4 pt-3 border-top d-flex justify-content-end">
                  <button type="submit" className="btn btn-primary" disabled={processing}>
                    {processing ? (
                      <>
                        <span className="spinner-border spinner-border-sm me-2" role="status"></span>
                        Saving...
                      </>
                    ) : (
                      <>
                        <i className="bi bi-check2-circle me-1"></i>
                        Save Backup Settings
                      </>
                    )}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>

      {/* Backup History Table */}
      <div className="card border-0 shadow-sm">
        <div className="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h2 className="h5 fw-bold mb-0 text-dark">
            <i className="bi bi-list-check me-2 text-primary"></i>Backup Execution History
          </h2>
          <span className="text-muted small">Showing {backups.data.length} of {backups.total} backups</span>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle mb-0">
            <thead className="table-light">
              <tr>
                <th>Timestamp</th>
                <th>Archive File</th>
                <th>Engine &amp; Size</th>
                <th>Trigger</th>
                <th>Status</th>
                <th className="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              {backups.data.length === 0 ? (
                <tr>
                  <td colSpan={6} className="text-center py-5 text-muted">
                    <i className="bi bi-cloud-slash fs-2 d-block mb-2"></i>
                    No backups recorded yet. Click <strong>Run Backup Now</strong> to create your first compressed SQL archive.
                  </td>
                </tr>
              ) : (
                backups.data.map((backup) => (
                  <tr key={backup.id}>
                    <td className="text-nowrap small">
                      <div className="fw-semibold text-dark">{formatTimestamp(backup.created_at)}</div>
                      {backup.duration_ms !== null && (
                        <div className="text-muted">{(backup.duration_ms / 1000).toFixed(2)}s</div>
                      )}
                    </td>
                    <td>
                      <div className="font-monospace small fw-semibold text-dark">{backup.file_name}</div>
                      {backup.sha256_checksum && (
                        <div className="text-muted font-monospace" style={{ fontSize: '11px' }}>
                          SHA-256: {backup.sha256_checksum.slice(0, 16)}...
                        </div>
                      )}
                      {backup.error_message && (
                        <div className="text-danger small mt-1">{backup.error_message}</div>
                      )}
                    </td>
                    <td className="small">
                      <span className="badge bg-light text-dark border text-uppercase me-1">
                        {backup.database_driver}
                      </span>
                      <span className="fw-semibold">{backup.human_size}</span>
                    </td>
                    <td className="small">
                      <div className="text-capitalize fw-semibold">{backup.trigger_type}</div>
                      <div className="text-muted">{backup.triggered_by_name}</div>
                    </td>
                    <td>{renderStatusBadge(backup.status)}</td>
                    <td className="text-end text-nowrap">
                      <div className="d-inline-flex gap-1">
                        {backup.google_drive_web_link && !backup.google_drive_deleted_at && (
                          <a
                            href={backup.google_drive_web_link}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="btn btn-sm btn-outline-success"
                            title="Open in Google Drive"
                          >
                            <i className="bi bi-box-arrow-up-right me-1"></i>Drive
                          </a>
                        )}
                        {backup.has_local_file && (
                          <a
                            href={`/backups/${backup.id}/download`}
                            className="btn btn-sm btn-outline-primary"
                            title="Download compressed SQL archive"
                          >
                            <i className="bi bi-download"></i>
                          </a>
                        )}
                        <button
                          type="button"
                          className="btn btn-sm btn-outline-danger"
                          onClick={() => handleDeleteBackup(backup)}
                          title="Delete backup record"
                        >
                          <i className="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <Pagination links={backups.links} />
      </div>
    </AppLayout>
  );
}
