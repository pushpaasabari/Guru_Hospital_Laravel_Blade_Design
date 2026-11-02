<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDischargeRequest extends FormRequest
{
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
            'clinical_history' => ['nullable', 'string', 'max:10000'],
            'examination' => ['nullable', 'string', 'max:10000'],
            'treatment_course' => ['nullable', 'string', 'max:10000'],
            'medication_orders' => ['nullable', 'string', 'max:10000'],
            'discharge_advice' => ['nullable', 'string', 'max:10000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
            'mrd_no' => ['nullable', 'string', 'max:60'],
            'payment_mode' => ['nullable', 'string', 'max:100'],
            'handover_name' => ['nullable', 'string', 'max:160'],
            'floor_incharge_name' => ['nullable', 'string', 'max:160'],
            'mrd_incharge_name' => ['nullable', 'string', 'max:160'],
        ];
    }
}

