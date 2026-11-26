<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('newborns', function (Blueprint $table) {
            $table->decimal('discharge_weight_kg', 4, 2)->nullable()->after('birth_weight_kg');
            $table->json('birth_vaccines')->nullable()->after('notes');
            $table->date('vaccine_date')->nullable()->after('birth_vaccines');
        });
    }

    public function down(): void
    {
        Schema::table('newborns', function (Blueprint $table) {
            $table->dropColumn(['discharge_weight_kg', 'birth_vaccines', 'vaccine_date']);
        });
    }
};
