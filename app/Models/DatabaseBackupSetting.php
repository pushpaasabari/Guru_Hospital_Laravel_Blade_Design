<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class DatabaseBackupSetting extends Model
{
    protected $fillable = [
        'enabled',
        'auth_mode',
        'schedule_time',
        'schedule_timezone',
        'local_retention_days',
        'drive_retention_days',
        'google_client_id',
        'google_client_secret_encrypted',
        'google_refresh_token_encrypted',
        'google_service_account_encrypted',
        'google_connected_email',
        'google_folder_id',
        'google_subfolder_name',
        'last_run_at',
        'last_success_at',
        'last_error',
        'updated_by',
    ];

    protected $hidden = [
        'google_client_secret_encrypted',
        'google_refresh_token_encrypted',
        'google_service_account_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'local_retention_days' => 'integer',
            'drive_retention_days' => 'integer',
            'last_run_at' => 'datetime',
            'last_success_at' => 'datetime',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        $existing = static::query()->first();
        if ($existing) {
            return $existing;
        }

        return static::create([
            'enabled' => (bool) config('backup.enabled', true),
            'auth_mode' => (string) config('backup.google_drive.auth_mode', 'oauth'),
            'schedule_time' => (string) config('backup.schedule_time', '02:00'),
            'schedule_timezone' => (string) config('backup.schedule_timezone', 'Asia/Kolkata'),
            'local_retention_days' => (int) config('backup.local_retention_days', 7),
            'drive_retention_days' => (int) config('backup.drive_retention_days', 30),
            'google_subfolder_name' => (string) (config('backup.google_drive.subfolder_name') ?: 'Guru_Hospital_DB_Backups'),
        ]);
    }

    public function setGoogleClientSecret(?string $plain): void
    {
        $trimmed = trim((string) $plain);
        $this->google_client_secret_encrypted = $trimmed !== '' ? Crypt::encryptString($trimmed) : null;
    }

    public function getGoogleClientSecret(): ?string
    {
        $dbSecret = $this->decryptNullable($this->google_client_secret_encrypted);
        return $dbSecret ?: (config('backup.google_drive.client_secret') ?: null);
    }

    public function setGoogleRefreshToken(?string $plain): void
    {
        $trimmed = trim((string) $plain);
        $this->google_refresh_token_encrypted = $trimmed !== '' ? Crypt::encryptString($trimmed) : null;
    }

    public function getGoogleRefreshToken(): ?string
    {
        $dbToken = $this->decryptNullable($this->google_refresh_token_encrypted);
        return $dbToken ?: (config('backup.google_drive.refresh_token') ?: null);
    }

    public function setGoogleServiceAccountJson(?string $json): void
    {
        $trimmed = trim((string) $json);
        $this->google_service_account_encrypted = $trimmed !== '' ? Crypt::encryptString($trimmed) : null;
    }

    public function getGoogleServiceAccountJson(): ?string
    {
        $dbJson = $this->decryptNullable($this->google_service_account_encrypted);
        if ($dbJson) {
            return $dbJson;
        }

        $envJson = config('backup.google_drive.service_account_json');
        if (is_string($envJson) && trim($envJson) !== '') {
            if (is_file($envJson) && is_readable($envJson)) {
                return file_get_contents($envJson) ?: null;
            }
            return trim($envJson);
        }

        return null;
    }

    public function effectiveClientId(): ?string
    {
        $val = trim((string) $this->google_client_id);
        return $val !== '' ? $val : (config('backup.google_drive.client_id') ?: null);
    }

    public function effectiveFolderId(): ?string
    {
        $val = trim((string) $this->google_folder_id);
        return $val !== '' ? $val : (config('backup.google_drive.folder_id') ?: null);
    }

    public function effectiveSubfolderName(): ?string
    {
        if ($this->google_subfolder_name !== null) {
            $trimmed = trim($this->google_subfolder_name);
            return $trimmed !== '' ? $trimmed : null;
        }

        $env = trim((string) config('backup.google_drive.subfolder_name', 'Guru_Hospital_DB_Backups'));
        return $env !== '' ? $env : null;
    }

    public function isGoogleDriveConfigured(): bool
    {
        if ($this->auth_mode === 'service_account') {
            return $this->getGoogleServiceAccountJson() !== null;
        }

        return $this->effectiveClientId() !== null
            && $this->getGoogleClientSecret() !== null
            && $this->getGoogleRefreshToken() !== null;
    }

    public function toSafeArray(): array
    {
        return [
            'enabled' => (bool) $this->enabled,
            'auth_mode' => $this->auth_mode ?: 'oauth',
            'schedule_time' => $this->schedule_time ?: '02:00',
            'schedule_timezone' => $this->schedule_timezone ?: 'Asia/Kolkata',
            'local_retention_days' => (int) $this->local_retention_days,
            'drive_retention_days' => (int) $this->drive_retention_days,
            'google_client_id' => $this->effectiveClientId() ?? '',
            'has_client_secret' => $this->getGoogleClientSecret() !== null,
            'has_refresh_token' => $this->getGoogleRefreshToken() !== null,
            'has_service_account_json' => $this->getGoogleServiceAccountJson() !== null,
            'google_connected_email' => $this->google_connected_email,
            'google_folder_id' => $this->effectiveFolderId() ?? '',
            'google_subfolder_name' => $this->effectiveSubfolderName() ?? '',
            'is_drive_configured' => $this->isGoogleDriveConfigured(),
            'last_run_at' => $this->last_run_at?->toIso8601String(),
            'last_success_at' => $this->last_success_at?->toIso8601String(),
            'last_error' => $this->last_error,
        ];
    }

    private function decryptNullable(?string $encrypted): ?string
    {
        if (!$encrypted) {
            return null;
        }

        try {
            $plain = Crypt::decryptString($encrypted);
            return trim($plain) !== '' ? $plain : null;
        } catch (DecryptException) {
            return null;
        }
    }
}
