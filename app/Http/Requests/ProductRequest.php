<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\ProductVariant;

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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        $productId = $product ? $product->id : null;

        $rules = [
            // --- General Rules ---
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:published,draft,pending_review,trashed',
            'type' => 'required|in:simple,variable',
        ];

        // --- Rules based on Product Type ---
        if ($this->input('type') === 'simple') {
            // Khi cập nhật, cần tìm ID của biến thể duy nhất để bỏ qua khi kiểm tra SKU
            $variantIdToIgnore = null;
            if ($product && $product->type === 'simple' && $product->variants->first()) {
                $variantIdToIgnore = $product->variants->first()->id;
            }

            $rules = array_merge($rules, [
                'cover_image_id' => 'required|integer|exists:uploaded_files,id',
                'simple_price' => 'required|numeric|min:0',
                'simple_sale_price' => 'nullable|numeric|min:0|lte:simple_price',
                'simple_sale_price_starts_at' => ['nullable', 'date', 'after_or_equal:now'],
                'simple_sale_price_ends_at' => ['nullable', 'date', 'after:simple_sale_price_starts_at'],
                'simple_sku' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('product_variants', 'sku')->ignore($variantIdToIgnore)
                ],
                // SỬA ĐỔI: Validation cho tồn kho chi tiết của sản phẩm đơn giản để khớp với view
                'simple_inventories' => ['required', 'array'],
                'simple_inventories.new' => ['required', 'integer', 'min:0'],
                'simple_inventories.defective' => ['nullable', 'integer', 'min:0'],
            ]);
        }

        if ($this->input('type') === 'variable') {
            $rules = array_merge($rules, [
                'variants' => 'required|array|min:1',
                'variants.*.id' => 'nullable|integer|exists:product_variants,id',
                'variants.*.price' => 'required|nullable|numeric|min:0',
                'variants.*.sale_price' => 'nullable|numeric|min:0|lte:variants.*.price',
                'variants.*.sale_price_starts_at' => ['nullable', 'date', 'after_or_equal:now'],
                'variants.*.sale_price_ends_at' => ['nullable', 'date', 'after:variants.*.sale_price_starts_at'],
                'variants.*.attributes' => 'required|array|min:1',
                'variants.*.attributes.*' => 'required|integer|exists:attribute_values,id',
                'variants.*.sku' => [
                    'required',
                    'string',
                    'max:255',
                    'distinct:ignore_case', // Kiểm tra trùng lặp SKU trong chính request
                ],
                // SỬA ĐỔI: Validation cho tồn kho chi tiết của biến thể để khớp với view
                'variants.*.inventories' => ['required', 'array'],
                'variants.*.inventories.new' => ['required', 'integer', 'min:0'],
                'variants.*.inventories.defective' => ['nullable', 'integer', 'min:0'],
            ]);
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
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
                    // 1. Kiểm tra SKU của từng biến thể trong database
                    $sku = $variant['sku'] ?? null;
                    $variantId = $variant['id'] ?? null;
                    if ($sku) {
                        $query = ProductVariant::where('sku', $sku);
                        if ($variantId) {
                            $query->where('id', '!=', $variantId);
                        }
                        if ($query->exists()) {
                            $validator->errors()->add("variants.{$index}.sku", "SKU '{$sku}' đã tồn tại trong hệ thống.");
                        }
                    }

                    // 2. Kiểm tra tổ hợp thuộc tính có bị trùng lặp trong form không
                    $attributes = $variant['attributes'] ?? [];
                    if (!empty($attributes)) {
                        sort($attributes);
                        $combination = implode('-', $attributes);
                        if (in_array($combination, $variantAttributeCombinations)) {
                            $validator->errors()->add("variants.{$index}.attributes", 'Tổ hợp thuộc tính này bị trùng lặp trong form.');
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
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.unique' => 'Tên sản phẩm này đã tồn tại.',
            'category_id.required' => 'Vui lòng chọn danh mục cho sản phẩm.',
            'cover_image_id.required' => 'Sản phẩm đơn giản phải có ảnh bìa.',

            // Simple Product Messages
            'simple_price.required' => 'Giá sản phẩm đơn giản không được để trống.',
            'simple_sku.required' => 'SKU sản phẩm đơn giản không được để trống.',
            'simple_sku.unique' => 'SKU này đã tồn tại trong hệ thống.',
            'simple_sale_price.lte' => 'Giá khuyến mãi phải nhỏ hơn hoặc bằng giá gốc.',
            'simple_sale_price_starts_at.after_or_equal' => 'Thời gian bắt đầu khuyến mãi không được là ngày trong quá khứ.',
            'simple_sale_price_ends_at.after' => 'Thời gian kết thúc khuyến mãi phải sau thời gian bắt đầu.',
            'simple_inventories.new.required' => 'Tồn kho "Hàng mới" không được để trống.',
            'simple_inventories.new.min' => 'Tồn kho "Hàng mới" phải là số không âm.',
            'simple_inventories.defective.min' => 'Tồn kho "Hàng lỗi" phải là số không âm.',


            // Variants Messages
            'variants.required' => 'Sản phẩm có biến thể phải có ít nhất một biến thể.',
            'variants.min' => 'Sản phẩm có biến thể phải có ít nhất một biến thể.',
            'variants.*.sku.required' => 'SKU của biến thể không được để trống.',
            'variants.*.sku.distinct' => 'SKU của các biến thể trong form không được trùng nhau.',
            'variants.*.price.required' => 'Giá của biến thể không được để trống.',
            'variants.*.sale_price.lte' => 'Giá khuyến mãi của biến thể phải nhỏ hơn hoặc bằng giá gốc.',
            'variants.*.inventories.new.required' => 'Tồn kho "Hàng mới" của biến thể không được để trống.',
            'variants.*.inventories.new.min' => 'Tồn kho "Hàng mới" của biến thể phải là số không âm.',
            'variants.*.inventories.defective.min' => 'Tồn kho "Hàng lỗi" của biến thể phải là số không âm.',
            'variants.*.attributes.required' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
            'variants.*.attributes.min' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
            'variants.*.sale_price_starts_at.after_or_equal' => 'Thời gian bắt đầu khuyến mãi của biến thể không được là ngày trong quá khứ.',
            'variants.*.sale_price_ends_at.after' => 'Thời gian kết thúc khuyến mãi của biến thể phải sau thời gian bắt đầu.',
        ];
    }
}
