<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    /**
     * Billing-only update — notes field only.
     * Meter changes go through the existing AJAX meter endpoint.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('units.edit')
            || $this->user()->isSuperAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
