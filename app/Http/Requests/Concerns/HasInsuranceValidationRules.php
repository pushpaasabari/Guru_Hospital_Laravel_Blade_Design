<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait HasInsuranceValidationRules
{
    protected function insuranceRules(): array
    {
        return [
            'payment_mode' => ['nullable', Rule::in(['Cash', 'Insurance'])],
            'insurance_type' => ['nullable', 'required_if:payment_mode,Insurance', Rule::in(['CMCHISTN Govt Scheme', 'Private Insurance / TPA', 'Corporate / Employee'])],
            'insurance_company' => ['nullable', 'required_if:payment_mode,Insurance', 'string', 'max:160'],
            'policy_number' => ['nullable', 'required_if:payment_mode,Insurance', 'string', 'max:100'],
            'pre_auth_no' => ['nullable', 'string', 'max:100'],
            'pre_auth_status' => ['nullable', Rule::in(['Pending', 'Approved', 'Enhancement Requested', 'Rejected'])],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
            'policy_holder_name' => ['nullable', 'string', 'max:160'],
            'policy_holder_relation' => ['nullable', 'string', 'max:60'],
            'policy_validity' => ['nullable', 'date'],
        ];
    }

    protected function insuranceMessages(): array
    {
        return [
            'insurance_type.required_if' => 'Insurance Category / Scheme is required when Payment Mode is Insurance.',
            'insurance_company.required_if' => 'Insurance Company / TPA Name is required when Payment Mode is Insurance.',
            'policy_number.required_if' => 'Policy / Health Card / URN Number is required when Payment Mode is Insurance.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $insuranceFields = [
            'insurance_type',
            'insurance_company',
            'policy_number',
            'pre_auth_no',
            'pre_auth_status',
            'approved_amount',
            'policy_holder_name',
            'policy_holder_relation',
            'policy_validity',
        ];

        $updates = [];

        if ($this->has('payment_mode')) {
            $mode = trim((string) $this->input('payment_mode'));
            if ($mode === '') {
                $updates['payment_mode'] = null;
            } elseif (strcasecmp($mode, 'Cash') === 0) {
                $updates['payment_mode'] = 'Cash';
                foreach ($insuranceFields as $field) {
                    $updates[$field] = null;
                }
            } elseif (strcasecmp($mode, 'Insurance') === 0) {
                $updates['payment_mode'] = 'Insurance';
            }
        }

        foreach (['insurance_type', 'pre_auth_status', 'approved_amount', 'policy_validity'] as $nullableField) {
            if (!array_key_exists($nullableField, $updates) && $this->has($nullableField) && $this->input($nullableField) === '') {
                $updates[$nullableField] = null;
            }
        }

        if (!empty($updates)) {
            $this->merge($updates);
        }

        if (method_exists($this, 'normalizeUppercaseClinicalInputs')) {
            $this->normalizeUppercaseClinicalInputs();
        }
    }

    public function messages(): array
    {
        $clinicalMessages = method_exists($this, 'clinicalValidationMessages')
            ? $this->clinicalValidationMessages()
            : [];

        return array_merge($this->insuranceMessages(), $clinicalMessages);
    }
}

