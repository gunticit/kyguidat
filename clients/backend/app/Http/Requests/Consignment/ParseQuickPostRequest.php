<?php

namespace App\Http\Requests\Consignment;

use Illuminate\Foundation\Http\FormRequest;

class ParseQuickPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => 'required|string|min:20|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => 'Vui lòng dán nội dung bài đăng',
            'text.min' => 'Nội dung quá ngắn, cần ít nhất 20 ký tự',
            'text.max' => 'Nội dung quá dài, tối đa 5,000 ký tự',
        ];
    }
}
