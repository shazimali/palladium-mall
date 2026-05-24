<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUtilityReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('utilities.edit')
            || $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        $reading = $this->route('utility');

        return [
            'unit_id' => ['required', 'exists:units,id'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'type' => ['required', 'in:electricity,water,gas'],
            'month' => [
                'required',
                'date',
                Rule::unique('utility_readings', 'month')
                    ->where('unit_id', $this->unit_id)
                    ->where('type', $this->type)
                    ->ignore($reading->id)
            ],
            'previous_reading' => ['required', 'numeric', 'min:0'],
            'current_reading' => [
                'required',
                'numeric',
                'min:' . $this->input('previous_reading', 0)
            ],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_reading.min' => 'Current reading must be greater than or equal to previous reading.',
            'month.unique' => 'A reading for this unit, type and month already exists.',
        ];
    }
}