<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('attender_name')->nullable()->after('patient_name');
            $table->string('attender_relation')->nullable()->after('attender_name');
            $table->string('vital_bp')->nullable()->after('investigations');
            $table->string('vital_pulse')->nullable()->after('vital_bp');
            $table->string('vital_spo2')->nullable()->after('vital_pulse');
            $table->date('stent_removal_date')->nullable()->after('postoperative_orders');
            $table->string('discharge_bp')->nullable()->after('discharge_condition');
            $table->string('discharge_pulse')->nullable()->after('discharge_bp');
            $table->string('discharge_spo2')->nullable()->after('discharge_pulse');
            $table->string('post_surgery_diet')->nullable()->after('discharge_advice');
            $table->string('post_surgery_activity')->nullable()->after('post_surgery_diet');
            $table->date('suture_removal_date')->nullable()->after('follow_up');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn([
                'attender_name',
                'attender_relation',
                'vital_bp',
                'vital_pulse',
                'vital_spo2',
                'stent_removal_date',
                'discharge_bp',
                'discharge_pulse',
                'discharge_spo2',
                'post_surgery_diet',
                'post_surgery_activity',
                'suture_removal_date',
            ]);
        });
    }
};
