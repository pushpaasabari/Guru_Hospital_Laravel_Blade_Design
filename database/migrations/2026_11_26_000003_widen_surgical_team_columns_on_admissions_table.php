<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->text('surgeon')->nullable()->change();
            $table->text('assisted_surgeon')->nullable()->change();
            $table->text('pediatrician_name')->nullable()->change();
            $table->text('anaesthetist')->nullable()->change();
            $table->text('assisted_anaesthetist')->nullable()->change();
            $table->text('assistant')->nullable()->change();
            $table->text('assisted_by')->nullable()->change();
            $table->text('staff_nurse')->nullable()->change();
            $table->text('ot_technician')->nullable()->change();
            $table->text('anaesthesia_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('surgeon', 160)->nullable()->change();
            $table->string('assisted_surgeon', 160)->nullable()->change();
            $table->string('pediatrician_name', 160)->nullable()->change();
            $table->string('anaesthetist', 160)->nullable()->change();
            $table->string('assisted_anaesthetist', 160)->nullable()->change();
            $table->string('assistant', 160)->nullable()->change();
            $table->string('assisted_by', 160)->nullable()->change();
            $table->string('staff_nurse', 160)->nullable()->change();
            $table->string('ot_technician', 160)->nullable()->change();
            $table->string('anaesthesia_type', 160)->nullable()->change();
        });
    }
};
