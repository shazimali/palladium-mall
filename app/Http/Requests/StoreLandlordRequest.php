<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLandlordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('landlords.create')
            || $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:landlords,email'],
            'phone' => ['required', 'string', 'max:50'],
            'cnic' => [
                'required',
                'string',
                'max:50',
                'unique:landlords,cnic',
                'regex:/^\d{5}-\d{7}-\d{1}$/'
            ],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'cnic.regex' => 'CNIC format must be: 35201-1234567-1',
            'cnic.unique' => 'This CNIC is already registered.',
        ];
    }
}
