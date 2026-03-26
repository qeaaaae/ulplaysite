<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->whereNotNull('parent_id')),
            ],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:4096'],
            'in_stock' => ['nullable'],
            'stock' => ['required', 'integer', 'min:0'],
            'discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_new' => ['nullable'],
            'is_recommended' => ['nullable'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('title', '')),
            'in_stock' => $this->boolean('in_stock'),
            'is_new' => $this->boolean('is_new'),
            'is_recommended' => $this->boolean('is_recommended'),
        ]);
    }
}
