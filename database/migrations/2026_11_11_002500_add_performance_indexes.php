<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->index('status');
            $table->index('format');
            $table->index('surgery_type');
            $table->index('patient_name');
            $table->index('admission_at');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });

        Schema::table('document_revisions', function (Blueprint $table) {
            $table->index(['admission_id', 'revision_number']);
        });
    }

    public function down(): void
    {
        Schema::table('document_revisions', function (Blueprint $table) {
            $table->dropIndex(['admission_id', 'revision_number']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['auditable_type', 'auditable_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['format']);
            $table->dropIndex(['surgery_type']);
            $table->dropIndex(['patient_name']);
            $table->dropIndex(['admission_at']);
        });
    }
};

