<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasInsuranceValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDischargeRequest extends FormRequest
{
    use HasInsuranceValidationRules;

    public function authorize(): bool
    {
        $admission = $this->route('admission');
        return $this->user()?->can('update', $admission) ?? false;
    }

    public function rules(): array
    {
        return [
            'discharge_at' => ['nullable', 'date', 'after_or_equal:admission_at'],
            'discharge_condition' => ['nullable', 'string', 'max:5000'],
            'discharge_bp' => ['nullable', 'string', 'max:50'],
            'discharge_pulse' => ['nullable', 'string', 'max:50'],
            'discharge_spo2' => ['nullable', 'string', 'max:50'],
            'clinical_history' => ['nullable', 'string', 'max:10000'],
            'examination' => ['nullable', 'string', 'max:10000'],
            'treatment_course' => ['nullable', 'string', 'max:10000'],
            'medication_orders' => ['nullable', 'string', 'max:10000'],
            'discharge_advice' => ['nullable', 'string', 'max:10000'],
            'post_surgery_diet' => ['nullable', 'string', 'max:255'],
            'post_surgery_activity' => ['nullable', 'string', 'max:255'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
            'suture_removal_date' => ['nullable', 'date'],
            'mrd_no' => ['nullable', 'string', 'max:60'],
            ...$this->insuranceRules(),
            'handover_name' => ['nullable', 'string', 'max:160'],
            'floor_incharge_name' => ['nullable', 'string', 'max:160'],
            'mrd_incharge_name' => ['nullable', 'string', 'max:160'],
        ];
    }
}

