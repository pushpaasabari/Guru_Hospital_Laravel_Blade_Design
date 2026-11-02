<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('address_line1', 255)->nullable()->after('address');
            $table->string('address_line2', 255)->nullable()->after('address_line1');
            $table->string('address_line3', 255)->nullable()->after('address_line2');
        });

        // Backfill address_line1 from existing address column where available
        DB::statement("UPDATE admissions SET address_line1 = address WHERE address_line1 IS NULL AND address IS NOT NULL");
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['address_line1', 'address_line2', 'address_line3']);
        });
    }
};
