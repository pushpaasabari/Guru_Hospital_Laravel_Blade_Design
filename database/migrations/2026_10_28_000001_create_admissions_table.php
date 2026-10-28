<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name', 160);
            $table->string('uhid', 60)->index();
            $table->string('ip_no', 60)->unique();
            $table->string('age', 30)->nullable();
            $table->string('sex', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('contact', 50)->nullable();
            $table->dateTime('admission_at');
            $table->dateTime('procedure_at')->nullable();
            $table->dateTime('discharge_at');
            $table->string('consultant', 160)->nullable();
            $table->string('department', 160)->nullable();
            $table->text('diagnosis');
            $table->enum('format', ['general','cmchistn','private','employee']);
            $table->enum('surgery_type', ['delivery','other']);
            $table->longText('clinical_history')->nullable();
            $table->longText('examination')->nullable();
            $table->json('investigations')->nullable();
            $table->longText('treatment_course')->nullable();
            $table->longText('discharge_condition')->nullable();
            $table->longText('medication_orders')->nullable();
            $table->longText('discharge_advice')->nullable();
            $table->longText('follow_up')->nullable();
            $table->string('procedure_name',300)->nullable();
            $table->string('surgeon',160)->nullable();
            $table->string('anaesthetist',160)->nullable();
            $table->string('assistant',160)->nullable();
            $table->string('anaesthesia_type',160)->nullable();
            $table->longText('procedure_note')->nullable();
            $table->longText('incision')->nullable();
            $table->longText('findings')->nullable();
            $table->longText('complications')->nullable();
            $table->decimal('blood_loss_ml',8,2)->nullable();
            $table->text('specimen')->nullable();
            $table->text('drain')->nullable();
            $table->longText('postoperative_orders')->nullable();
            $table->string('mrd_no',60)->nullable()->index();
            $table->string('payment_mode',100)->nullable();
            $table->string('handover_name',160)->nullable();
            $table->string('floor_incharge_name',160)->nullable();
            $table->string('mrd_incharge_name',160)->nullable();
            $table->enum('status',['draft','awaiting_review','approved','superseded'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
