<?php

use App\Models\DatabaseBackupSetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$backupEnabled = (bool) config('backup.enabled', true);
$backupTime = (string) config('backup.schedule_time', '02:00');
$backupTimezone = (string) config('backup.schedule_timezone', 'Asia/Kolkata');

try {
    if (Schema::hasTable('database_backup_settings')) {
        $setting = DatabaseBackupSetting::query()->first();
        if ($setting) {
            $backupEnabled = (bool) $setting->enabled;
            $backupTime = $setting->schedule_time ?: $backupTime;
            $backupTimezone = $setting->schedule_timezone ?: $backupTimezone;
        }
    }
} catch (\Throwable) {
    // Database may not be migrated yet during install/build commands.
}

if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $backupTime)) {
    $backupTime = '02:00';
}

if ($backupEnabled) {
    Schedule::command('db:backup --trigger=scheduled')
        ->dailyAt($backupTime)
        ->timezone($backupTimezone)
        ->withoutOverlapping(30);
}

