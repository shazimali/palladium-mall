<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')?->id;

        return [
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name,' . $permissionId],
            'display_name' => ['required', 'string', 'max:150'],
            'group' => ['required', 'string', 'max:100'],
        ];
    }
}
