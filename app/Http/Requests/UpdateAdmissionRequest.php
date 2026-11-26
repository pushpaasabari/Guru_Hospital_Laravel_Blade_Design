<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasClinicalInputNormalization;
use App\Http\Requests\Concerns\HasInsuranceValidationRules;
use App\Http\Requests\Concerns\HasNewbornValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdmissionRequest extends FormRequest
{
    use HasClinicalInputNormalization;
    use HasInsuranceValidationRules;
    use HasNewbornValidationRules;
    public function authorize(): bool
    {
        $admission = $this->route('admission');
        return $this->user()?->can('update', $admission) ?? false;
    }

    public function rules(): array
    {
        $admissionId = $this->route('admission')?->id;

        return [
            // Patient Demographics
            'patient_name' => ['required', 'string', 'max:160'],
            'attender_name' => ['nullable', 'string', 'max:160'],
            'attender_relation' => ['nullable', 'string', 'max:60'],
            'uhid' => ['required', 'string', 'max:60'],
            'ip_no' => ['required', 'string', 'max:60', Rule::unique('admissions', 'ip_no')->ignore($admissionId)],
            'picme_no' => ['nullable', 'string', 'max:30'],
            'vhn_name' => ['nullable', 'string', 'max:160'],
            'phc' => ['nullable', 'string', 'max:160'],
            'age' => ['nullable', 'string', 'max:30'],
            'sex' => ['nullable', Rule::in(['Female', 'Male', 'Other', 'Not recorded'])],
            'address' => ['nullable', 'string', 'max:1000'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'address_line3' => ['nullable', 'string', 'max:255'],
            'contact' => $this->contactValidationRules(),

            // Core Dates & Doctors
            'admission_at' => ['required', 'date'],
            'procedure_at' => ['nullable', 'date'],
            'discharge_at' => ['nullable', 'date', 'after_or_equal:admission_at'],
            'consultant' => ['nullable', 'string', 'max:160'],
            'department' => ['nullable', 'string', 'max:160'],
            'ward_room_no' => ['nullable', 'string', 'max:50'],

            // Workflow Configurations
            'format' => ['required', Rule::in(['general', 'cmchistn', 'private', 'employee'])],
            'surgery_type' => ['required', Rule::in(['delivery', 'other', 'medical'])],
            'diagnosis' => ['required', 'string', 'max:3000'],

            // Clinical Summary
            'clinical_history' => ['nullable', 'string', 'max:10000'],
            'examination' => ['nullable', 'string', 'max:10000'],
            'investigations' => ['nullable'],
            'treatment_course' => ['nullable', 'string', 'max:10000'],
            'discharge_condition' => ['nullable', 'string', 'max:5000'],
            'medication_orders' => ['nullable', 'string', 'max:10000'],
            'discharge_advice' => ['nullable', 'string', 'max:10000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],

            // Operation Theatre Fields
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

            // MRD Administrative Fields
            'mrd_no' => ['nullable', 'string', 'max:60'],
            ...$this->insuranceRules(),
            'handover_name' => ['nullable', 'string', 'max:160'],
            'floor_incharge_name' => ['nullable', 'string', 'max:160'],
            'mrd_incharge_name' => ['nullable', 'string', 'max:160'],

            // Dynamic Newborns
            ...$this->newbornRules(),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $surgeryType = $this->input('surgery_type');
            $sex = $this->input('sex');

            if ($surgeryType === 'delivery' && strtolower((string) $sex) !== 'female') {
                $validator->errors()->add(
                    'surgery_type',
                    'Delivery / Obstetric workflow is only enabled for female patients.'
                );
            }
        });
    }
}

