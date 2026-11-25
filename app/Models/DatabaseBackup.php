<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseBackup extends Model
{
    protected $fillable = [
        'file_name',
        'disk_path',
        'database_driver',
        'database_name',
        'file_size_bytes',
        'sha256_checksum',
        'status',
        'trigger_type',
        'triggered_by',
        'google_drive_file_id',
        'google_drive_web_link',
        'google_drive_folder_id',
        'google_drive_deleted_at',
        'local_deleted_at',
        'duration_ms',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'duration_ms' => 'integer',
            'google_drive_deleted_at' => 'datetime',
            'local_deleted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function localFileExists(): bool
    {
        if (!$this->disk_path || $this->local_deleted_at !== null) {
            return false;
        }

        $fullPath = $this->absoluteLocalPath();
        return $fullPath !== null && is_file($fullPath);
    }

    public function absoluteLocalPath(): ?string
    {
        if (!$this->disk_path) {
            return null;
        }

        $safeName = basename($this->disk_path);
        return rtrim((string) config('backup.local_directory'), DIRECTORY_SEPARATOR.'/')
            .DIRECTORY_SEPARATOR.$safeName;
    }

    public function humanFileSize(): string
    {
        $bytes = (int) ($this->file_size_bytes ?? 0);
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / (1024 * 1024), 2).' MB';
    }
}
