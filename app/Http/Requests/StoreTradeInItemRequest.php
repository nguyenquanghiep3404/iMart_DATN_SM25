<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTradeInItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Hoặc return auth()->check();
    }

    public function rules(): array
    {
        return [
            'product_variant_id'    => 'required|integer|exists:product_variants,id',
            'sku'                   => 'nullable|string|max:255|unique:trade_in_items,sku',
            'imei_or_serial'        => 'required|string|max:255|unique:trade_in_items,imei_or_serial',
            'selling_price'         => 'required|numeric|min:0',
            'type'                  => 'required|string|in:used,open_box',
            'status'                => 'required|string|in:pending_inspection,available,sold',
            'condition_grade'       => 'required|string|in:A,B,C',
            'store_location_id'     => 'required|integer|exists:store_locations,id',
            'condition_description' => 'required|string|max:1000',

            // --- CẬP NHẬT VALIDATION CHO ẢNH ---
            'primary_image_id'      => [
                'required',
                'integer',
                'exists:uploaded_files,id',
                // Đảm bảo ảnh chính phải nằm trong danh sách các ảnh được chọn
                Rule::in($this->input('image_ids', [])),
            ],
            'image_ids'             => 'required|array|min:1',
            'image_ids.*'           => 'required|integer|exists:uploaded_files,id',
        ];
    }

    public function messages(): array
    {
        return [
            'primary_image_id.required' => 'Vui lòng chọn một ảnh làm ảnh đại diện.',
            'image_ids.required' => 'Sản phẩm phải có ít nhất một hình ảnh.',
            'image_ids.min' => 'Sản phẩm phải có ít nhất một hình ảnh.',
        ];
    }
}