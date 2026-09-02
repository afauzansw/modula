<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyRoleRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                'exists:roles,id',
                // Reject the whole request if any id is a system role rather
                // than silently skipping them (see EloquentRoleRepository).
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Role::query()->whereKey($value)->where('is_system', true)->exists()) {
                        $fail('System roles cannot be deleted.');
                    }
                },
            ],
        ];
    }
}
