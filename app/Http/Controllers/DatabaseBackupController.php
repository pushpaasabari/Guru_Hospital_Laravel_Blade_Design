<?php

namespace App\Http\Controllers;

use App\Models\DatabaseBackup;
use App\Models\DatabaseBackupSetting;
use App\Services\AuditService;
use App\Services\DatabaseBackupService;
use App\Services\GoogleDriveBackupClient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DatabaseBackupController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAdmin($request);

        $setting = DatabaseBackupSetting::current();

        $backups = DatabaseBackup::query()
            ->with('triggeredByUser:id,name')
            ->latest('id')
            ->paginate(15)
            ->through(function (DatabaseBackup $backup) {
                return [
                    'id' => $backup->id,
                    'file_name' => $backup->file_name,
                    'database_driver' => $backup->database_driver,
                    'database_name' => $backup->database_name,
                    'file_size_bytes' => $backup->file_size_bytes,
                    'human_size' => $backup->humanFileSize(),
                    'sha256_checksum' => $backup->sha256_checksum,
                    'status' => $backup->status,
                    'trigger_type' => $backup->trigger_type,
                    'triggered_by_name' => $backup->triggeredByUser?->name ?? ($backup->trigger_type === 'scheduled' ? 'Daily Scheduler' : 'CLI'),
                    'has_local_file' => $backup->localFileExists(),
                    'google_drive_file_id' => $backup->google_drive_file_id,
                    'google_drive_web_link' => $backup->google_drive_web_link,
                    'google_drive_deleted_at' => $backup->google_drive_deleted_at?->toIso8601String(),
                    'local_deleted_at' => $backup->local_deleted_at?->toIso8601String(),
                    'duration_ms' => $backup->duration_ms,
                    'error_message' => $backup->error_message,
                    'started_at' => $backup->started_at?->toIso8601String(),
                    'completed_at' => $backup->completed_at?->toIso8601String(),
                    'created_at' => $backup->created_at?->toIso8601String(),
                ];
            });

        $localDir = (string) config('backup.local_directory', storage_path('app/private/backups'));
        $localBytes = 0;
        if (is_dir($localDir)) {
            foreach (glob($localDir.DIRECTORY_SEPARATOR.'*.sql.gz') ?: [] as $file) {
                $localBytes += (int) @filesize($file);
            }
        }

        $tz = $setting->schedule_timezone ?: 'Asia/Kolkata';
        $time = $setting->schedule_time ?: '02:00';
        $nextRun = $this->computeNextRun($time, $tz);

        return Inertia::render('Backups/Index', [
            'settings' => $setting->toSafeArray(),
            'backups' => $backups,
            'stats' => [
                'total_backups' => DatabaseBackup::count(),
                'uploaded_count' => DatabaseBackup::where('status', 'uploaded')->count(),
                'failed_count' => DatabaseBackup::where('status', 'failed')->count(),
                'local_storage_human' => $this->formatBytes($localBytes),
                'database_driver' => (string) config('database.default'),
                'next_scheduled_run' => $setting->enabled ? $nextRun->format('d.m.Y h:i A T') : 'Paused',
                'oauth_redirect_uri' => route('backups.google.callback'),
                'cron_command' => '* * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1',
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'auth_mode' => ['required', 'in:oauth,service_account'],
            'schedule_time' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'schedule_timezone' => ['required', 'timezone'],
            'local_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'drive_retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:500'],
            'google_refresh_token' => ['nullable', 'string', 'max:2000'],
            'google_service_account_json' => ['nullable', 'string', 'max:25000'],
            'google_folder_id' => ['nullable', 'string', 'max:500'],
            'google_subfolder_name' => ['nullable', 'string', 'max:120'],
        ]);

        if (!empty($validated['google_service_account_json'])) {
            $decoded = json_decode($validated['google_service_account_json'], true);
            if (!is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
                return back()->withErrors([
                    'google_service_account_json' => 'Service Account JSON must contain valid client_email and private_key fields.',
                ]);
            }
        }

        $setting = DatabaseBackupSetting::current();
        $setting->enabled = (bool) $validated['enabled'];
        $setting->auth_mode = $validated['auth_mode'];
        $setting->schedule_time = $validated['schedule_time'];
        $setting->schedule_timezone = $validated['schedule_timezone'];
        $setting->local_retention_days = (int) $validated['local_retention_days'];
        $setting->drive_retention_days = (int) $validated['drive_retention_days'];
        $setting->google_client_id = trim((string) ($validated['google_client_id'] ?? '')) ?: null;
        $setting->google_folder_id = $this->normalizeFolderId($validated['google_folder_id'] ?? null);
        $setting->google_subfolder_name = trim((string) ($validated['google_subfolder_name'] ?? ''));

        if (!empty($validated['google_client_secret'])) {
            $setting->setGoogleClientSecret($validated['google_client_secret']);
        }
        if (!empty($validated['google_refresh_token'])) {
            $setting->setGoogleRefreshToken($validated['google_refresh_token']);
        }
        if (!empty($validated['google_service_account_json'])) {
            $setting->setGoogleServiceAccountJson($validated['google_service_account_json']);
        }

        $setting->updated_by = $user->id;
        $setting->save();

        AuditService::log(
            'backup.settings_updated',
            'DatabaseBackupSetting',
            $setting->id,
            "Database backup settings updated by {$user->name} (schedule: {$setting->schedule_time} {$setting->schedule_timezone}, mode: {$setting->auth_mode})"
        );

        return redirect()->route('backups.index')
            ->with('status', 'Database backup settings saved successfully.');
    }

    public function runNow(Request $request, DatabaseBackupService $backupService): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $localOnly = $request->boolean('local_only', false);

        $backup = $backupService->runBackup('manual', $user->id, $localOnly);

        if ($backup->status === 'failed') {
            return redirect()->route('backups.index')
                ->with('error', "Backup failed: {$backup->error_message}");
        }

        if ($backup->status === 'uploaded') {
            return redirect()->route('backups.index')
                ->with('status', "Backup '{$backup->file_name}' ({$backup->humanFileSize()}) completed and uploaded to Google Drive.");
        }

        return redirect()->route('backups.index')
            ->with('status', "Local backup '{$backup->file_name}' ({$backup->humanFileSize()}) created. ".($backup->error_message ?? ''));
    }

    public function testConnection(Request $request, GoogleDriveBackupClient $driveClient): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $setting = DatabaseBackupSetting::current();

        try {
            $info = $driveClient->verifyConnection($setting);
            if (!empty($info['email'])) {
                $setting->google_connected_email = $info['email'];
            }
            $setting->last_error = null;
            $setting->save();

            AuditService::log(
                'backup.connection_tested',
                'DatabaseBackupSetting',
                $setting->id,
                "Google Drive connection verified by {$user->name} ({$info['email']})"
            );

            $folderPart = $info['folder_name']
                ? " Target folder '{$info['folder_name']}' is accessible."
                : ' Subfolder will be auto-created inside Drive on upload.';

            return redirect()->route('backups.index')
                ->with('status', "Google Drive connected successfully ({$info['email']}).{$folderPart}");
        } catch (\Throwable $e) {
            $setting->update(['last_error' => mb_substr($e->getMessage(), 0, 1000)]);

            return redirect()->route('backups.index')
                ->with('error', 'Google Drive connection failed: '.$e->getMessage());
        }
    }

    public function connectGoogle(Request $request, GoogleDriveBackupClient $driveClient): SymfonyResponse
    {
        $this->authorizeAdmin($request);
        $setting = DatabaseBackupSetting::current();

        $clientId = $setting->effectiveClientId();
        $clientSecret = $setting->getGoogleClientSecret();

        if (!$clientId || !$clientSecret) {
            return redirect()->route('backups.index')
                ->with('error', 'Enter and save your Google OAuth Client ID and Client Secret first before connecting.');
        }

        $state = Str::random(40);
        $request->session()->put('google_drive_oauth_state', $state);

        $authUrl = $driveClient->buildAuthorizationUrl(
            $clientId,
            route('backups.google.callback'),
            $state
        );

        return redirect()->away($authUrl);
    }

    public function callbackGoogle(Request $request, GoogleDriveBackupClient $driveClient): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);

        if ($request->filled('error')) {
            return redirect()->route('backups.index')
                ->with('error', 'Google authorization was cancelled or denied: '.$request->string('error'));
        }

        $expectedState = (string) $request->session()->pull('google_drive_oauth_state', '');
        $actualState = (string) $request->input('state', '');
        if ($expectedState === '' || !hash_equals($expectedState, $actualState)) {
            return redirect()->route('backups.index')
                ->with('error', 'Invalid OAuth state parameter. Please click "Connect Google Drive Account" again.');
        }

        $code = (string) $request->input('code', '');
        if ($code === '') {
            return redirect()->route('backups.index')
                ->with('error', 'Missing authorization code from Google.');
        }

        $setting = DatabaseBackupSetting::current();
        $clientId = $setting->effectiveClientId();
        $clientSecret = $setting->getGoogleClientSecret();
        if (!$clientId || !$clientSecret) {
            return redirect()->route('backups.index')
                ->with('error', 'Google OAuth Client ID or Client Secret is missing.');
        }

        try {
            $tokens = $driveClient->exchangeAuthorizationCode(
                $clientId,
                $clientSecret,
                route('backups.google.callback'),
                $code
            );

            if (!empty($tokens['refresh_token'])) {
                $setting->setGoogleRefreshToken($tokens['refresh_token']);
            } elseif (!$setting->getGoogleRefreshToken()) {
                throw new \RuntimeException('Google did not return a refresh token. Please revoke prior app access in your Google Account and reconnect.');
            }

            $setting->auth_mode = 'oauth';
            $setting->google_connected_email = $tokens['email'] ?? $setting->google_connected_email;
            $setting->last_error = null;
            $setting->updated_by = $user->id;
            $setting->save();

            AuditService::log(
                'backup.google_connected',
                'DatabaseBackupSetting',
                $setting->id,
                "Google Drive OAuth connected by {$user->name} ({$setting->google_connected_email})"
            );

            return redirect()->route('backups.index')
                ->with('status', 'Google Drive connected successfully'.($setting->google_connected_email ? " ({$setting->google_connected_email})" : '').'!');
        } catch (\Throwable $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Could not complete Google Drive sign-in: '.$e->getMessage());
        }
    }

    public function disconnectGoogle(Request $request): RedirectResponse
    {
        $user = $this->authorizeAdmin($request);
        $setting = DatabaseBackupSetting::current();
        $setting->google_refresh_token_encrypted = null;
        $setting->google_connected_email = null;
        $setting->updated_by = $user->id;
        $setting->save();

        AuditService::log(
            'backup.google_disconnected',
            'DatabaseBackupSetting',
            $setting->id,
            "Google Drive OAuth token disconnected by {$user->name}"
        );

        return redirect()->route('backups.index')
            ->with('status', 'Google Drive OAuth connection cleared.');
    }

    public function download(Request $request, DatabaseBackup $databaseBackup): BinaryFileResponse
    {
        $user = $this->authorizeAdmin($request);

        if (!$databaseBackup->localFileExists()) {
            abort(404, 'Local backup archive has been pruned or is no longer available.');
        }

        $path = $databaseBackup->absoluteLocalPath();
        AuditService::log(
            'backup.downloaded',
            'DatabaseBackup',
            $databaseBackup->id,
            "Database backup {$databaseBackup->file_name} downloaded by {$user->name}"
        );

        return response()->download((string) $path, $databaseBackup->file_name, [
            'Content-Type' => 'application/gzip',
        ]);
    }

    public function destroy(
        Request $request,
        DatabaseBackup $databaseBackup,
        GoogleDriveBackupClient $driveClient
    ): RedirectResponse {
        $user = $this->authorizeAdmin($request);

        $localPath = $databaseBackup->absoluteLocalPath();
        if ($localPath && is_file($localPath)) {
            @unlink($localPath);
        }

        if ($request->boolean('delete_from_drive') && $databaseBackup->google_drive_file_id) {
            $setting = DatabaseBackupSetting::current();
            if ($setting->isGoogleDriveConfigured()) {
                try {
                    $token = $driveClient->getAccessToken($setting);
                    $driveClient->deleteFile($token, $databaseBackup->google_drive_file_id);
                } catch (\Throwable) {
                    // Continue removing record even if already removed in Drive.
                }
            }
        }

        $fileName = $databaseBackup->file_name;
        $id = $databaseBackup->id;
        $databaseBackup->delete();

        AuditService::log(
            'backup.deleted',
            'DatabaseBackup',
            $id,
            "Database backup record {$fileName} deleted by {$user->name}"
        );

        return redirect()->route('backups.index')
            ->with('status', "Backup record '{$fileName}' removed.");
    }

    private function authorizeAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized access to database backup management.');
        }

        return $user;
    }

    private function normalizeFolderId(?string $input): ?string
    {
        $trimmed = trim((string) $input);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('#/folders/([a-zA-Z0-9_-]+)#', $trimmed, $m)) {
            return $m[1];
        }

        return $trimmed;
    }

    private function computeNextRun(string $time, string $timezone): Carbon
    {
        try {
            $now = now($timezone);
            [$hour, $minute] = array_map('intval', explode(':', $time));
            $candidate = $now->copy()->setTime($hour, $minute, 0);
            if ($candidate->lessThanOrEqualTo($now)) {
                $candidate->addDay();
            }
            return $candidate;
        } catch (\Throwable) {
            return now('Asia/Kolkata')->addDay()->setTime(2, 0, 0);
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / (1024 * 1024), 2).' MB';
    }
}
