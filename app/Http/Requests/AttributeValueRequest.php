<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttributeValueRequest extends FormRequest
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
        // Lấy model Attribute từ route
        $attribute = $this->route('attribute');

        // Lấy ID của AttributeValue, sẽ có giá trị khi update và null khi store
        $valueId = $this->route('value') ? $this->route('value')->id : null;
        
        // **LƯU Ý QUAN TRỌNG**: Các quy tắc dưới đây giả định rằng
        // tên input trong form của bạn là 'value' và 'meta'.
        // Xem hướng dẫn ở phần Controller để điều chỉnh form cho phù hợp.

        return [
            'value' => [
                'required',
                'string',
                'max:255',
                // Quy tắc unique: giá trị phải là duy nhất cho attribute_id này,
                // và bỏ qua chính nó khi cập nhật.
                Rule::unique('attribute_values', 'value')
                    ->where('attribute_id', $attribute->id)
                    ->ignore($valueId),
            ],
            'meta' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'value.required' => 'Giá trị không được để trống.',
            'value.unique'   => 'Giá trị này đã tồn tại cho thuộc tính hiện tại.',
        ];
    }
}