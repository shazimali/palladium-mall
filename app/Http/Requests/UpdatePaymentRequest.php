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

        $isExtra = $payment->type === 'extra_payment';

        $uniqueRule = null;
        if (!$isSelf && !$isExtra) {
            if (in_array($this->type, ['rent', 'maintenance', 'electricity', 'water', 'gas'])) {
                $uniqueRule = Rule::unique('payments', 'month')
                    ->where('unit_id', $this->unit_id)
                    ->where('type', $this->type)
                    ->ignore($payment->id);
            }
        } elseif ($isSelf && !$isExtra) {
            $uniqueRule = Rule::unique('payments', 'month')
                ->where('unit_id', $this->unit_id)
                ->where('type', 'maintenance')
                ->ignore($payment->id);
        }

        return [
            'tenant_id'    => ($isSelf || $isExtra) ? ['nullable'] : ['required', 'exists:tenants,id'],
            'unit_id'      => ['required', 'exists:units,id'],
            'agreement_id' => array_filter([
                ($isSelf || $isExtra) ? 'nullable' : 'required',
                'exists:agreements,id',
                (!$isSelf && !$isExtra && $this->type === 'security_deposit')
                    ? Rule::unique('payments', 'agreement_id')->where('type', 'security_deposit')->ignore($payment->id)
                    : null
            ]),
            'type'         => ($isSelf || $isExtra) ? ['nullable'] : ['required', 'in:rent,maintenance,fine,other,security_deposit,extra_payment'],
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