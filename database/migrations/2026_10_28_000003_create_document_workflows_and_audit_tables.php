<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Document Approval History
        Schema::create('document_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->string('action', 50)->comment('submitted_for_review, returned_for_correction, approved, generated');
            $table->foreignId('performed_by')->constrained('users');
            $table->string('user_role', 50);
            $table->text('notes_or_correction_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Document Revisions (Immutable snapshots)
        Schema::create('document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->unsignedInteger('revision_number')->default(1);
            $table->json('snapshot_data')->comment('Full JSON state of Admission, Newborns, OT Notes, and MRD');
            $table->string('pdf_storage_path')->nullable();
            $table->foreignId('approved_by')->constrained('users');
            $table->timestamp('approved_at');
            $table->timestamp('created_at')->useCurrent();
        });

        // Template Versions
        Schema::create('template_versions', function (Blueprint $table) {
            $table->id();
            $table->string('template_code', 50)->index()->comment('general, cmchistn, private, employee, ot_notes, mrd');
            $table->string('version_tag', 30);
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->boolean('is_approved_by_hospital')->default(false);
            $table->string('approved_by_name')->nullable();
            $table->date('effective_date')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Audit Logs (Append-only)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 150)->nullable();
            $table->string('user_role', 50)->nullable();
            $table->string('action', 100)->index();
            $table->string('auditable_type', 100)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->text('description')->nullable();
            $table->json('payload_before')->nullable();
            $table->json('payload_after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('template_versions');
        Schema::dropIfExists('document_revisions');
        Schema::dropIfExists('document_approvals');
    }
};

