<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTradeInItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Mặc định là true để cho phép mọi admin có quyền truy cập
        // Bạn có thể thêm logic phân quyền phức tạp hơn ở đây nếu cần
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // Thông tin sản phẩm
            'product_variant_id' => 'required|exists:product_variants,id',
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('trade_in_items', 'sku')->ignore($this->trade_in_item), // Đảm bảo SKU là duy nhất
            ],
            'imei_or_serial' => [
                'required',
                'string',
                'max:255',
                Rule::unique('trade_in_items', 'imei_or_serial')->ignore($this->trade_in_item), // Đảm bảo IMEI/Serial là duy nhất
            ],
            'selling_price' => 'required|numeric|min:0',
            'type' => 'required|in:used,open_box', // Chỉ chấp nhận giá trị 'used' hoặc 'open_box'
            'status' => 'required|in:pending_inspection,available,sold',

            // Tình trạng & Tồn kho
            'condition_grade' => 'required|string|max:255', // Ví dụ: 'A', 'B', 'C'
            'store_location_id' => 'required|exists:store_locations,id',
            'condition_description' => 'required|string',

            // Hình ảnh
            'images' => 'nullable|array', // Phải là một mảng
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120', // Mỗi file phải là ảnh và dung lượng tối đa 5MB
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'product_variant_id.required' => 'Vui lòng chọn sản phẩm gốc.',
            'imei_or_serial.required' => 'Vui lòng nhập IMEI hoặc số Serial.',
            'imei_or_serial.unique' => 'IMEI hoặc số Serial này đã tồn tại trong hệ thống.',
            'selling_price.required' => 'Vui lòng nhập giá bán.',
            'selling_price.numeric' => 'Giá bán phải là một con số.',
            'condition_description.required' => 'Vui lòng mô tả chi tiết tình trạng sản phẩm.',
            'images.*.image' => 'File tải lên phải là một hình ảnh.',
            'images.*.mimes' => 'Chỉ hỗ trợ các định dạng ảnh: jpeg, png, jpg, webp.',
            'images.*.max' => 'Dung lượng mỗi ảnh không được vượt quá 5MB.',
        ];
    }
}
