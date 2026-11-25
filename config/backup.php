<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Automated Daily Database Backup Configuration
    |--------------------------------------------------------------------------
    |
    | Settings saved from Administration -> Database Backups override these
    | .env defaults when populated. Secrets stored in the database are encrypted
    | at rest using Laravel's application encryption key.
    |
    */

    'enabled' => filter_var(env('DB_BACKUP_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'schedule_time' => env('DB_BACKUP_TIME', '02:00'),

    'schedule_timezone' => env('DB_BACKUP_TIMEZONE', 'Asia/Kolkata'),

    'local_directory' => storage_path('app/private/backups'),

    'filename_prefix' => env('DB_BACKUP_PREFIX', 'guru_hospital_db'),

    'local_retention_days' => (int) env('DB_BACKUP_LOCAL_RETENTION_DAYS', 7),

    'drive_retention_days' => (int) env('DB_BACKUP_DRIVE_RETENTION_DAYS', 30),

    'google_drive' => [
        // Supported modes: 'oauth' (personal Gmail & Workspace Drive) or 'service_account' (Workspace Shared Drive)
        'auth_mode' => env('GOOGLE_DRIVE_AUTH_MODE', 'oauth'),
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
        'service_account_json' => env('GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        'subfolder_name' => env('GOOGLE_DRIVE_SUBFOLDER', 'Guru_Hospital_DB_Backups'),
        'upload_chunk_bytes' => (int) env('GOOGLE_DRIVE_UPLOAD_CHUNK_BYTES', 8 * 1024 * 1024),
    ],
];
