<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesImageUploadTotals;
use App\Support\UploadLimits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutPageRequest extends FormRequest
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
            'address' => ['required', 'string', 'max:512'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:' . UploadLimits::imageMaxKb()],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer'],
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
            'images' => 'фото',
            'address' => 'адрес',
        ];
    }
}
