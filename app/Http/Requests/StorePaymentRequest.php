<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('payments.create')
            || $this->user()->isSuperAdmin();
    }

    protected function prepareForValidation()
    {
        if ($this->has('month') && !empty($this->month)) {
            try {
                $this->merge([
                    'month' => \Carbon\Carbon::parse($this->month)->startOfMonth()->toDateString(),
                ]);
            } catch (\Exception $e) {
                // Ignore parsing errors, standard date validator will catch them
            }
        }
    }

    public function rules(): array
    {
        $uniqueRule = null;
        if (in_array($this->type, ['rent', 'maintenance', 'electricity', 'water', 'gas'])) {
            $uniqueRule = Rule::unique('payments', 'month')
                ->where('unit_id', $this->unit_id)
                ->where('type', $this->type);
        }

        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'agreement_id' => ['required', 'exists:agreements,id'],
            'type' => ['required', 'in:rent,maintenance,fine,other'],
            'month' => array_filter([
                'required',
                'date',
                $uniqueRule
            ]),
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.unique' => 'A payment record for this unit, type, and month already exists.',
        ];
    }
}