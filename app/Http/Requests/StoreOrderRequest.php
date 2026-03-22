<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasVerifiedEmail();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email'],
            'delivery_type' => ['required', 'in:delivery,pickup'],
            'address' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:500'],
            'payment' => ['required', 'in:cash,card'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'имя',
            'phone' => 'телефон',
            'email' => 'email',
            'delivery_type' => 'способ доставки',
            'address' => 'адрес',
            'payment' => 'способ оплаты',
            'comment' => 'комментарий',
        ];
    }
}
