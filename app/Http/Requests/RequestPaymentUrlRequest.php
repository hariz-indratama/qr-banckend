<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestPaymentUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Asumsikan diizinkan, autentikasi bisa ditangani Middleware
    }

    public function rules(): array
    {
        return [
            'merchant_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:100'],
            'payment_method' => ['required', 'string'],
        ];
    }
}
