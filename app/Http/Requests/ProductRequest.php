<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

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

        $defaultVariantId = null;
        if ($product && $product->type === 'simple') {
            $defaultVariant = $product->variants()->where('is_default', true)->first();
            $defaultVariantId = $defaultVariant ? $defaultVariant->id : null;
        }

        return [
            // --- General Rules ---
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'category_id' => 'required|exists:categories,id',
            'short_description' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'status' => 'required|in:published,draft,pending_review,trashed',
            'is_featured' => 'nullable|boolean',
            'type' => 'required|in:simple,variable',

            // --- Rules for Simple Product ---
            'simple_sku' => ['required_if:type,simple', 'nullable', 'string', 'max:255', Rule::unique('product_variants', 'sku')->ignore($defaultVariantId)],
            'simple_price' => 'required_if:type,simple|nullable|numeric|min:0',
            'simple_sale_price' => 'nullable|numeric|min:0|lt:simple_price',
            'simple_stock_quantity' => 'required_if:type,simple|nullable|integer|min:0',
            'simple_sale_price_starts_at' => 'nullable|date',
            'simple_sale_price_ends_at' => 'nullable|date|after_or_equal:simple_sale_price_starts_at',
            'simple_weight' => 'nullable|numeric|min:0',
            'simple_dimensions_length' => 'nullable|numeric|min:0',
            'simple_dimensions_width' => 'nullable|numeric|min:0',
            'simple_dimensions_height' => 'nullable|numeric|min:0',

            // --- Image Rules ---
            'cover_image_id' => 'required_if:type,simple|integer|exists:uploaded_files,id',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'integer|exists:uploaded_files,id',
            'variants.*.image_ids' => 'nullable|array',
            'variants.*.image_ids.*' => 'integer|exists:uploaded_files,id',
            'variants.*.primary_image_id' => [
                'nullable', 'integer', 'exists:uploaded_files,id',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $imageIds = $this->input("variants.{$index}.image_ids", []);
                    if ($value && !in_array($value, $imageIds)) {
                        $fail('Ảnh chính phải là một trong những ảnh đã được upload cho biến thể này.');
                    }
                },
            ],

            // --- Rules for Variable Product ---
            'sku_prefix' => 'nullable|string|max:50',
            'variants' => 'required_if:type,variable|array|min:1',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.sku' => [
                'required_if:type,variable', 'nullable', 'string', 'max:255', 'distinct',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $variantId = $this->input("variants.{$index}.id");
                    $query = DB::table('product_variants')->where('sku', $value);
                    if ($variantId) {
                        $query->where('id', '!=', $variantId);
                    }
                    if ($query->exists()) {
                        $fail("SKU '{$value}' đã được sử dụng.");
                    }
                }
            ],
            'variants.*.price' => 'required_if:type,variable|nullable|numeric|min:0',
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0', function ($attribute, $value, $fail) {
                $index = explode('.', $attribute)[1];
                $price = $this->input("variants.{$index}.price");
                if ($value !== null && $price !== null && $value >= $price) {
                    $fail('Giá khuyến mãi của biến thể phải nhỏ hơn giá gốc.');
                }
            }],
            'variants.*.stock_quantity' => 'required_if:type,variable|nullable|integer|min:0',
            'variants.*.attributes' => 'required_if:type,variable|array|min:1',
            'variants.*.attributes.*' => 'required|exists:attribute_values,id',
            'variants.*.is_default' => 'required|in:true,false',
            'variants.*.sale_price_starts_at' => 'nullable|date',
            'variants.*.sale_price_ends_at' => ['nullable', 'date', function ($attribute, $value, $fail) {
                $index = explode('.', $attribute)[1];
                $startDate = $this->input("variants.{$index}.sale_price_starts_at");
                if ($value && $startDate && strtotime($value) < strtotime($startDate)) {
                    $fail('Ngày kết thúc khuyến mãi của biến thể phải sau hoặc bằng ngày bắt đầu.');
                }
            }],
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.dimensions_length' => 'nullable|numeric|min:0',
            'variants.*.dimensions_width' => 'nullable|numeric|min:0',
            'variants.*.dimensions_height' => 'nullable|numeric|min:0',

            // --- SEO & Other Rules ---
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'tags' => 'nullable|string',
            'warranty_information' => 'nullable|string',
        ];
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
            'type.required' => 'Vui lòng chọn loại sản phẩm.',

            // Simple Product
            'simple_sku.required_if' => 'SKU của sản phẩm không được để trống.',
            'simple_sku.unique' => 'SKU này đã tồn tại.',
            'simple_price.required_if' => 'Giá của sản phẩm không được để trống.',
            'simple_sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'simple_stock_quantity.required_if' => 'Số lượng tồn kho không được để trống.',
            'simple_sale_price_ends_at.after_or_equal' => 'Ngày kết thúc khuyến mãi phải sau hoặc bằng ngày bắt đầu.',
            
            // Image IDs
            'cover_image_id.required_if' => 'Sản phẩm đơn giản phải có ít nhất một hình ảnh (và phải được chọn làm ảnh bìa).',
            'cover_image_id.exists' => 'Ảnh bìa được chọn không hợp lệ.',
            'gallery_images.*.exists' => 'Một ảnh trong thư viện không hợp lệ.',

            // Variable Product
            'variants.required_if' => 'Cần có ít nhất một biến thể.',
            'variants.min' => 'Cần có ít nhất một biến thể.',
            'variants.*.sku.required_if' => 'SKU của biến thể không được để trống.',
            'variants.*.sku.distinct' => 'SKU của các biến thể không được trùng nhau.',
            'variants.*.price.required_if' => 'Giá của biến thể không được để trống.',
            'variants.*.stock_quantity.required_if' => 'Tồn kho của biến thể không được để trống.',
            'variants.*.attributes.required_if' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
            'variants.*.attributes.min' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
            
            // Image IDs for Variants
            'variants.*.image_ids.*.exists' => 'Một ảnh của biến thể không hợp lệ.',
            'variants.*.primary_image_id.exists' => 'Ảnh chính của biến thể không hợp lệ.',
        ];
    }
}
