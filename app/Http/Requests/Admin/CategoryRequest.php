<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $current = $this->route('category');
        $ignoreId = $current instanceof Category ? $current->getKey() : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($ignoreId): void {
                    $slug = Str::slug((string) $value);

                    if ($slug === '') {
                        $fail('The name needs at least one letter or number.');

                        return;
                    }

                    $taken = Category::query()
                        ->where('slug', $slug)
                        ->whereKeyNot($ignoreId ?? 0)
                        ->exists();

                    if ($taken) {
                        $fail('Another category already uses that name.');
                    }
                },
            ],
            'slug' => ['required', 'string', 'max:255'],
        ];
    }
}
