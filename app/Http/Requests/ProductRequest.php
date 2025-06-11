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
        // Cho phép mọi người dùng đã xác thực, có thể thêm logic phân quyền ở đây
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product ? $product->id : null;

        // Lấy ID của biến thể mặc định cho sản phẩm đơn giản (chỉ khi update)
        $defaultVariantId = null;
        if ($product && $product->type === 'simple') {
            $defaultVariant = $product->variants()->where('is_default', true)->first();
            $defaultVariantId = $defaultVariant ? $defaultVariant->id : null;
        }

        return [
            // --- Quy tắc chung ---
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'category_id' => 'required|exists:categories,id',
            'short_description' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'status' => 'required|in:published,draft,pending_review,trashed',
            'is_featured' => 'nullable|boolean',
            'type' => 'required|in:simple,variable',

            // --- Quy tắc cho sản phẩm đơn giản ---
            'simple_sku' => ['required_if:type,simple', 'nullable', 'string', 'max:255', Rule::unique('product_variants', 'sku')->ignore($defaultVariantId)],
            'simple_price' => 'required_if:type,simple|nullable|numeric|min:0',
            'simple_sale_price' => 'nullable|numeric|min:0|lt:simple_price',
            'simple_stock_quantity' => 'required_if:type,simple|nullable|integer|min:0',

            // =========================================================================
            // SỬA ĐỔI: Quy tắc xác thực ID ảnh thay vì file upload
            // =========================================================================
            // Ảnh cho sản phẩm đơn giản
            'cover_image_id' => 'required_if:type,simple|integer|exists:uploaded_files,id',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'integer|exists:uploaded_files,id',

            // Ảnh cho sản phẩm có biến thể
            'variants.*.image_ids' => 'nullable|array',
            'variants.*.image_ids.*' => 'integer|exists:uploaded_files,id',
            'variants.*.primary_image_id' => [
                'nullable',
                'integer',
                'exists:uploaded_files,id',
                // Đảm bảo primary_image_id phải nằm trong danh sách image_ids của chính biến thể đó
                function ($attribute, $value, $fail) {
                    // $attribute là 'variants.0.primary_image_id' -> index là 0
                    $index = explode('.', $attribute)[1];
                    
                    // Lấy danh sách image_ids của cùng biến thể đó
                    $imageIds = $this->input("variants.{$index}.image_ids", []);

                    if ($value && !in_array($value, $imageIds)) {
                        $fail('Ảnh chính phải là một trong những ảnh đã được upload cho biến thể này.');
                    }
                },
            ],
            // =========================================================================

            // --- Quy tắc cho sản phẩm có biến thể ---
            'sku_prefix' => 'nullable|string|max:50',
            'variants' => 'required_if:type,variable|array|min:1',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.sku' => [
                'required_if:type,variable',
                'nullable',
                'string',
                'max:255',
                'distinct', // SKU phải là duy nhất trong request này
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
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'required_if:type,variable|nullable|integer|min:0',
            'variants.*.attributes' => 'required_if:type,variable|array|min:1',
            'variants.*.attributes.*' => 'required|exists:attribute_values,id',
            'variants.*.is_default' => 'required|in:true,false',

            // --- Quy tắc cho SEO & khác ---
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

            // Sản phẩm đơn giản
            'simple_sku.required_if' => 'SKU của sản phẩm không được để trống.',
            'simple_sku.unique' => 'SKU này đã tồn tại.',
            'simple_price.required_if' => 'Giá của sản phẩm không được để trống.',
            'simple_sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'simple_stock_quantity.required_if' => 'Số lượng tồn kho không được để trống.',
            
            // SỬA ĐỔI: Thông báo lỗi cho ID ảnh
            'cover_image_id.required_if' => 'Sản phẩm đơn giản phải có ít nhất một hình ảnh (ảnh bìa).',
            'cover_image_id.exists' => 'Ảnh bìa được chọn không hợp lệ.',
            'gallery_images.*.exists' => 'Một ảnh trong thư viện không hợp lệ.',

            // Sản phẩm có biến thể
            'variants.required_if' => 'Cần có ít nhất một biến thể.',
            'variants.min' => 'Cần có ít nhất một biến thể.',
            'variants.*.sku.required_if' => 'SKU của biến thể không được để trống.',
            'variants.*.sku.distinct' => 'SKU của các biến thể không được trùng nhau.',
            'variants.*.price.required_if' => 'Giá của biến thể không được để trống.',
            'variants.*.stock_quantity.required_if' => 'Tồn kho của biến thể không được để trống.',
            'variants.*.attributes.required_if' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
            'variants.*.attributes.min' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
            
            // SỬA ĐỔI: Thông báo lỗi cho ID ảnh của biến thể
            'variants.*.image_ids.*.exists' => 'Một ảnh của biến thể không hợp lệ.',
            'variants.*.primary_image_id.exists' => 'Ảnh chính của biến thể không hợp lệ.',
        ];
    }
}
