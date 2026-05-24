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

    public function rules(): array
    {
        $payment = $this->route('payment');

        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'agreement_id' => ['required', 'exists:agreements,id'],
            'type' => ['required', 'in:rent,maintenance,fine,other'],
            'month' => [
                'required',
                'date',
                Rule::unique('payments', 'month')
                    ->where('tenant_id', $this->tenant_id)
                    ->where('type', $this->type)
                    ->ignore($payment->id)
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}