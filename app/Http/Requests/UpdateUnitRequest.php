<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('units.edit')
            || $this->user()->isSuperAdmin();

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'unit_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'unit_number')->ignore($this->unit)
            ],
            'floor_id' => ['nullable', 'exists:floors,id'],
            'block_id' => ['nullable', 'exists:blocks,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'landlord_id' => ['required', 'exists:landlords,id'],
            'type' => ['required', 'in:flat,shop,office'],
            'status' => ['required', 'in:vacant,occupied,sold'],
            'area_sqft' => ['nullable', 'numeric', 'min:0'],
            'elec_meter_id' => ['nullable', 'string', 'max:50'],
            'water_meter_id' => ['nullable', 'string', 'max:50'],
            'gas_meter_id' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
