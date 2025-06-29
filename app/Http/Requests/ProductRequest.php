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
            'status' => 'required|in:published,draft,pending_review,trashed',
            'type' => 'required|in:simple,variable',

            // --- Image Rules ---
            'cover_image_id' => 'required_if:type,simple|nullable|integer|exists:uploaded_files,id',
            
            // --- Variable Product Rules ---
            'variants' => 'required_if:type,variable|array|min:1',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.price' => 'required_if:type,variable|nullable|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0|lte:variants.*.price', // Giá KM phải nhỏ hơn hoặc bằng giá gốc
            
            // MODIFIED: Date fields are now optional.
            'variants.*.sale_price_starts_at' => ['nullable', 'date'], 
            'variants.*.sale_price_ends_at' => ['nullable', 'date', 'after:variants.*.sale_price_starts_at'], 

            'variants.*.stock_quantity' => 'required_if:type,variable|nullable|integer|min:0',
            'variants.*.attributes' => 'required_if:type,variable|array|min:1',
            'variants.*.attributes.*' => 'required|integer|exists:attribute_values,id',
            'variants.*.sku' => ['required_if:type,variable', 'nullable', 'string', 'max:255', 'distinct:ignore_case'],
        ];

        // --- Simple Product Rules ---
        if ($this->input('type') === 'simple') {
            $rules['simple_price'] = 'required|numeric|min:0';
            $rules['simple_sale_price'] = 'nullable|numeric|min:0|lte:simple_price'; // Giá KM phải nhỏ hơn hoặc bằng giá gốc
            
            // MODIFIED: Date fields are now optional.
            $rules['simple_sale_price_starts_at'] = ['nullable', 'date']; 
            $rules['simple_sale_price_ends_at'] = ['nullable', 'date', 'after:simple_sale_price_starts_at']; 

            $rules['simple_stock_quantity'] = 'required|integer|min:0';
            $rules['simple_sku'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
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
                            $validator->errors()->add("variants.{$index}.sku", "SKU '{$sku}' đã được sử dụng.");
                        }
                    }

                    // 2. Kiểm tra xem có sự trùng lặp về tổ hợp thuộc tính không
                    $attributes = $variant['attributes'] ?? [];
                    if (!empty($attributes)) {
                        sort($attributes);
                        $combination = implode('-', $attributes);
                        if (in_array($combination, $variantAttributeCombinations)) {
                            $validator->errors()->add("variants.{$index}.attributes", 'Tổ hợp thuộc tính này đã tồn tại.');
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
            
            // Simple Product Messages
            'simple_price.required' => 'Giá sản phẩm đơn giản không được để trống.',
            'simple_sale_price.lte' => 'Giá khuyến mãi phải nhỏ hơn hoặc bằng giá gốc.',
            'simple_sale_price_ends_at.after' => 'Thời gian kết thúc khuyến mãi phải sau thời gian bắt đầu.',

            // Variants Messages
            'variants.required_if' => 'Sản phẩm có biến thể phải có ít nhất một biến thể.',
            'variants.min' => 'Sản phẩm có biến thể phải có ít nhất một biến thể.',
            'variants.*.sku.required_if' => 'SKU của biến thể không được để trống.',
            'variants.*.sku.distinct' => 'SKU của các biến thể trong form không được trùng nhau.',
            'variants.*.price.required_if' => 'Giá của biến thể không được để trống.',
            'variants.*.sale_price.lte' => 'Giá khuyến mãi của biến thể phải nhỏ hơn hoặc bằng giá gốc.',
            'variants.*.stock_quantity.required_if' => 'Tồn kho của biến thể không được để trống.',
            'variants.*.attributes.required_if' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
            'variants.*.attributes.min' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
            'variants.*.sale_price_ends_at.after' => 'Thời gian kết thúc khuyến mãi của biến thể phải sau thời gian bắt đầu.',
        ];
    }
}
