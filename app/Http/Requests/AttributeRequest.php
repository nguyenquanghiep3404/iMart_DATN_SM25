<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Cho phép mọi request, bạn có thể thêm logic phân quyền ở đây
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Lấy ID từ route parameter, sẽ có giá trị khi update và null khi store
        $attributeId = $this->route('attribute') ? $this->route('attribute')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attributes', 'name')->ignore($attributeId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('attributes', 'slug')->ignore($attributeId),
            ],
            'display_type' => 'required|in:select,radio,color_swatch',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên thuộc tính không được để trống.',
            'name.unique'   => 'Tên thuộc tính này đã tồn tại.',
            'slug.unique'   => 'Đường dẫn tĩnh (slug) này đã tồn tại.',
            'display_type.required' => 'Vui lòng chọn kiểu hiển thị.',
        ];
    }
}