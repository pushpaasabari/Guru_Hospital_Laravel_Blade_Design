<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_staff', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('category', 60); // Primary category: consultant_doctor, surgeon, assisted_surgeon, anaesthetist, assisted_anaesthetist, staff_nurse, ot_technician
            $table->json('roles')->nullable(); // Array of roles this person can fulfill
            $table->string('qualification', 120)->nullable();
            $table->string('doctor_reg_no', 60)->nullable();
            $table->string('department', 120)->nullable();
            $table->string('phone', 50)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_staff');
    }
};
