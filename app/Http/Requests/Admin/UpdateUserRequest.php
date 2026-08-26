<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'                     => ['required', 'string', 'max:255'],
            'email'                    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password'                 => ['nullable', 'string', 'confirmed', Password::defaults()],
            'is_active'                => ['nullable', 'boolean'],
            'is_employee'              => ['nullable', 'boolean'],
            'roles'                    => ['nullable', 'array'],
            'roles.*'                  => ['exists:roles,id'],
            'employee_code'            => ['nullable', 'string', 'max:50'],
            'designation'              => ['nullable', 'string', 'max:100'],
            'department'               => ['nullable', 'string', 'max:100'],
            'joined_at'                => ['nullable', 'date'],
            'basic_salary'             => ['nullable', 'numeric', 'min:0'],
            'fuel_allowance'           => ['nullable', 'numeric', 'min:0'],
            'attendance_incentive'     => ['nullable', 'numeric', 'min:0'],
            'collection_incentive_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
