<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SupportTicketTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketRequest extends FormRequest
{
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
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:' . implode(',', array_column(SupportTicketTypeEnum::cases(), 'value'))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'max:5120'],
        ];
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
