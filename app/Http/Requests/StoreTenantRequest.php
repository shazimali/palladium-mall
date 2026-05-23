<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('tenants.create')
            || $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'exists:units,id'],
            'name' => ['required', 'string', 'max:100'],
            'cnic' => [
                'required',
                'string',
                'max:20',
                'unique:tenants,cnic',
                'regex:/^\d{5}-\d{7}-\d{1}$/'
            ],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'dependents' => ['nullable', 'integer', 'min:0', 'max:20'],
            'cnic_front_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'cnic_back_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'cnic.regex' => 'CNIC format must be: 35201-1234567-1',
            'cnic.unique' => 'This CNIC is already registered.',
            'unit_id.exists' => 'Selected unit does not exist.',
            'cnic_front_image.max' => 'CNIC front image must not exceed 2MB.',
            'cnic_back_image.max' => 'CNIC back image must not exceed 2MB.',
        ];
    }
}