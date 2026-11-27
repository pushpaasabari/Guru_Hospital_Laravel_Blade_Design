<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_dropdown_options', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->string('group_label', 120)->nullable();
            $table->string('label', 300);
            $table->string('normalized_key', 300);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'normalized_key'], 'clinical_dropdown_type_key_unique');
            $table->index(['type', 'is_active', 'sort_order'], 'clinical_dropdown_type_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_dropdown_options');
    }
};
