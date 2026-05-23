<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('agreements.create')
            || $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:30'],
            'fine_per_day' => ['required', 'numeric', 'min:0'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:active,expired,terminated'],
            'document' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,pdf',
                'max:5120'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after' => 'End date must be after start date.',
            'document.max' => 'Document must not exceed 5MB.',
            'tenant_id.exists' => 'Selected tenant does not exist.',
            'unit_id.exists' => 'Selected unit does not exist.',
        ];
    }
}