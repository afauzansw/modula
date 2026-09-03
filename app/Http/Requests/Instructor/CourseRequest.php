<?php

namespace App\Http\Requests\Instructor;

use App\Models\Course;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('title')),
            'is_free' => $this->boolean('is_free'),
        ]);

        if ($this->boolean('is_free')) {
            $this->merge(['price' => 0]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $current = $this->route('course');
        $ignoreId = $current instanceof Course ? $current->getKey() : null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($ignoreId): void {
                    $slug = Str::slug((string) $value);

                    if ($slug === '') {
                        $fail('The title needs at least one letter or number.');

                        return;
                    }

                    if (Course::query()->where('slug', $slug)->whereKeyNot($ignoreId ?? 0)->exists()) {
                        $fail('A course with a similar title already exists.');
                    }
                },
            ],
            'slug' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_free' => ['required', 'boolean'],
            'price' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
