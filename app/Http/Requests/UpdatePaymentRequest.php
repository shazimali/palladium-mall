<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('payments.edit')
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
        $payment = $this->route('payment');
        $isSelf = is_null($payment->tenant_id);

        $uniqueRule = null;
        if (!$isSelf) {
            if ($this->type === 'security_deposit') {
                $uniqueRule = Rule::unique('payments', 'agreement_id')
                    ->where('type', 'security_deposit')
                    ->ignore($payment->id);
            } elseif (in_array($this->type, ['rent', 'maintenance', 'electricity', 'water', 'gas'])) {
                $uniqueRule = Rule::unique('payments', 'month')
                    ->where('unit_id', $this->unit_id)
                    ->where('type', $this->type)
                    ->ignore($payment->id);
            }
        } elseif ($isSelf) {
            $uniqueRule = Rule::unique('payments', 'month')
                ->where('unit_id', $this->unit_id)
                ->where('type', 'maintenance')
                ->ignore($payment->id);
        }

        return [
            'tenant_id'    => $isSelf ? ['nullable'] : ['required', 'exists:tenants,id'],
            'unit_id'      => ['required', 'exists:units,id'],
            'agreement_id' => $isSelf ? ['nullable'] : ['required', 'exists:agreements,id'],
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