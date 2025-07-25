<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTradeInItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Thay đổi logic phân quyền nếu cần
        return true;
    }

   
    public function rules(): array
    {
        // Lấy ID của item đang được sửa từ route
        $tradeInItemId = $this->route('trade_in_item')->id;

        return [
            'product_variant_id'    => 'required|integer|exists:product_variants,id',
            'sku'                   => ['nullable', 'string', 'max:255', Rule::unique('trade_in_items')->ignore($tradeInItemId)],
            'imei_or_serial'        => ['required', 'string', 'max:255', Rule::unique('trade_in_items')->ignore($tradeInItemId)],
            'selling_price'         => 'required|numeric|min:0',
            'type'                  => 'required|string|in:used,open_box',
            'status'                => 'required|string|in:pending_inspection,available,sold',
            'condition_grade'       => 'required|string|in:A,B,C',
            'store_location_id'     => 'required|integer|exists:store_locations,id',
            'condition_description' => 'required|string|max:1000',

            // Validation cho ảnh
            'primary_image_id'      => [
                'required',
                'integer',
                'exists:uploaded_files,id',
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
