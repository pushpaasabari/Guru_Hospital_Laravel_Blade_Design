<?php

namespace App\Models;

use App\Support\AdmissionInputNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admission extends Model
{
    protected $fillable = [
        'patient_name', 'uhid', 'ip_no', 'picme_no', 'age', 'sex', 'address', 'address_line1', 'address_line2', 'address_line3', 'contact',
        'admission_at', 'procedure_at', 'discharge_at', 'consultant',
        'department', 'diagnosis', 'format', 'surgery_type', 'clinical_history',
        'examination', 'investigations', 'treatment_course', 'discharge_condition',
        'medication_orders', 'discharge_advice', 'follow_up',
        'procedure_name', 'procedure_done', 'icd_code', 'surgeon', 'assisted_surgeon', 'pediatrician_name', 'anaesthetist', 'assisted_anaesthetist', 'assistant', 'assisted_by', 'staff_nurse', 'ot_technician', 'anaesthesia_type',
        'procedure_note', 'incision', 'findings', 'complications', 'blood_loss_ml',
        'specimen', 'drain', 'postoperative_orders', 'mrd_no', 'payment_mode',
        'insurance_type', 'insurance_company', 'policy_number', 'pre_auth_no',
        'pre_auth_status', 'approved_amount', 'policy_holder_name',
        'policy_holder_relation', 'policy_validity',
        'handover_name', 'floor_incharge_name', 'mrd_incharge_name', 'status',
        'rejection_reason', 'reprint_reason',
        'created_by', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'admission_at' => 'datetime',
        'procedure_at' => 'datetime',
        'discharge_at' => 'datetime',
        'approved_at' => 'datetime',
        'policy_validity' => 'date:Y-m-d',
        'birth_details' => 'array',
        'blood_loss_ml' => 'decimal:2',
        'approved_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function ($admission) {
            foreach (AdmissionInputNormalizer::UPPERCASE_FIELDS as $field) {
                if ($admission->$field !== null) {
                    $admission->$field = AdmissionInputNormalizer::toUpper((string) $admission->$field);
                }
            }

            if ($admission->contact !== null) {
                $admission->contact = AdmissionInputNormalizer::digitsOnly((string) $admission->contact);
            }

            if (!empty($admission->address_line1) || !empty($admission->address_line2) || !empty($admission->address_line3)) {
                $admission->address = implode(', ', array_filter([
                    $admission->address_line1,
                    $admission->address_line2,
                    $admission->address_line3,
                ]));
            } elseif (!empty($admission->address) && empty($admission->address_line1)) {
                $admission->address_line1 = $admission->address;
            }

            // Temporarily MRD File Number will be (MRD-{UHID})
            if (empty($admission->mrd_no) && !empty($admission->uhid)) {
                $admission->mrd_no = str_starts_with((string) $admission->uhid, 'MRD-')
                    ? $admission->uhid
                    : 'MRD-' . $admission->uhid;
            }
        });
    }

    /**
     * Restrict queries to lightweight columns needed for list tables and dashboards.
     */
    public function scopeSummaryColumns(Builder $query): Builder
    {
        return $query->select([
            'id',
            'patient_name',
            'uhid',
            'ip_no',
            'age',
            'sex',
            'format',
            'surgery_type',
            'status',
            'admission_at',
            'discharge_at',
            'rejection_reason',
        ]);
    }

    /**
     * Apply search filtering across key patient identifiers and optionally consultant.
     */
    public function scopeFilterSearch(Builder $query, ?string $search, bool $includeConsultant = false): Builder
    {
        $term = trim((string) $search);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term, $includeConsultant) {
            $like = "%{$term}%";
            $q->where('patient_name', 'like', $like)
                ->orWhere('uhid', 'like', $like)
                ->orWhere('ip_no', 'like', $like);

            if ($includeConsultant) {
                $q->orWhere('consultant', 'like', $like);
            }
        });
    }

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

    public function isMedical(): bool
    {
        return $this->surgery_type === 'medical';
    }

    public function isSurgical(): bool
    {
        return $this->surgery_type === 'other';
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

    public function getFormattedTreatmentCourseAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatTreatmentCourse($this->treatment_course);
    }

    public function getFormattedInvestigationsAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatInvestigations($this->investigations);
    }

    public function getFormattedConditionAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatConditionOnDischarge($this->discharge_condition);
    }

    public function getFormattedMedicationsAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatMedications($this->medication_orders);
    }

    public function getFormattedAdviceAndReviewAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatAdviceAndReview($this->discharge_advice, $this->follow_up, $this->discharge_at, $this);
    }

    public function getHasStentAttribute(): bool
    {
        return \App\Services\DischargeSummaryFormatter::hasStent($this);
    }

    public function getStentRemovalNoticeAttribute(): ?string
    {
        $review = $this->formatted_advice_and_review;
        return $review['tamil_note'] ?? ($this->has_stent ? \App\Services\DischargeSummaryFormatter::getStentRemovalNotice($review['review_date'] ?? null) : null);
    }

    public function getFormattedNewbornsAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatNewborns($this);
    }

    public function getFormattedSurgicalPastHistoryAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatSurgicalPastHistory($this);
    }

    public function getFormattedSurgicalInvestigationsAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatSurgicalInvestigations($this);
    }

    public function getFormattedSurgicalAdviceAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatSurgicalAdvice($this);
    }

    public function isInsurance(): bool
    {
        return strtolower(trim((string) $this->payment_mode)) === 'insurance';
    }

    /**
     * Normalized key-value pairs for rendering insurance metadata in summaries & PDFs.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public function getInsuranceDetailsListAttribute(): array
    {
        if (!$this->isInsurance() && empty($this->insurance_company) && empty($this->policy_number)) {
            return [];
        }

        $items = [];
        if ($this->insurance_type) {
            $items[] = ['label' => 'Scheme / Category', 'value' => $this->insurance_type];
        }
        if ($this->insurance_company) {
            $items[] = ['label' => 'Insurer / TPA', 'value' => $this->insurance_company];
        }
        if ($this->policy_number) {
            $items[] = ['label' => 'Policy / URN No.', 'value' => $this->policy_number];
        }
        if ($this->pre_auth_no) {
            $items[] = ['label' => 'Pre-Auth / Claim No.', 'value' => $this->pre_auth_no];
        }
        if ($this->pre_auth_status) {
            $items[] = ['label' => 'Pre-Auth Status', 'value' => $this->pre_auth_status];
        }
        if ($this->approved_amount !== null && $this->approved_amount !== '') {
            $items[] = ['label' => 'Approved Amount', 'value' => 'Rs. ' . number_format((float) $this->approved_amount, 2)];
        }
        if ($this->policy_holder_name) {
            $holder = $this->policy_holder_name . ($this->policy_holder_relation ? " ({$this->policy_holder_relation})" : '');
            $items[] = ['label' => 'Policy Holder', 'value' => $holder];
        }
        if ($this->policy_validity) {
            $items[] = ['label' => 'Valid Till', 'value' => $this->policy_validity->format('d/m/Y')];
        }

        return $items;
    }

    public function getPaymentSummaryAttribute(): string
    {
        if (!$this->payment_mode && empty($this->insurance_company) && empty($this->policy_number)) {
            return '—';
        }

        if (!$this->isInsurance() && empty($this->insurance_company) && empty($this->policy_number)) {
            return $this->payment_mode ?: '—';
        }

        $parts = array_filter([
            $this->insurance_type ?: null,
            $this->insurance_company ?: null,
            $this->policy_number ? "Policy/URN: {$this->policy_number}" : null,
            $this->pre_auth_no ? "Pre-Auth: {$this->pre_auth_no}" : null,
            $this->pre_auth_status ? "Status: {$this->pre_auth_status}" : null,
        ]);

        return !empty($parts) ? 'Insurance — ' . implode(' | ', $parts) : 'Insurance';
    }

    public function getFormattedSurgicalDrugsAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatSurgicalDrugs($this->medication_orders);
    }

    public function getFormattedClinicalSectionsAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatClinicalSections($this);
    }
}
