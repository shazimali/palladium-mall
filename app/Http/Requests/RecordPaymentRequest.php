<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('payments.edit')
            || $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,bank_transfer,cheque,other'],
            'reference' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['required', 'date'],
            'receipt' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,pdf',
                'max:3072'
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.max' => 'Receipt must not exceed 3MB.',
        ];
    }
}