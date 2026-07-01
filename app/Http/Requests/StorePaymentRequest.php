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
        $isSelf = $this->input('payment_mode') === 'self';

        $uniqueRule = null;
        if (!$isSelf) {
            if (in_array($this->type, ['rent', 'maintenance', 'electricity', 'water', 'gas'])) {
                $uniqueRule = Rule::unique('payments', 'month')
                    ->where('unit_id', $this->unit_id)
                    ->where('type', $this->type);
            }
        }

        return [
            'tenant_id'    => $isSelf ? ['nullable'] : ['required', 'exists:tenants,id'],
            'unit_id'      => $isSelf ? ['nullable'] : ['required', 'exists:units,id'],
            'agreement_id' => array_filter([
                $isSelf ? 'nullable' : 'required',
                'exists:agreements,id',
                (!$isSelf && $this->type === 'security_deposit')
                    ? Rule::unique('payments', 'agreement_id')->where('type', 'security_deposit')
                    : null
            ]),
            'type'         => $isSelf ? ['nullable'] : ['required', 'in:rent,maintenance,fine,other,security_deposit'],
            'month' => array_filter([
                'required',
                'date',
                $uniqueRule
            ]),
            'amount'   => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.unique' => 'A payment record for this unit, type, and month already exists.',
            'agreement_id.unique' => 'A security deposit payment for this agreement has already been created.',
        ];
    }
}