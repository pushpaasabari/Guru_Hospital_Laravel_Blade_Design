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
            if ($this->has($field) && is_string($this->input($field))) {
                $updates[$field] = AdmissionInputNormalizer::toUpper($this->input($field));
            }
        }

        if ($updates !== []) {
            $this->merge($updates);
        }
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

