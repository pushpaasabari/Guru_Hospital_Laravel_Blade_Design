<?php

namespace App\Http\Requests\Concerns;

use App\Support\AdmissionInputNormalizer;

trait HasClinicalInputNormalization
{
    /**
     * Convert Patient Full Name, Address lines, Consultant Doctor, and Surgical & Anaesthesia Team fields to uppercase.
     */
    protected function normalizeUppercaseClinicalInputs(): void
    {
        $updates = [];

        foreach (AdmissionInputNormalizer::UPPERCASE_FIELDS as $field) {
            if (!$this->has($field)) {
                continue;
            }

            $raw = $this->input($field);
            if (is_array($raw)) {
                $updates[$field] = AdmissionInputNormalizer::joinMulti($raw, true);
            } elseif (is_string($raw)) {
                $updates[$field] = AdmissionInputNormalizer::toUpper($raw);
            }
        }

        if ($this->has('anaesthesia_type')) {
            $rawAnes = $this->input('anaesthesia_type');
            if (is_array($rawAnes)) {
                $updates['anaesthesia_type'] = AdmissionInputNormalizer::joinMulti($rawAnes, false);
            }
        }

        if ($updates !== []) {
            $this->merge($updates);
        }
    }

    /**
     * Validation rules for multiple-choice Surgical & Anaesthesia Team fields.
     *
     * @return array<string, array<int, string>>
     */
    protected function surgicalTeamRules(): array
    {
        return [
            'surgeon' => ['nullable', 'string', 'max:2000'],
            'assisted_surgeon' => ['nullable', 'string', 'max:2000'],
            'pediatrician_name' => ['nullable', 'string', 'max:2000'],
            'anaesthetist' => ['nullable', 'string', 'max:2000'],
            'assisted_anaesthetist' => ['nullable', 'string', 'max:2000'],
            'assistant' => ['nullable', 'string', 'max:2000'],
            'assisted_by' => ['nullable', 'string', 'max:2000'],
            'staff_nurse' => ['nullable', 'string', 'max:2000'],
            'ot_technician' => ['nullable', 'string', 'max:2000'],
            'anaesthesia_type' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Shared numeric-only validation rules for Phone / Contact.
     *
     * @return array<int, string>
     */
    protected function contactValidationRules(): array
    {
        return ['nullable', 'string', 'regex:/^[0-9]+$/', 'max:50'];
    }

    /**
     * Shared validation messages for normalized clinical fields.
     *
     * @return array<string, string>
     */
    protected function clinicalValidationMessages(): array
    {
        return [
            'contact.regex' => 'Phone / Contact may contain only numbers (0-9).',
        ];
    }
}

