<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreNewsRequest extends FormRequest
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
            'slug' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'url', 'max:512', function (string $attr, mixed $value, \Closure $fail) {
                if ($value && ! app(\App\Services\VideoEmbedService::class)->isValidUrl($value)) {
                    $fail('Ссылка должна быть с YouTube или Rutube.');
                }
            }],
            'published_at' => ['nullable', 'date'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:4096'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $videoUrl = $this->input('video_url');
        $trimmed = is_string($videoUrl) ? trim($videoUrl) : '';
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('title', '')),
            'video_url' => $trimmed === '' ? null : $trimmed,
        ]);
    }
}
