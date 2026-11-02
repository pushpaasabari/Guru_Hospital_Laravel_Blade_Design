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
        ];
    }
}
