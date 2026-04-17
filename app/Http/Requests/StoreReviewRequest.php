<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesImageUploadTotals;
use App\Support\UploadLimits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    use ValidatesImageUploadTotals;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('body')) {
            $this->merge(['body' => strip_tags((string) $this->body)]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'max:' . UploadLimits::imageMaxKb()],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateImagesArrayTotalSize($validator, 'images');
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'rating' => 'оценка',
            'body' => 'текст отзыва',
            'images' => 'фото',
        ];
    }
}
