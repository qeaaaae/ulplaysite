<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesImageUploadTotals;
use App\Services\VideoEmbedService;
use App\Support\UploadLimits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    use ValidatesImageUploadTotals;

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
            'avito_url' => ['nullable', 'string', 'url', 'max:1024'],
            'description' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'url', 'max:512', function (string $attr, mixed $value, \Closure $fail) {
                if ($value && ! app(VideoEmbedService::class)->isValidUrl($value)) {
                    $fail('Ссылка должна быть с YouTube или Rutube.');
                }
            }],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->whereNotNull('parent_id')),
            ],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:' . UploadLimits::imageMaxKb()],
            'in_stock' => ['nullable'],
            'stock' => ['required', 'integer', 'min:0'],
            'discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_new' => ['nullable'],
            'is_recommended' => ['nullable'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateImagesArrayTotalSize($validator, 'images');
    }

    protected function prepareForValidation(): void
    {
        $videoUrl = $this->input('video_url');
        $trimmed = is_string($videoUrl) ? trim($videoUrl) : '';
        $avitoUrl = $this->input('avito_url');
        $avitoTrimmed = is_string($avitoUrl) ? trim($avitoUrl) : '';

        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('title', '')),
            'avito_url' => $avitoTrimmed === '' ? null : $avitoTrimmed,
            'video_url' => $trimmed === '' ? null : $trimmed,
            'in_stock' => $this->boolean('in_stock'),
            'is_new' => $this->boolean('is_new'),
            'is_recommended' => $this->boolean('is_recommended'),
        ]);
    }
}
