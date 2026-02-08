<?php

namespace App\Http\Requests\Consignment;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'address' => 'required|string|max:500',
            'google_map_link' => 'nullable|string|url|max:500',
            'price' => 'required|numeric|min:1000000',
            'min_price' => 'nullable|numeric|min:1000000|lte:price',
            'seller_phone' => 'required|string|regex:/^[0-9]{10,11}$/',
            'images' => 'nullable|array|max:20',
            'images.*' => 'string',
            'description_files' => 'nullable|array|max:5',
            'description_files.*' => 'string',
            'note_to_admin' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề là bắt buộc',
            'title.max' => 'Tiêu đề tối đa 255 ký tự',
            'description.max' => 'Nội dung rao bán tối đa 10,000 ký tự',
            'address.required' => 'Địa chỉ là bắt buộc',
            'address.max' => 'Địa chỉ tối đa 500 ký tự',
            'google_map_link.url' => 'Link Google Map không hợp lệ',
            'price.required' => 'Giá mong muốn là bắt buộc',
            'price.min' => 'Giá tối thiểu là 1,000,000đ',
            'min_price.min' => 'Giá thấp nhất tối thiểu là 1,000,000đ',
            'min_price.lte' => 'Giá thấp nhất phải nhỏ hơn hoặc bằng giá mong muốn',
            'seller_phone.required' => 'Số điện thoại người bán là bắt buộc',
            'seller_phone.regex' => 'Số điện thoại phải có 10-11 chữ số',
            'images.max' => 'Tối đa 20 hình ảnh',
            'description_files.max' => 'Tối đa 5 file mô tả',
            'note_to_admin.max' => 'Chú thích tối đa 2,000 ký tự',
        ];
    }
}
