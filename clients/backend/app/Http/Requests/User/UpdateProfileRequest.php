<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'phone' => [
                'nullable',
                'string',
                // Vietnamese mobile: 0 + carrier prefix (3,5,7,8,9) + 8 digits = 10 total.
                'regex:/^0(3|5|7|8|9)[0-9]{8}$/',
                Rule::unique('users', 'phone')->ignore($userId)->whereNotNull('phone'),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.max' => 'Họ và tên không được vượt quá :max ký tự.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'email.max' => 'Email không được vượt quá :max ký tự.',
            'phone.regex' => 'Số điện thoại không hợp lệ. Định dạng: 10 chữ số bắt đầu bằng 03/05/07/08/09.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng bởi tài khoản khác.',
            'address.max' => 'Địa chỉ không được vượt quá :max ký tự.',
            'avatar.max' => 'Đường dẫn avatar không được vượt quá :max ký tự.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'họ và tên',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'address' => 'địa chỉ',
            'avatar' => 'ảnh đại diện',
        ];
    }
}
