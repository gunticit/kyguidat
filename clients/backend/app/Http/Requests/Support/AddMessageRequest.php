<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class AddMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'string|url',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Nội dung tin nhắn là bắt buộc',
            'message.max' => 'Tin nhắn không được vượt quá 5000 ký tự',
        ];
    }
}
