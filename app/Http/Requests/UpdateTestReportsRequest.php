<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admission = $this->route('admission');
        return $this->user()?->can('update', $admission) ?? false;
    }

    public function rules(): array
    {
        return [
            'investigations' => ['nullable', 'string', 'max:20000'],
            'vital_bp' => ['nullable', 'string', 'max:50'],
            'vital_pulse' => ['nullable', 'string', 'max:50'],
            'vital_spo2' => ['nullable', 'string', 'max:50'],
        ];
    }
}

