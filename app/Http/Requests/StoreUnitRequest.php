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
            'unit_number'             => ['required', 'string', 'max:20', Rule::unique('units', 'unit_number')],
            'floor_id'                => ['required', 'exists:floors,id'],
            'block_id'                => ['nullable', 'exists:blocks,id'],
            'area_id'                 => ['nullable', 'exists:areas,id'],
            'landlord_id'             => ['nullable', 'exists:landlords,id'],
            'type'                    => ['required', 'in:flat,shop,office'],
            'status'                  => ['nullable', 'in:vacant,rented,self'],
            'file_no'                 => ['nullable', 'string', 'max:100', Rule::unique('units', 'file_no')],
            'area_sqft'               => ['nullable', 'numeric', 'min:0'],
            'is_self'                 => ['nullable', 'boolean'],
            'default_maintenance_charge' => ['nullable', 'numeric', 'min:0'],
            'default_monthly_rent'       => ['nullable', 'numeric', 'min:0'],
            
            // Nominee
            'nominee_name'            => ['nullable', 'string', 'max:255'],
            'nominee_relation_type'   => ['nullable', 'in:son_of,daughter_of,wife_of'],
            'nominee_relation_name'   => ['nullable', 'string', 'max:255'],
            
            // Financial
            'total_amount'            => ['nullable', 'numeric', 'min:0'],
            'received_amount'         => ['nullable', 'numeric', 'min:0'],
            'received_from'           => ['nullable', 'string', 'max:255'],
            
            // Office
            'approved_by'             => ['nullable', 'string', 'max:255'],
            'received_by'             => ['nullable', 'string', 'max:255'],
            'approved_date'           => ['nullable', 'date'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
            'date'                    => ['nullable', 'date'],
        ];
    }
}
