<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SupportTicketTypeEnum;
use App\Http\Requests\Concerns\ValidatesImageUploadTotals;
use App\Support\UploadLimits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketRequest extends FormRequest
{
    use ValidatesImageUploadTotals;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $this->merge(['title' => strip_tags((string) $this->title)]);
        }
        if ($this->has('description')) {
            $this->merge(['description' => strip_tags((string) $this->description)]);
        }
        if (! $this->filled('service_id')) {
            $this->merge(['service_id' => null]);
        } else {
            $this->merge(['service_id' => (int) $this->input('service_id')]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:' . implode(',', array_column(SupportTicketTypeEnum::cases(), 'value'))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
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
            'type' => 'тип обращения',
            'title' => 'тема',
            'description' => 'описание',
            'images' => 'изображения',
        ];
    }
}
