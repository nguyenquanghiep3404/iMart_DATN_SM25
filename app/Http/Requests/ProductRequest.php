<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProductRequest extends FormRequest
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
     * Đây là các quy tắc validation cơ bản, không bao gồm các logic phức tạp.
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product ? $product->id : null;

        $rules = [
            // --- General Rules ---
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:published,draft,pending_review',
            'type' => 'required|in:simple,variable',

            // --- Image Rules (Basic) ---
            'cover_image_id' => 'required_if:type,simple|nullable|integer|exists:uploaded_files,id',
            
            // --- Variable Product Rules (Basic) ---
            'variants' => 'required_if:type,variable|array|min:1',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.price' => 'required_if:type,variable|nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'required_if:type,variable|nullable|integer|min:0',
            'variants.*.attributes' => 'required_if:type,variable|array|min:1',
            'variants.*.attributes.*' => 'required|integer|exists:attribute_values,id',
            'variants.*.sku' => ['required_if:type,variable', 'nullable', 'string', 'max:255', 'distinct:ignore_case'],
        ];

        // --- Simple Product Rules (Basic) ---
        if ($this->input('type') === 'simple') {
            $rules['simple_price'] = 'required|numeric|min:0';
            $rules['simple_stock_quantity'] = 'required|integer|min:0';
            $rules['simple_sku'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     * Thêm các logic validation phức tạp hơn sau khi các quy tắc cơ bản đã được kiểm tra.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Chỉ thực hiện các kiểm tra này nếu loại sản phẩm là 'variable'
            if ($this->input('type') === 'variable') {
                $variants = $this->input('variants', []);
                $variantAttributeCombinations = [];

                foreach ($variants as $index => $variant) {
                    
                    // 1. Kiểm tra SKU có tồn tại trong database không, bỏ qua chính nó khi cập nhật
                    $sku = $variant['sku'] ?? null;
                    $id = $variant['id'] ?? null;

                    if ($sku) {
                        $query = DB::table('product_variants')->where('sku', $sku);
                        if ($id) {
                            $query->where('id', '!=', $id);
                        }
                        if ($query->exists()) {
                            // Thêm lỗi vào đúng trường SKU của biến thể đó
                            $validator->errors()->add("variants.{$index}.sku", "SKU '{$sku}' đã được sử dụng ở một sản phẩm khác.");
                        }
                    }

                    // 2. Kiểm tra xem có sự trùng lặp về tổ hợp thuộc tính không
                    $attributes = $variant['attributes'] ?? [];
                    if (!empty($attributes)) {
                        sort($attributes); // Sắp xếp để đảm bảo thứ tự không ảnh hưởng
                        $combination = implode('-', $attributes);
                        if (in_array($combination, $variantAttributeCombinations)) {
                            $validator->errors()->add("variants.{$index}.attributes", 'Tổ hợp thuộc tính này đã tồn tại ở một biến thể khác trong form.');
                        } else {
                            $variantAttributeCombinations[] = $combination;
                        }
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.unique' => 'Tên sản phẩm này đã tồn tại.',
            'category_id.required' => 'Vui lòng chọn danh mục cho sản phẩm.',
            'cover_image_id.required_if' => 'Sản phẩm đơn giản phải có ảnh bìa.',
            
            'variants.required_if' => 'Sản phẩm có biến thể phải có ít nhất một biến thể.',
            'variants.min' => 'Sản phẩm có biến thể phải có ít nhất một biến thể.',

            'variants.*.sku.required_if' => 'SKU của biến thể không được để trống.',
            'variants.*.sku.distinct' => 'SKU của các biến thể trong form không được trùng nhau.',
            'variants.*.price.required_if' => 'Giá của biến thể không được để trống.',
            'variants.*.stock_quantity.required_if' => 'Tồn kho của biến thể không được để trống.',
            'variants.*.attributes.required_if' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
            'variants.*.attributes.min' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
        ];
    }
}