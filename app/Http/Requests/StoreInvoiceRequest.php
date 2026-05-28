<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('invoices.create')
            || $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'month' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', 'in:rent,maintenance,electricity,water,gas,fine,other'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one invoice item is required.',
            'items.min' => 'At least one invoice item is required.',
            'items.*.amount.min' => 'Item amount cannot be negative.',
        ];
    }
}