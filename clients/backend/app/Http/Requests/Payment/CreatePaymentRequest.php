<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:10000|max:100000000',
            'method' => 'nullable|string|in:bank_transfer,sepay',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Số tiền là bắt buộc',
            'amount.min' => 'Số tiền tối thiểu là 10,000đ',
            'amount.max' => 'Số tiền tối đa là 100,000,000đ',
        ];
    }
}
