<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('assisted_surgeon', 160)->nullable()->after('surgeon');
            $table->string('assisted_anaesthetist', 160)->nullable()->after('anaesthetist');
            $table->string('staff_nurse', 160)->nullable()->after('assisted_by');
            $table->string('ot_technician', 160)->nullable()->after('staff_nurse');
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn([
                'assisted_surgeon',
                'assisted_anaesthetist',
                'staff_nurse',
                'ot_technician',
            ]);
        });
    }
};
