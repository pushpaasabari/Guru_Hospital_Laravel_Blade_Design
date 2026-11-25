<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\DatabaseBackup;
use App\Models\DatabaseBackupSetting;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $nurse;
    private User $doctor;
    private User $auditor;
    private string $testBackupDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBackupDir = storage_path('app/private/test_backups_'.uniqid());
        config(['backup.local_directory' => $this->testBackupDir]);

        $this->admin = User::create([
            'name' => 'Hospital Admin',
            'email' => 'admin@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'administrator',
            'is_active' => true,
        ]);

        $this->nurse = User::create([
            'name' => 'Nurse Anitha',
            'email' => 'nurse@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'nursing_staff',
            'is_active' => true,
        ]);

        $this->doctor = User::create([
            'name' => 'Dr. Ambika',
            'email' => 'doctor@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $this->auditor = User::create([
            'name' => 'Auditor',
            'email' => 'auditor@guruhospital.local',
            'password' => bcrypt('Secret123!'),
            'role' => 'auditor',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->testBackupDir);
        parent::tearDown();
    }

    public function test_only_administrator_can_access_backup_endpoints_and_secrets_are_not_leaked(): void
    {
        $this->get(route('backups.index'))->assertRedirect(route('login'));

        foreach ([$this->nurse, $this->doctor, $this->auditor] as $user) {
            $this->actingAs($user)->get(route('backups.index'))->assertForbidden();
            $this->actingAs($user)->put(route('backups.settings.update'), [])->assertForbidden();
            $this->actingAs($user)->post(route('backups.run'))->assertForbidden();
            $this->actingAs($user)->post(route('backups.test'))->assertForbidden();
            $this->actingAs($user)->get(route('backups.google.connect'))->assertForbidden();
        }

        $setting = DatabaseBackupSetting::current();
        $setting->google_client_id = 'test-client-id.apps.googleusercontent.com';
        $setting->setGoogleClientSecret('SUPER-SECRET-VALUE-999');
        $setting->setGoogleRefreshToken('1//SUPER-REFRESH-TOKEN-999');
        $setting->save();

        $this->actingAs($this->admin)
            ->get(route('backups.index'))
            ->assertOk()
            ->assertDontSee('SUPER-SECRET-VALUE-999', false)
            ->assertDontSee('1//SUPER-REFRESH-TOKEN-999', false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backups/Index')
                ->where('settings.has_client_secret', true)
                ->where('settings.has_refresh_token', true)
                ->where('settings.is_drive_configured', true)
                ->where('settings.schedule_time', '02:00')
                ->where('settings.schedule_timezone', 'Asia/Kolkata')
                ->where('settings.drive_retention_days', 30)
                ->where('settings.local_retention_days', 7)
            );
    }

    public function test_admin_can_update_backup_settings_with_encrypted_secrets_and_folder_url_extraction(): void
    {
        $this->actingAs($this->admin)
            ->put(route('backups.settings.update'), [
                'enabled' => true,
                'auth_mode' => 'oauth',
                'schedule_time' => '03:15',
                'schedule_timezone' => 'Asia/Kolkata',
                'local_retention_days' => 10,
                'drive_retention_days' => 45,
                'google_client_id' => 'client-123.apps.googleusercontent.com',
                'google_client_secret' => 'plain-secret-abc',
                'google_refresh_token' => '1//refresh-xyz',
                'google_folder_id' => 'https://drive.google.com/drive/folders/1AbCdEfGhIjK99?usp=sharing',
                'google_subfolder_name' => 'Hospital_Daily_SQL',
            ])
            ->assertRedirect(route('backups.index'));

        $setting = DatabaseBackupSetting::current()->fresh();
        $this->assertEquals('03:15', $setting->schedule_time);
        $this->assertEquals(10, $setting->local_retention_days);
        $this->assertEquals(45, $setting->drive_retention_days);
        $this->assertEquals('1AbCdEfGhIjK99', $setting->google_folder_id);
        $this->assertNotEquals('plain-secret-abc', $setting->google_client_secret_encrypted);
        $this->assertEquals('plain-secret-abc', $setting->getGoogleClientSecret());
        $this->assertEquals('1//refresh-xyz', $setting->getGoogleRefreshToken());
    }

    public function test_google_oauth_connect_and_callback_stores_encrypted_refresh_token(): void
    {
        $setting = DatabaseBackupSetting::current();
        $setting->google_client_id = 'client-oauth-1.apps.googleusercontent.com';
        $setting->setGoogleClientSecret('secret-oauth-1');
        $setting->save();

        $connectResp = $this->actingAs($this->admin)->get(route('backups.google.connect'));
        $connectResp->assertRedirect();
        $targetUrl = (string) $connectResp->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $targetUrl);

        parse_str((string) parse_url($targetUrl, PHP_URL_QUERY), $queryParams);
        $this->assertNotEmpty($queryParams['state'] ?? null);
        $this->assertEquals('offline', $queryParams['access_type'] ?? null);

        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'ya29.test-access-token',
                'refresh_token' => '1//brand-new-refresh-token',
                'token_type' => 'Bearer',
            ], 200),
            'https://www.googleapis.com/oauth2/v2/userinfo' => Http::response([
                'email' => 'backup@guruhospital.org',
            ], 200),
        ]);

        // Mismatched state is rejected safely
        $this->actingAs($this->admin)
            ->get(route('backups.google.callback', ['state' => 'tampered-state', 'code' => 'auth-code']))
            ->assertRedirect(route('backups.index'))
            ->assertSessionHas('error');

        // Re-initiate state and complete callback
        $connectResp2 = $this->actingAs($this->admin)->get(route('backups.google.connect'));
        parse_str((string) parse_url((string) $connectResp2->headers->get('Location'), PHP_URL_QUERY), $qp2);

        $this->actingAs($this->admin)
            ->get(route('backups.google.callback', ['state' => $qp2['state'], 'code' => 'valid-auth-code']))
            ->assertRedirect(route('backups.index'))
            ->assertSessionHas('status');

        $fresh = $setting->fresh();
        $this->assertEquals('backup@guruhospital.org', $fresh->google_connected_email);
        $this->assertEquals('1//brand-new-refresh-token', $fresh->getGoogleRefreshToken());
    }

    public function test_daily_backup_creates_valid_gzip_sql_dump_and_uploads_to_google_drive_via_oauth(): void
    {
        Admission::create([
            'patient_name' => 'BACKUP VERIFY PATIENT',
            'uhid' => 'UHID-BK-001',
            'ip_no' => 'IP-BK-001',
            'format' => 'general',
            'surgery_type' => 'other',
            'diagnosis' => 'Cholelithiasis',
            'admission_at' => now(),
            'created_by' => $this->admin->id,
            'status' => 'draft',
        ]);

        $setting = DatabaseBackupSetting::current();
        $setting->auth_mode = 'oauth';
        $setting->google_client_id = 'client-123';
        $setting->setGoogleClientSecret('secret-123');
        $setting->setGoogleRefreshToken('1//refresh-123');
        $setting->google_subfolder_name = 'Guru_Hospital_DB_Backups';
        $setting->save();

        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'ya29.mock-token',
                'token_type' => 'Bearer',
            ], 200),
            'https://www.googleapis.com/drive/v3/files?*' => Http::response([
                'files' => [],
            ], 200),
            'https://www.googleapis.com/drive/v3/files?supportsAllDrives=true&fields=id,name' => Http::response([
                'id' => 'drive-folder-001',
                'name' => 'Guru_Hospital_DB_Backups',
            ], 200),
            'https://www.googleapis.com/upload/drive/v3/files?*' => Http::response(
                null,
                200,
                ['Location' => 'https://upload.example.test/resumable/session-1']
            ),
            'https://upload.example.test/resumable/session-1' => Http::response([
                'id' => 'drive-file-999',
                'name' => 'guru_hospital_db.sql.gz',
                'webViewLink' => 'https://drive.google.com/file/d/drive-file-999/view',
                'size' => '4096',
            ], 200),
        ]);

        $this->artisan('db:backup')
            ->assertExitCode(0);

        $backup = DatabaseBackup::latest('id')->first();
        $this->assertNotNull($backup);
        $this->assertEquals('uploaded', $backup->status);
        $this->assertEquals('drive-file-999', $backup->google_drive_file_id);
        $this->assertEquals('https://drive.google.com/file/d/drive-file-999/view', $backup->google_drive_web_link);
        $this->assertTrue($backup->localFileExists());

        $compressedBytes = file_get_contents((string) $backup->absoluteLocalPath());
        $sqlContent = gzdecode((string) $compressedBytes);
        $this->assertNotFalse($sqlContent);
        $this->assertStringContainsString('BACKUP VERIFY PATIENT', $sqlContent);
        $this->assertStringContainsString('UHID-BK-001', $sqlContent);

        // Verify Admin can download the local compressed archive
        $download = $this->actingAs($this->admin)->get(route('backups.download', $backup));
        $download->assertOk();
        $this->assertStringContainsString('.sql.gz', (string) $download->headers->get('Content-Disposition'));
    }

    public function test_service_account_mode_signs_jwt_and_surfaces_quota_hint_on_403(): void
    {
        $pkeyArgs = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = @openssl_pkey_new($pkeyArgs);
        if ($res === false) {
            foreach ([
                'C:/xampp/php/extras/openssl/openssl.cnf',
                'C:/xampp/apache/conf/openssl.cnf',
                'C:/xampp/php/extras/ssl/openssl.cnf',
            ] as $cnfPath) {
                if (is_file($cnfPath)) {
                    $pkeyArgs['config'] = $cnfPath;
                    $res = @openssl_pkey_new($pkeyArgs);
                    if ($res !== false) {
                        break;
                    }
                }
            }
        }

        $this->assertNotFalse($res);
        openssl_pkey_export($res, $privateKeyPem, null, $pkeyArgs);

        $serviceAccountJson = json_encode([
            'type' => 'service_account',
            'client_email' => 'backup-bot@hospital-proj.iam.gserviceaccount.com',
            'private_key' => $privateKeyPem,
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ]);

        $setting = DatabaseBackupSetting::current();
        $setting->auth_mode = 'service_account';
        $setting->setGoogleServiceAccountJson($serviceAccountJson);
        $setting->google_folder_id = 'shared-drive-folder-1';
        $setting->google_subfolder_name = '';
        $setting->save();

        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => function (HttpRequest $request) {
                $this->assertEquals('urn:ietf:params:oauth:grant-type:jwt-bearer', $request['grant_type']);
                $this->assertNotEmpty($request['assertion']);
                return Http::response(['access_token' => 'ya29.sa-token'], 200);
            },
            'https://www.googleapis.com/upload/drive/v3/files?*' => Http::response([
                'error' => [
                    'code' => 403,
                    'message' => 'storageQuotaExceeded: Service Accounts do not have storage quota.',
                ],
            ], 403),
        ]);

        $this->artisan('db:backup')->assertExitCode(1);

        $backup = DatabaseBackup::latest('id')->first();
        $this->assertEquals('failed', $backup->status);
        $this->assertTrue($backup->localFileExists(), 'Local archive must be preserved even if Drive upload fails.');
        $this->assertStringContainsString('use OAuth 2.0 mode', (string) $backup->error_message);
    }

    public function test_retention_cleanup_prunes_expired_local_and_drive_backups_while_preserving_latest(): void
    {
        File::ensureDirectoryExists($this->testBackupDir);

        $setting = DatabaseBackupSetting::current();
        $setting->auth_mode = 'oauth';
        $setting->local_retention_days = 7;
        $setting->drive_retention_days = 30;
        $setting->google_client_id = 'client-123';
        $setting->setGoogleClientSecret('secret-123');
        $setting->setGoogleRefreshToken('1//refresh-123');
        $setting->save();

        $oldPath = $this->testBackupDir.DIRECTORY_SEPARATOR.'old_backup.sql.gz';
        $newPath = $this->testBackupDir.DIRECTORY_SEPARATOR.'new_backup.sql.gz';
        file_put_contents($oldPath, gzencode('-- old sql'));
        file_put_contents($newPath, gzencode('-- new sql'));

        $oldBackup = DatabaseBackup::create([
            'file_name' => 'old_backup.sql.gz',
            'disk_path' => 'old_backup.sql.gz',
            'database_driver' => 'sqlite',
            'database_name' => 'test',
            'file_size_bytes' => 50,
            'status' => 'uploaded',
            'trigger_type' => 'scheduled',
            'google_drive_file_id' => 'drive-old-id',
        ]);
        $oldBackup->created_at = now()->subDays(35);
        $oldBackup->save();

        $newBackup = DatabaseBackup::create([
            'file_name' => 'new_backup.sql.gz',
            'disk_path' => 'new_backup.sql.gz',
            'database_driver' => 'sqlite',
            'database_name' => 'test',
            'file_size_bytes' => 50,
            'status' => 'uploaded',
            'trigger_type' => 'scheduled',
            'google_drive_file_id' => 'drive-new-id',
        ]);

        Http::preventStrayRequests();
        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.prune'], 200),
            'https://www.googleapis.com/drive/v3/files/drive-old-id*' => Http::response('', 204),
        ]);

        $res = app(DatabaseBackupService::class)->pruneOldBackups($setting);

        $this->assertEquals(1, $res['local_pruned']);
        $this->assertEquals(1, $res['drive_pruned']);
        $this->assertFileDoesNotExist($oldPath);
        $this->assertFileExists($newPath);
        $this->assertNotNull($oldBackup->fresh()->local_deleted_at);
        $this->assertNotNull($oldBackup->fresh()->google_drive_deleted_at);
        $this->assertNull($newBackup->fresh()->google_drive_deleted_at);
    }

    public function test_daily_backup_command_is_registered_on_laravel_scheduler(): void
    {
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        $backupEvent = $events->first(
            fn ($event) => str_contains((string) $event->command, 'db:backup')
        );

        $this->assertNotNull($backupEvent, 'Daily db:backup command must be scheduled in routes/console.php');
        $this->assertEquals('0 2 * * *', $backupEvent->expression);
        $this->assertEquals('Asia/Kolkata', $backupEvent->timezone);
    }
}
