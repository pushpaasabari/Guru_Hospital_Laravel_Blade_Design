<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSurgeryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admission = $this->route('admission');
        return $this->user()?->can('update', $admission) ?? false;
    }

    public function rules(): array
    {
        return [
            'procedure_at' => ['nullable', 'date'],
            'procedure_name' => ['nullable', 'string', 'max:300'],
            'procedure_done' => ['nullable', 'string', 'max:300'],
            'icd_code' => ['nullable', 'string', 'max:60'],
            'surgeon' => ['nullable', 'string', 'max:160'],
            'assisted_surgeon' => ['nullable', 'string', 'max:160'],
            'pediatrician_name' => ['nullable', 'string', 'max:160'],
            'anaesthetist' => ['nullable', 'string', 'max:160'],
            'assisted_anaesthetist' => ['nullable', 'string', 'max:160'],
            'assistant' => ['nullable', 'string', 'max:160'],
            'assisted_by' => ['nullable', 'string', 'max:160'],
            'staff_nurse' => ['nullable', 'string', 'max:160'],
            'ot_technician' => ['nullable', 'string', 'max:160'],
            'anaesthesia_type' => ['nullable', 'string', 'max:160'],
            'procedure_note' => ['nullable', 'string', 'max:15000'],
            'incision' => ['nullable', 'string', 'max:5000'],
            'findings' => ['nullable', 'string', 'max:10000'],
            'complications' => ['nullable', 'string', 'max:5000'],
            'blood_loss_ml' => ['nullable', 'numeric', 'min:0'],
            'specimen' => ['nullable', 'string', 'max:2000'],
            'drain' => ['nullable', 'string', 'max:2000'],
            'postoperative_orders' => ['nullable', 'string', 'max:10000'],

            // Newborns for delivery workflow
            'newborns' => ['nullable', 'array'],
            'newborns.*.sex' => ['nullable', Rule::in(['Female', 'Male', 'Other', 'Not recorded'])],
            'newborns.*.birth_at' => ['nullable', 'date'],
            'newborns.*.birth_weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:8'],
            'newborns.*.apgar_1_min' => ['nullable', 'integer', 'min:0', 'max:10'],
            'newborns.*.apgar_5_min' => ['nullable', 'integer', 'min:0', 'max:10'],
            'newborns.*.neonatal_care' => ['nullable', 'string', 'max:3000'],
            'newborns.*.notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}

