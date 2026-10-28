<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('newborns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained()->cascadeOnDelete();
            $table->string('sex',30)->nullable();
            $table->dateTime('birth_at')->nullable();
            $table->decimal('birth_weight_kg',4,2)->nullable();
            $table->unsignedTinyInteger('apgar_1_min')->nullable();
            $table->unsignedTinyInteger('apgar_5_min')->nullable();
            $table->text('neonatal_care')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newborns');
    }
};
