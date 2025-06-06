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
        return true; // Luôn cho phép, bạn có thể thêm logic phân quyền tại đây
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Lấy model Product từ route, sẽ có giá trị khi update và null khi store
        $product = $this->route('product');
        $productId = $product ? $product->id : null;

        // --- Logic để lấy ID của biến thể mặc định cho sản phẩm đơn giản (khi update) ---
        $defaultVariantId = null;
        if ($product && $this->input('type') === 'simple') {
            // Dùng getOriginal() để đảm bảo lấy đúng type từ DB, phòng trường hợp type bị thay đổi trong request
            $originalType = $product->getOriginal('type');
            if ($originalType === 'simple') {
                $defaultVariant = $product->variants()->where('is_default', true)->first();
                $defaultVariantId = $defaultVariant ? $defaultVariant->id : null;
            }
        }

        return [
            // --- Quy tắc cho thông tin chung của sản phẩm ---
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'category_id' => 'required|exists:categories,id',
            'short_description' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'status' => 'required|in:published,draft,pending_review,trashed',
            'is_featured' => 'nullable|boolean',
            'type' => 'required|in:simple,variable',

            // --- Quy tắc cho hình ảnh ---
            // Ảnh bìa chỉ bắt buộc khi tạo mới (store), khi cập nhật (update) có thể không cần upload lại
            'cover_image_file' => [$productId ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'gallery_image_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'deleted_gallery_image_ids' => 'nullable|array',
            'deleted_gallery_image_ids.*' => 'nullable|integer|exists:uploaded_files,id',

            // --- Quy tắc cho sản phẩm đơn giản (Simple Product) ---
            'simple_sku' => ['required_if:type,simple', 'nullable', 'string', 'max:255', Rule::unique('product_variants', 'sku')->ignore($defaultVariantId)],
            'simple_price' => 'required_if:type,simple|nullable|numeric|min:0',
            'simple_sale_price' => 'nullable|numeric|min:0|lt:simple_price',
            'simple_stock_quantity' => 'required_if:type,simple|nullable|integer|min:0',

            // --- Quy tắc cho sản phẩm có biến thể (Variable Product) ---
            'variants' => 'required_if:type,variable|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.sku' => [
                'required_if:type,variable',
                'string',
                'max:255',
                'distinct', // SKU không được trùng lặp trong chính request gửi lên
                function ($attribute, $value, $fail) {
                    // $attribute sẽ là 'variants.0.sku', 'variants.1.sku',...
                    $index = explode('.', $attribute)[1];
                    $variantIdBeingChecked = $this->input("variants.{$index}.id"); // Lấy ID của biến thể đang check (nếu có)

                    $query = DB::table('product_variants')->where('sku', $value);

                    // Khi cập nhật một biến thể đã có, ta phải loại trừ chính nó ra khỏi việc kiểm tra unique
                    if ($variantIdBeingChecked) {
                        $query->where('id', '!=', $variantIdBeingChecked);
                    }

                    if ($query->exists()) {
                        $fail("SKU '{$value}' đã tồn tại trong hệ thống.");
                    }
                }
            ],
            'variants.*.price' => 'required_if:type,variable|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'required_if:type,variable|integer|min:0',
            'variants.*.attributes' => 'required_if:type,variable|array|min:1',
            'variants.*.attributes.*' => 'required|exists:attribute_values,id',

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
            'cover_image_file.required' => 'Ảnh bìa là bắt buộc.',
            'cover_image_file.image' => 'File tải lên cho ảnh bìa phải là hình ảnh.',
            'type.required' => 'Vui lòng chọn loại sản phẩm.',
            
            'simple_sku.required_if' => 'SKU là bắt buộc với sản phẩm đơn giản.',
            'simple_sku.unique' => 'SKU này đã tồn tại.',
            'simple_price.required_if' => 'Giá là bắt buộc với sản phẩm đơn giản.',
            'simple_sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            
            'variants.required_if' => 'Cần có ít nhất một biến thể cho sản phẩm có biến thể.',
            'variants.*.sku.required_if' => 'SKU của biến thể không được để trống.',
            'variants.*.sku.distinct' => 'SKU của các biến thể gửi lên không được trùng nhau.',
            'variants.*.price.required_if' => 'Giá của biến thể không được để trống.',
            'variants.*.attributes.required_if' => 'Mỗi biến thể phải có ít nhất một thuộc tính.',
        ];
    }
}