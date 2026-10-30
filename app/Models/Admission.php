<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admission extends Model
{
    protected $fillable = [
        'patient_name', 'uhid', 'ip_no', 'age', 'sex', 'address', 'contact',
        'admission_at', 'procedure_at', 'discharge_at', 'consultant',
        'department', 'diagnosis', 'format', 'surgery_type', 'clinical_history',
        'examination', 'investigations', 'treatment_course', 'discharge_condition',
        'medication_orders', 'discharge_advice', 'follow_up',
        'procedure_name', 'procedure_done', 'icd_code', 'surgeon', 'assisted_surgeon', 'pediatrician_name', 'anaesthetist', 'assisted_anaesthetist', 'assistant', 'assisted_by', 'staff_nurse', 'ot_technician', 'anaesthesia_type',
        'procedure_note', 'incision', 'findings', 'complications', 'blood_loss_ml',
        'specimen', 'drain', 'postoperative_orders', 'mrd_no', 'payment_mode',
        'handover_name', 'floor_incharge_name', 'mrd_incharge_name', 'status',
        'rejection_reason', 'reprint_reason',
        'created_by', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'admission_at' => 'datetime',
        'procedure_at' => 'datetime',
        'discharge_at' => 'datetime',
        'approved_at' => 'datetime',
        'birth_details' => 'array',
        'blood_loss_ml' => 'decimal:2',
    ];

    public function newborns(): HasMany
    {
        return $this->hasMany(Newborn::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DocumentRevision::class)->orderBy('revision_number', 'desc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDelivery(): bool
    {
        return $this->surgery_type === 'delivery';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isReadyForReview(): bool
    {
        return $this->status === 'ready_for_review';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned_for_correction';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isGenerated(): bool
    {
        return $this->status === 'generated';
    }

    public function isSuperseded(): bool
    {
        return $this->status === 'superseded';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'returned_for_correction'], true);
    }
}
