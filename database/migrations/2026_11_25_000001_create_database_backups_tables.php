<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_backup_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('auth_mode', 32)->default('oauth');
            $table->string('schedule_time', 10)->default('02:00');
            $table->string('schedule_timezone', 64)->default('Asia/Kolkata');
            $table->unsignedInteger('local_retention_days')->default(7);
            $table->unsignedInteger('drive_retention_days')->default(30);
            $table->text('google_client_id')->nullable();
            $table->text('google_client_secret_encrypted')->nullable();
            $table->text('google_refresh_token_encrypted')->nullable();
            $table->longText('google_service_account_encrypted')->nullable();
            $table->string('google_connected_email')->nullable();
            $table->string('google_folder_id')->nullable();
            $table->string('google_subfolder_name')->default('Guru_Hospital_DB_Backups');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->text('last_error')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('disk_path')->nullable();
            $table->string('database_driver', 32);
            $table->string('database_name');
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('sha256_checksum', 64)->nullable();
            $table->string('status', 32)->default('running'); // running, local_only, uploaded, failed
            $table->string('trigger_type', 32)->default('manual'); // scheduled, manual, cli
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('google_drive_file_id')->nullable();
            $table->string('google_drive_web_link', 512)->nullable();
            $table->string('google_drive_folder_id')->nullable();
            $table->timestamp('google_drive_deleted_at')->nullable();
            $table->timestamp('local_deleted_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('google_drive_file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backups');
        Schema::dropIfExists('database_backup_settings');
    }
};
