<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup
        {--local-only : Save compressed backup locally without uploading to Google Drive}
        {--prune-only : Only apply retention cleanup without creating a new dump}
        {--trigger=cli : Execution source (cli, scheduled, manual)}';

    protected $description = 'Create a compressed database backup and upload it to Google Drive.';

    public function handle(DatabaseBackupService $backupService): int
    {
        if ($this->option('prune-only')) {
            $res = $backupService->pruneOldBackups();
            $this->info("Pruned {$res['local_pruned']} local backup(s) and {$res['drive_pruned']} Google Drive backup(s).");
            return self::SUCCESS;
        }

        $trigger = (string) ($this->option('trigger') ?: 'cli');
        $localOnly = (bool) $this->option('local-only');

        $backup = $backupService->runBackup($trigger, null, $localOnly);

        if ($backup->status === 'failed') {
            $this->error("Database backup failed: {$backup->error_message}");
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Database backup %s completed [%s] (%s).',
            $backup->file_name,
            $backup->status,
            $backup->humanFileSize()
        ));

        return self::SUCCESS;
    }
}
