<?php

namespace App\Http\Requests\Concerns;

use App\Models\Newborn;
use Illuminate\Validation\Rule;

trait HasNewbornValidationRules
{
    /**
     * Shared validation rules for delivery newborn records.
     *
     * @return array<string, mixed>
     */
    protected function newbornRules(): array
    {
        return [
            'newborns' => ['nullable', 'array'],
            'newborns.*.sex' => ['nullable', Rule::in(['Female', 'Male', 'Other', 'Not recorded'])],
            'newborns.*.birth_at' => ['nullable', 'date'],
            'newborns.*.birth_weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:8'],
            'newborns.*.discharge_weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:8'],
            'newborns.*.apgar_1_min' => ['nullable', 'integer', 'min:0', 'max:10'],
            'newborns.*.apgar_5_min' => ['nullable', 'integer', 'min:0', 'max:10'],
            'newborns.*.neonatal_care' => ['nullable', 'string', 'max:3000'],
            'newborns.*.notes' => ['nullable', 'string', 'max:3000'],
            'newborns.*.birth_vaccines' => ['nullable', 'array'],
            'newborns.*.birth_vaccines.*' => ['string', Rule::in(Newborn::BIRTH_VACCINES)],
            'newborns.*.vaccine_date' => ['nullable', 'date'],
        ];
    }
}
