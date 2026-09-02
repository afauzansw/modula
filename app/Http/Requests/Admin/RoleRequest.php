<?php

namespace App\Http\Requests\Admin;

use App\Enums\AdminPermission;
use App\Enums\SystemRole;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($this->route('role')),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (SystemRole::isReserved($value)) {
                        $fail('That role name is reserved for a system role.');
                    }
                },
            ],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(AdminPermission::values())],
        ];
    }
}
