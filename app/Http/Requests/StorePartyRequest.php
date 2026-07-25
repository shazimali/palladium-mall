<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('parties.create')
            || $this->user()->isSuperAdmin();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('opening_balance') && !is_null($this->opening_balance)) {
            $clean = str_replace(',', '', (string) $this->opening_balance);
            $this->merge([
                'opening_balance' => $clean === '' ? 0 : $clean,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['nullable', 'numeric'],
        ];
    }
}
