<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admission extends Model
{
    protected $fillable = [
        'patient_name', 'uhid', 'ip_no', 'age', 'sex', 'address', 'contact',
        'admission_at', 'procedure_at', 'discharge_at', 'consultant',
        'department', 'diagnosis', 'format', 'surgery_type', 'clinical_history',
        'examination', 'investigations', 'treatment_course', 'discharge_condition',
        'medication_orders', 'discharge_advice', 'follow_up',
        'procedure_name', 'surgeon', 'anaesthetist', 'assistant', 'anaesthesia_type',
        'procedure_note', 'incision', 'findings', 'complications', 'blood_loss_ml',
        'specimen', 'drain', 'postoperative_orders', 'mrd_no', 'payment_mode',
        'handover_name', 'floor_incharge_name', 'mrd_incharge_name', 'status',
        'created_by', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'admission_at' => 'datetime',
        'procedure_at' => 'datetime',
        'discharge_at' => 'datetime',
        'approved_at' => 'datetime',
        'investigations' => 'array',
        'birth_details' => 'array',
    ];

    public function newborns(): HasMany
    {
        return $this->hasMany(Newborn::class);
    }

    public function isDelivery(): bool
    {
        return $this->surgery_type === 'delivery';
    }
}
