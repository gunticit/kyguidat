<?php

namespace App\Http\Requests\Support;

use App\Models\SupportTicket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'category' => ['nullable', Rule::in([
                SupportTicket::CATEGORY_GENERAL,
                SupportTicket::CATEGORY_PAYMENT,
                SupportTicket::CATEGORY_CONSIGNMENT,
                SupportTicket::CATEGORY_ACCOUNT,
                SupportTicket::CATEGORY_OTHER,
            ])],
            'priority' => ['nullable', Rule::in([
                SupportTicket::PRIORITY_LOW,
                SupportTicket::PRIORITY_MEDIUM,
                SupportTicket::PRIORITY_HIGH,
                SupportTicket::PRIORITY_URGENT,
            ])],
            'message' => 'required|string|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'string|url',
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Tiêu đề là bắt buộc',
            'message.required' => 'Nội dung là bắt buộc',
        ];
    }
}
