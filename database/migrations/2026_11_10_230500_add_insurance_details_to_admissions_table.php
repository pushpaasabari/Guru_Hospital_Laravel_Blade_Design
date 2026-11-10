<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('insurance_type', 80)->nullable()->after('payment_mode');
            $table->string('insurance_company', 160)->nullable()->after('insurance_type');
            $table->string('policy_number', 100)->nullable()->after('insurance_company');
            $table->string('pre_auth_no', 100)->nullable()->after('policy_number');
            $table->string('pre_auth_status', 50)->nullable()->after('pre_auth_no');
            $table->decimal('approved_amount', 12, 2)->nullable()->after('pre_auth_status');
            $table->string('policy_holder_name', 160)->nullable()->after('approved_amount');
            $table->string('policy_holder_relation', 60)->nullable()->after('policy_holder_name');
            $table->date('policy_validity')->nullable()->after('policy_holder_relation');
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn([
                'insurance_type',
                'insurance_company',
                'policy_number',
                'pre_auth_no',
                'pre_auth_status',
                'approved_amount',
                'policy_holder_name',
                'policy_holder_relation',
                'policy_validity',
            ]);
        });
    }
};

