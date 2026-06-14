<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('units.create')
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
            'unit_number' => ['required', 'string', 'max:20', Rule::unique('units', 'unit_number')],
            'floor_id' => ['required', 'exists:floors,id'],
            'block_id' => ['required', 'exists:blocks,id'],
            'area_id' => ['required', 'exists:areas,id'],
            'landlord_id' => ['required', 'exists:landlords,id'],
            'type' => ['required', 'in:flat,shop,office'],
            'status' => ['required', 'in:vacant,rented,self'],
            'file_no' => ['nullable', 'string', 'max:100', Rule::unique('units', 'file_no')],
            'area_sqft' => ['nullable', 'numeric', 'min:0'],
            'elec_meter_id' => ['nullable', 'string', 'max:50'],
            'water_meter_id' => ['nullable', 'string', 'max:50'],
            'gas_meter_id' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'date' => ['nullable', 'date'],
        ];
    }
}
