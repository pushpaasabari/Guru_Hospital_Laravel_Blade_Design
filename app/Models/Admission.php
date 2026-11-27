<?php

namespace App\Models;

use App\Support\AdmissionInputNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class Admission extends Model
{
    protected $fillable = [
        'patient_name', 'attender_name', 'attender_relation', 'uhid', 'ip_no', 'picme_no', 'vhn_name', 'phc', 'age', 'sex', 'address', 'address_line1', 'address_line2', 'address_line3', 'contact',
        'admission_at', 'procedure_at', 'discharge_at', 'consultant',
        'department', 'diagnosis', 'format', 'surgery_type', 'clinical_history',
        'examination', 'investigations', 'vital_bp', 'vital_pulse', 'vital_spo2', 'treatment_course', 'discharge_condition',
        'discharge_bp', 'discharge_pulse', 'discharge_spo2',
        'medication_orders', 'discharge_advice', 'post_surgery_diet', 'post_surgery_activity', 'follow_up', 'suture_removal_date',
        'procedure_name', 'procedure_done', 'icd_code', 'surgeon', 'assisted_surgeon', 'pediatrician_name', 'anaesthetist', 'assisted_anaesthetist', 'assistant', 'assisted_by', 'staff_nurse', 'ot_technician', 'anaesthesia_type',
        'procedure_note', 'incision', 'findings', 'complications', 'blood_loss_ml',
        'specimen', 'drain', 'postoperative_orders', 'stent_removal_date', 'mrd_no', 'payment_mode',
        'insurance_type', 'insurance_company', 'policy_number', 'pre_auth_no',
        'pre_auth_status', 'approved_amount', 'policy_holder_name',
        'policy_holder_relation', 'policy_validity',
        'handover_name', 'floor_incharge_name', 'mrd_incharge_name', 'status',
        'rejection_reason', 'reprint_reason',
        'created_by', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'admission_at' => 'datetime:Y-m-d\TH:i:s',
        'procedure_at' => 'datetime:Y-m-d\TH:i:s',
        'discharge_at' => 'datetime:Y-m-d\TH:i:s',
        'approved_at' => 'datetime',
        'policy_validity' => 'date:Y-m-d',
        'stent_removal_date' => 'date:Y-m-d',
        'suture_removal_date' => 'date:Y-m-d',
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

            // Default MRD File Number format is MRD-{IP No}
            if (empty($admission->mrd_no) && !empty($admission->ip_no)) {
                $admission->mrd_no = self::defaultMrdNo($admission->ip_no);
            }

            // Keep procedure_done and procedure_name synchronized if one is empty
            if (empty($admission->procedure_done) && !empty($admission->procedure_name)) {
                $admission->procedure_done = $admission->procedure_name;
            } elseif (empty($admission->procedure_name) && !empty($admission->procedure_done)) {
                $admission->procedure_name = $admission->procedure_done;
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
            'mrd_no',
            'age',
            'sex',
            'format',
            'surgery_type',
            'diagnosis',
            'procedure_name',
            'procedure_done',
            'status',
            'admission_at',
            'procedure_at',
            'discharge_at',
            'rejection_reason',
            'consultant',
            'department',
            'ward_room_no',
        ]);
    }

    /**
     * Apply search filtering across key patient identifiers and optionally clinical metadata.
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
                ->orWhere('ip_no', 'like', $like)
                ->orWhere('mrd_no', 'like', $like);

            if ($includeConsultant) {
                $q->orWhere('consultant', 'like', $like)
                    ->orWhere('department', 'like', $like)
                    ->orWhere('ward_room_no', 'like', $like)
                    ->orWhere('diagnosis', 'like', $like);
            }
        });
    }

    /**
     * Shared status counts + filtered directory pagination for Dashboard and Archive datatables.
     *
     * @return array{counts: array<string, int>, admissions: \Illuminate\Contracts\Pagination\LengthAwarePaginator, filters: array<string, mixed>}
     */
    public static function buildDirectoryListing(Request $request, int $defaultPerPage = 50): array
    {
        $statusCounts = static::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $inAdmissionCount = (int) static::query()
            ->whereNull('discharge_at')
            ->count();

        $counts = [
            'total' => (int) $statusCounts->sum(),
            'draft' => (int) ($statusCounts['draft'] ?? 0),
            'ready_for_review' => (int) ($statusCounts['ready_for_review'] ?? 0),
            'returned_for_correction' => (int) ($statusCounts['returned_for_correction'] ?? 0),
            'approved' => (int) ($statusCounts['approved'] ?? 0),
            'generated' => (int) ($statusCounts['generated'] ?? 0),
            'superseded' => (int) ($statusCounts['superseded'] ?? 0),
            'in_admission' => $inAdmissionCount,
        ];

        $query = static::query()
            ->summaryColumns()
            ->with('newborns:id,admission_id')
            ->filterSearch($request->input('search'), true)
            ->latest('id');

        if ($status = $request->input('status')) {
            if ($status === 'in_admission') {
                $query->whereNull('discharge_at');
            } elseif ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($format = $request->input('format')) {
            if ($format !== 'all') {
                $query->where('format', $format);
            }
        }

        if ($surgeryType = $request->input('surgery_type')) {
            if ($surgeryType !== 'all') {
                $query->where('surgery_type', $surgeryType);
            }
        }

        if ($consultant = $request->input('consultant')) {
            if ($consultant !== 'all') {
                $query->where('consultant', $consultant);
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('admission_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('admission_at', '<=', $dateTo);
        }

        $perPage = (int) $request->input('per_page', $defaultPerPage);
        if ($perPage < 5 || $perPage > 250) {
            $perPage = $defaultPerPage;
        }

        $admissions = $query->paginate($perPage)->withQueryString();
        $filters = $request->only([
            'search',
            'status',
            'format',
            'surgery_type',
            'consultant',
            'date_from',
            'date_to',
        ]);
        $filters['per_page'] = $perPage;

        return compact('counts', 'admissions', 'filters');
    }

    /**
     * Eager-load document preview/history relations without pulling heavy revision JSON snapshots.
     */
    public function loadDocumentRelations(bool $withNewborns = true): self
    {
        $relations = [
            'approvals.performer:id,name,role',
            'revisions' => fn ($q) => $q->select([
                'id', 'admission_id', 'revision_number', 'approved_by', 'approved_at', 'created_at', 'pdf_storage_path',
            ]),
            'revisions.approver:id,name,role',
            'creator:id,name,role',
            'approver:id,name,role,doctor_reg_no',
        ];

        if ($withNewborns) {
            array_unshift($relations, 'newborns');
        }

        return $this->load($relations);
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

    /**
     * Determine whether an obstetric delivery case is a Natural / Normal Vaginal Delivery
     * rather than a surgical Caesarean section (LSCS) or operative theatre procedure.
     */
    public function isNaturalDelivery(): bool
    {
        if (!$this->isDelivery()) {
            return false;
        }

        $procText = trim(($this->procedure_done ?? '') . ' ' . ($this->procedure_name ?? ''));
        if ($procText === '') {
            return false;
        }

        if (preg_match('/\b(lscs|caesarean|cesarean|c-section|hysterectomy|laparotomy|laparoscop|myomectomy|salpingectomy|oophorectomy|cerclage)\b/i', $procText)) {
            return false;
        }

        return (bool) preg_match('/\b(natural|normal|ftnd|vaginal|nvd|svd|spontaneous|breech|forceps|vacuum|ventouse|labour\s*natural|labor\s*natural)\b/i', $procText);
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
        return \App\Services\DischargeSummaryFormatter::formatInvestigations($this->investigations, $this);
    }

    public function getFormattedConditionAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatConditionOnDischarge($this->discharge_condition, $this);
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

    public function getFormattedPostoperativeOrdersAttribute(): array
    {
        return \App\Services\DischargeSummaryFormatter::formatPostoperativeOrders($this->postoperative_orders);
    }

    /**
     * Resolves official doctor rubber stamp pad impression details for discharge summary footer.
     */
    public function getDoctorStampDetailsAttribute(): ?array
    {
        $rawDoctor = trim((string) ($this->consultant ?: $this->surgeon));
        $doctor = trim((string) (preg_split('/[;\r\n|]+/', $rawDoctor)[0] ?? ''));
        if ($doctor === '') {
            return null;
        }

        // Clean honorifics for staff directory lookup
        $clean = preg_replace('/^(?:Dr\.?|Prof\.?|Staff\s+Nurse|Nurse|Sister|Miss|Mr\.?|Mrs\.?)\s+/i', '', $doctor);
        $clean = trim($clean);

        // Attempt lookup from active ClinicalStaff
        $staff = ClinicalStaff::active()
            ->where(function ($q) use ($doctor, $clean) {
                $q->where('name', 'like', "%{$clean}%")
                    ->orWhere('name', 'like', "%{$doctor}%");
            })
            ->first();

        if (!$staff && $clean !== '') {
            $words = array_values(array_filter(preg_split('/[\s\.\-]+/', $clean), fn ($w) => strlen($w) >= 3));
            foreach ($words as $word) {
                $staff = ClinicalStaff::active()
                    ->where('name', 'like', "%{$word}%")
                    ->first();
                if ($staff) {
                    break;
                }
            }
        }

        // Stamp impression for Dr. Ambika matching physical seal
        if (preg_match('/Ambika/i', $doctor)) {
            $regNo = $staff?->doctor_reg_no ?: '80822';
            $regDisplay = str_starts_with($regNo, 'Reg') ? $regNo : 'Reg.No:' . $regNo;
            return [
                'name' => 'DR.K.AMBIKA, MS.,(O&G)',
                'reg_no' => $regDisplay,
                'line1' => 'Consultant Gynaecologist',
                'line2' => 'Infertility Specialist',
                'location' => 'Dharmapuri-636701',
            ];
        }

        // Stamp impression for Dr. Jayaprakash matching hospital seal
        if (preg_match('/Jayaprakash/i', $doctor)) {
            $regNo = $staff?->doctor_reg_no ?: '74950';
            $regDisplay = str_starts_with($regNo, 'Reg') ? $regNo : 'Reg.No:' . $regNo;
            return [
                'name' => 'DR.N.JAYAPRAKASH, MS.,MCh (URO)',
                'reg_no' => $regDisplay,
                'line1' => 'Consultant Urologist & Surgeon',
                'line2' => 'Guru Hospital',
                'location' => 'Dharmapuri-636701',
            ];
        }

        // Generic fallback for any other doctor / medical staff
        $docName = $doctor;
        if (!preg_match('/^Dr\.?\s+/i', $docName)) {
            $docName = 'DR.' . $docName;
        }
        $docName = strtoupper($docName);
        if ($staff && !empty($staff->qualification) && !str_contains(strtolower($docName), strtolower($staff->qualification))) {
            $docName .= ', ' . $staff->qualification;
        }

        $regNo = $staff?->doctor_reg_no ? 'Reg.No:' . $staff->doctor_reg_no : 'Reg.No: —';
        $desig = $staff?->category ? (ClinicalStaff::ROLES[$staff->category] ?? 'Consultant Doctor') : 'Consultant Doctor';
        $dept = $staff?->department ?: ($this->department ?: 'Guru Hospital');

        return [
            'name' => $docName,
            'reg_no' => $regNo,
            'line1' => $desig,
            'line2' => $dept !== $desig ? $dept : null,
            'location' => 'Dharmapuri-636701',
        ];
    }

    /**
     * Resolves consultant / treating doctor name accompanied by their academic qualification / degrees.
     */
    public function getConsultantWithDegreeAttribute(): string
    {
        $rawConsultant = trim((string) ($this->consultant ?: $this->surgeon));
        $consultant = trim((string) (preg_split('/[;\r\n|]+/', $rawConsultant)[0] ?? ''));
        if ($consultant === '') {
            return '—';
        }

        // Return immediately if doctor string already contains common degree abbreviations
        if (preg_match('/(,\s*|\s*\()?(MBBS|M\.B\.B\.S|MS|M\.S|MD|M\.D|MCh|M\.Ch|DGO|D\.G\.O|DNB|D\.N\.B|DA|D\.A|MRCOG|FRCS|DM|D\.M|FCPS|DCH|B\.Sc|GNM)/i', $consultant)) {
            return $consultant;
        }

        // Strip honorifics to search ClinicalStaff directory
        $clean = preg_replace('/^(?:Dr\.?|Prof\.?|Staff\s+Nurse|Nurse|Sister|Miss|Mr\.?|Mrs\.?)\s+/i', '', $consultant);
        $clean = trim($clean);

        // First attempt: exact or partial name match in active clinical staff
        $staff = ClinicalStaff::active()
            ->where(function ($q) use ($consultant, $clean) {
                $q->where('name', 'like', "%{$clean}%")
                    ->orWhere('name', 'like', "%{$consultant}%");
            })
            ->whereNotNull('qualification')
            ->where('qualification', '!=', '')
            ->first();

        // Second attempt: match by first or last word of name (e.g. "Jayaprakash", "Ambika")
        if (!$staff && $clean !== '') {
            $words = array_values(array_filter(preg_split('/[\s\.\-]+/', $clean), fn ($w) => strlen($w) >= 3));
            foreach ($words as $word) {
                $staff = ClinicalStaff::active()
                    ->where('name', 'like', "%{$word}%")
                    ->whereNotNull('qualification')
                    ->where('qualification', '!=', '')
                    ->first();
                if ($staff) {
                    break;
                }
            }
        }

        if ($staff && !empty($staff->qualification)) {
            if (!str_contains(strtolower($consultant), strtolower($staff->qualification))) {
                return $consultant . ', ' . $staff->qualification;
            }
        }

        // Well-known hospital defaults fallback if clinical staff table was empty/unseeded
        if (preg_match('/Jayaprakash/i', $consultant)) {
            return $consultant . ', M.S (Gen Surg), M.Ch (Uro)';
        }
        if (preg_match('/Ambika/i', $consultant)) {
            return $consultant . ', M.S (O&G)';
        }

        return $consultant;
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
            $items[] = ['label' => 'Valid Till', 'value' => $this->policy_validity->format('d.m.Y')];
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

    /**
     * Build the default MRD number from an IP No (`MRD-{IP No}`).
     */
    public static function defaultMrdNo(?string $ipNo): string
    {
        $trimmed = trim((string) $ipNo);
        if ($trimmed === '') {
            return '';
        }

        return str_starts_with($trimmed, 'MRD-') ? $trimmed : 'MRD-' . $trimmed;
    }

    /**
     * Resolve the explicit MRD number or fall back to the `MRD-{IP No}` format.
     */
    public function getResolvedMrdNoAttribute(): string
    {
        $explicit = trim((string) $this->mrd_no);
        if ($explicit !== '') {
            return $explicit;
        }

        return self::defaultMrdNo($this->ip_no);
    }
}
