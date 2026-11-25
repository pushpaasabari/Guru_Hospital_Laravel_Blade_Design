<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use App\Models\DatabaseBackupSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function __construct(
        private readonly GoogleDriveBackupClient $driveClient
    ) {
    }

    public function runBackup(
        string $triggerType = 'manual',
        ?int $triggeredBy = null,
        bool $localOnly = false
    ): DatabaseBackup {
        @set_time_limit(300);

        $startedAt = now();
        $startedMicro = microtime(true);
        $setting = DatabaseBackupSetting::current();

        $connectionName = config('database.default');
        $connectionConfig = (array) config("database.connections.{$connectionName}", []);
        $driver = (string) ($connectionConfig['driver'] ?? 'mysql');
        $databaseName = $this->resolveDatabaseLabel($driver, $connectionConfig);

        $tz = $setting->schedule_timezone ?: 'Asia/Kolkata';
        $timestamp = now($tz)->format('Y-m-d_H-i-s');
        $prefix = preg_replace('/[^a-zA-Z0-9_-]+/', '_', (string) config('backup.filename_prefix', 'guru_hospital_db'));
        $fileName = "{$prefix}_{$driver}_{$timestamp}.sql.gz";

        $backup = DatabaseBackup::create([
            'file_name' => $fileName,
            'disk_path' => null,
            'database_driver' => $driver,
            'database_name' => $databaseName,
            'status' => 'running',
            'trigger_type' => $triggerType,
            'triggered_by' => $triggeredBy,
            'started_at' => $startedAt,
        ]);

        try {
            $dir = (string) config('backup.local_directory', storage_path('app/private/backups'));
            File::ensureDirectoryExists($dir, 0750);
            $localPath = rtrim($dir, DIRECTORY_SEPARATOR.'/').DIRECTORY_SEPARATOR.$fileName;

            $this->createCompressedDump($connectionName, $driver, $connectionConfig, $localPath);

            $fileSize = is_file($localPath) ? (int) filesize($localPath) : 0;
            $checksum = is_file($localPath) ? (string) hash_file('sha256', $localPath) : null;

            $backup->update([
                'disk_path' => $fileName,
                'file_size_bytes' => $fileSize,
                'sha256_checksum' => $checksum,
            ]);

            if (!$localOnly && $setting->isGoogleDriveConfigured()) {
                $accessToken = $this->driveClient->getAccessToken($setting);
                $folderId = $this->driveClient->ensureTargetFolder(
                    $accessToken,
                    $setting->effectiveFolderId(),
                    $setting->effectiveSubfolderName()
                );

                $uploaded = $this->driveClient->uploadFile(
                    $accessToken,
                    $localPath,
                    $fileName,
                    $folderId,
                    'application/gzip'
                );

                $backup->update([
                    'status' => 'uploaded',
                    'google_drive_file_id' => $uploaded['id'],
                    'google_drive_web_link' => $uploaded['webViewLink'],
                    'google_drive_folder_id' => $folderId,
                    'completed_at' => now(),
                    'duration_ms' => (int) round((microtime(true) - $startedMicro) * 1000),
                ]);

                $setting->update([
                    'last_run_at' => now(),
                    'last_success_at' => now(),
                    'last_error' => null,
                ]);
            } else {
                $backup->update([
                    'status' => 'local_only',
                    'completed_at' => now(),
                    'duration_ms' => (int) round((microtime(true) - $startedMicro) * 1000),
                    'error_message' => (!$localOnly && !$setting->isGoogleDriveConfigured())
                        ? 'Google Drive is not connected yet; saved locally only.'
                        : null,
                ]);

                $setting->update([
                    'last_run_at' => now(),
                    'last_success_at' => now(),
                    'last_error' => (!$localOnly && !$setting->isGoogleDriveConfigured())
                        ? 'Google Drive is not connected yet. Configure credentials in Database Backups.'
                        : null,
                ]);
            }

            $this->pruneOldBackups($setting);

            AuditService::log(
                'backup.completed',
                'DatabaseBackup',
                $backup->id,
                "Database backup {$fileName} ({$backup->status}) via {$triggerType}"
            );

            return $backup->fresh();
        } catch (\Throwable $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
                'completed_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $startedMicro) * 1000),
            ]);

            $setting->update([
                'last_run_at' => now(),
                'last_error' => mb_substr($e->getMessage(), 0, 1000),
            ]);

            AuditService::log(
                'backup.failed',
                'DatabaseBackup',
                $backup->id,
                "Database backup failed ({$triggerType}): ".$e->getMessage()
            );

            return $backup->fresh();
        }
    }

    /**
     * @return array{local_pruned: int, drive_pruned: int}
     */
    public function pruneOldBackups(?DatabaseBackupSetting $setting = null): array
    {
        $setting = $setting ?? DatabaseBackupSetting::current();
        $localPruned = 0;
        $drivePruned = 0;

        $latestSuccessfulId = DatabaseBackup::query()
            ->whereIn('status', ['uploaded', 'local_only'])
            ->latest('id')
            ->value('id');

        if ($setting->local_retention_days > 0) {
            $localCutoff = now()->subDays($setting->local_retention_days);
            $oldLocals = DatabaseBackup::query()
                ->whereNull('local_deleted_at')
                ->whereNotNull('disk_path')
                ->where('status', '!=', 'running')
                ->where('created_at', '<', $localCutoff)
                ->when($latestSuccessfulId, fn ($q) => $q->where('id', '!=', $latestSuccessfulId))
                ->get();

            foreach ($oldLocals as $record) {
                $path = $record->absoluteLocalPath();
                if ($path && is_file($path)) {
                    @unlink($path);
                }
                $record->update(['local_deleted_at' => now()]);
                $localPruned++;
            }

            // Prune any orphaned archives in the backups directory past retention.
            $dir = (string) config('backup.local_directory', storage_path('app/private/backups'));
            if (is_dir($dir)) {
                $cutoffTs = $localCutoff->timestamp;
                $keepPath = $latestSuccessfulId
                    ? DatabaseBackup::find($latestSuccessfulId)?->absoluteLocalPath()
                    : null;

                foreach (glob($dir.DIRECTORY_SEPARATOR.'*.sql.gz') ?: [] as $file) {
                    if ($keepPath && realpath($file) === realpath($keepPath)) {
                        continue;
                    }
                    if (@filemtime($file) < $cutoffTs) {
                        @unlink($file);
                    }
                }
            }
        }

        $latestDriveId = DatabaseBackup::query()
            ->where('status', 'uploaded')
            ->whereNull('google_drive_deleted_at')
            ->latest('id')
            ->value('id');

        if ($setting->drive_retention_days > 0 && $setting->isGoogleDriveConfigured()) {
            $driveCutoff = now()->subDays($setting->drive_retention_days);
            $oldRemote = DatabaseBackup::query()
                ->where('status', 'uploaded')
                ->whereNull('google_drive_deleted_at')
                ->whereNotNull('google_drive_file_id')
                ->where('created_at', '<', $driveCutoff)
                ->when($latestDriveId, fn ($q) => $q->where('id', '!=', $latestDriveId))
                ->get();

            if ($oldRemote->isNotEmpty()) {
                try {
                    $accessToken = $this->driveClient->getAccessToken($setting);
                    foreach ($oldRemote as $record) {
                        $this->driveClient->deleteFile($accessToken, (string) $record->google_drive_file_id);
                        $record->update(['google_drive_deleted_at' => now()]);
                        $drivePruned++;
                    }
                } catch (\Throwable) {
                    // Do not fail completed backups if remote retention pruning hits a transient error.
                }
            }
        }

        return [
            'local_pruned' => $localPruned,
            'drive_pruned' => $drivePruned,
        ];
    }

    private function createCompressedDump(
        string $connectionName,
        string $driver,
        array $config,
        string $targetGzPath
    ): void {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            if ($this->tryMysqldumpToGzip($config, $targetGzPath)) {
                return;
            }
            $this->dumpMySqlViaPdoToGzip($connectionName, $config, $targetGzPath);
            return;
        }

        if ($driver === 'sqlite') {
            $this->dumpSqliteViaPdoToGzip($connectionName, $config, $targetGzPath);
            return;
        }

        if ($driver === 'pgsql') {
            if ($this->tryPgDumpToGzip($config, $targetGzPath)) {
                return;
            }
        }

        throw new RuntimeException("Unsupported database driver for backup: {$driver}");
    }

    private function tryMysqldumpToGzip(array $config, string $targetGzPath): bool
    {
        $binary = $this->findBinary([
            'mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysqldump.exe',
        ]);

        if (!$binary) {
            return false;
        }

        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '3306');
        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        $socket = (string) ($config['unix_socket'] ?? '');

        if ($database === '' || $username === '') {
            return false;
        }

        $args = [
            $binary,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--quick',
            '--hex-blob',
            '--default-character-set=utf8mb4',
            '--user='.$username,
        ];

        if ($socket !== '') {
            $args[] = '--socket='.$socket;
        } else {
            $args[] = '--host='.$host;
            $args[] = '--port='.$port;
        }

        $args[] = $database;

        $env = $password !== '' ? ['MYSQL_PWD' => $password] : [];
        $gz = gzopen($targetGzPath, 'wb9');
        if ($gz === false) {
            return false;
        }

        $process = new Process($args, null, $env, null, 300);
        try {
            $process->run(function (string $type, string $buffer) use ($gz): void {
                if ($type === Process::OUT && $buffer !== '') {
                    gzwrite($gz, $buffer);
                }
            });
            gzclose($gz);

            if ($process->isSuccessful() && is_file($targetGzPath) && filesize($targetGzPath) > 0) {
                return true;
            }
        } catch (\Throwable) {
            @gzclose($gz);
        }

        @unlink($targetGzPath);
        return false;
    }

    private function tryPgDumpToGzip(array $config, string $targetGzPath): bool
    {
        $binary = $this->findBinary(['pg_dump', '/usr/bin/pg_dump', '/usr/local/bin/pg_dump']);
        if (!$binary) {
            return false;
        }

        $args = [
            $binary,
            '--no-owner',
            '--no-privileges',
            '-h', (string) ($config['host'] ?? '127.0.0.1'),
            '-p', (string) ($config['port'] ?? '5432'),
            '-U', (string) ($config['username'] ?? ''),
            (string) ($config['database'] ?? ''),
        ];

        $env = !empty($config['password']) ? ['PGPASSWORD' => (string) $config['password']] : [];
        $gz = gzopen($targetGzPath, 'wb9');
        if ($gz === false) {
            return false;
        }

        $process = new Process($args, null, $env, null, 300);
        try {
            $process->run(function (string $type, string $buffer) use ($gz): void {
                if ($type === Process::OUT && $buffer !== '') {
                    gzwrite($gz, $buffer);
                }
            });
            gzclose($gz);

            if ($process->isSuccessful() && is_file($targetGzPath) && filesize($targetGzPath) > 0) {
                return true;
            }
        } catch (\Throwable) {
            @gzclose($gz);
        }

        @unlink($targetGzPath);
        return false;
    }

    private function dumpMySqlViaPdoToGzip(string $connectionName, array $config, string $targetGzPath): void
    {
        $pdo = DB::connection($connectionName)->getPdo();
        $database = (string) ($config['database'] ?? 'mysql');

        $gz = gzopen($targetGzPath, 'wb9');
        if ($gz === false) {
            throw new RuntimeException("Unable to open compressed backup target {$targetGzPath}.");
        }

        try {
            gzwrite($gz, "-- Guru Hospital Database Backup (MySQL PDO)\n");
            gzwrite($gz, "-- Database: {$database}\n");
            gzwrite($gz, '-- Generated: '.now('Asia/Kolkata')->toIso8601String()."\n\n");
            gzwrite($gz, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tables = $tablesStmt ? $tablesStmt->fetchAll(PDO::FETCH_COLUMN, 0) : [];

            foreach ($tables as $table) {
                $quotedTable = '`'.str_replace('`', '``', (string) $table).'`';
                $createStmt = $pdo->query("SHOW CREATE TABLE {$quotedTable}");
                $createRow = $createStmt ? $createStmt->fetch(PDO::FETCH_NUM) : null;
                if (!$createRow || empty($createRow[1])) {
                    continue;
                }

                gzwrite($gz, "-- Table: {$table}\n");
                gzwrite($gz, "DROP TABLE IF EXISTS {$quotedTable};\n");
                gzwrite($gz, $createRow[1].";\n\n");

                $this->writeTableInserts($pdo, $table, $gz, '`');
            }

            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            gzclose($gz);
        }
    }

    private function dumpSqliteViaPdoToGzip(string $connectionName, array $config, string $targetGzPath): void
    {
        $pdo = DB::connection($connectionName)->getPdo();
        $database = (string) ($config['database'] ?? 'sqlite');

        $gz = gzopen($targetGzPath, 'wb9');
        if ($gz === false) {
            throw new RuntimeException("Unable to open compressed backup target {$targetGzPath}.");
        }

        try {
            gzwrite($gz, "-- Guru Hospital Database Backup (SQLite PDO)\n");
            gzwrite($gz, "-- Database: {$database}\n");
            gzwrite($gz, '-- Generated: '.now('Asia/Kolkata')->toIso8601String()."\n\n");
            gzwrite($gz, "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n\n");

            $tablesStmt = $pdo->query(
                "SELECT name, sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            );
            $tables = $tablesStmt ? $tablesStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            foreach ($tables as $tableInfo) {
                $table = (string) $tableInfo['name'];
                $createSql = (string) ($tableInfo['sql'] ?? '');
                if ($createSql === '') {
                    continue;
                }

                $quotedTable = '"'.str_replace('"', '""', $table).'"';
                gzwrite($gz, "-- Table: {$table}\n");
                gzwrite($gz, "DROP TABLE IF EXISTS {$quotedTable};\n");
                gzwrite($gz, $createSql.";\n\n");

                $this->writeTableInserts($pdo, $table, $gz, '"');
            }

            $indexStmt = $pdo->query(
                "SELECT sql FROM sqlite_master WHERE type = 'index' AND sql IS NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY name"
            );
            $indexes = $indexStmt ? $indexStmt->fetchAll(PDO::FETCH_COLUMN, 0) : [];
            if (!empty($indexes)) {
                gzwrite($gz, "-- Indexes\n");
                foreach ($indexes as $indexSql) {
                    gzwrite($gz, $indexSql.";\n");
                }
                gzwrite($gz, "\n");
            }

            gzwrite($gz, "COMMIT;\nPRAGMA foreign_keys=ON;\n");
        } finally {
            gzclose($gz);
        }
    }

    /**
     * @param resource $gz
     */
    private function writeTableInserts(PDO $pdo, string $table, $gz, string $identQuote): void
    {
        $q = fn (string $name): string => $identQuote.str_replace($identQuote, $identQuote.$identQuote, $name).$identQuote;
        $quotedTable = $q($table);

        $stmt = $pdo->query("SELECT * FROM {$quotedTable}");
        if (!$stmt) {
            return;
        }

        $columns = null;
        $batch = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($columns === null) {
                $columns = implode(', ', array_map($q, array_keys($row)));
            }

            $values = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = 'NULL';
                } elseif (is_int($value) || is_float($value)) {
                    $values[] = (string) $value;
                } elseif (is_bool($value)) {
                    $values[] = $value ? '1' : '0';
                } else {
                    $values[] = $pdo->quote((string) $value);
                }
            }

            $batch[] = '('.implode(', ', $values).')';
            if (count($batch) >= 200) {
                gzwrite($gz, "INSERT INTO {$quotedTable} ({$columns}) VALUES\n".implode(",\n", $batch).";\n");
                $batch = [];
            }
        }

        if (!empty($batch) && $columns !== null) {
            gzwrite($gz, "INSERT INTO {$quotedTable} ({$columns}) VALUES\n".implode(",\n", $batch).";\n\n");
        }
    }

    private function resolveDatabaseLabel(string $driver, array $config): string
    {
        $db = (string) ($config['database'] ?? $driver);
        if ($driver === 'sqlite' && $db !== ':memory:') {
            return basename($db);
        }

        return $db !== '' ? $db : $driver;
    }

    private function findBinary(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (str_contains($candidate, DIRECTORY_SEPARATOR) || str_contains($candidate, '/')) {
                if (is_file($candidate)) {
                    return $candidate;
                }
                continue;
            }

            $check = new Process([PHP_OS_FAMILY === 'Windows' ? 'where' : 'which', $candidate]);
            $check->run();
            if ($check->isSuccessful()) {
                $resolved = trim(strtok($check->getOutput(), "\r\n") ?: '');
                if ($resolved !== '') {
                    return $resolved;
                }
            }
        }

        return null;
    }
}
