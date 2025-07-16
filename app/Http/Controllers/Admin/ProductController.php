<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductVariant;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Hàm này đã đúng, không cần sửa.
        $query = Product::with([
            'category',
            'variants' => function ($q) {
                $q->orderBy('is_default', 'desc')->orderBy('created_at', 'asc');
            },
            'variants.inventories',
            'variants.primaryImage',
            'coverImage'
        ]);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('variants', function ($variantQuery) use ($searchTerm) {
                        $variantQuery->where('sku', 'like', "%{$searchTerm}%");
                    });
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSortColumns = ['name', 'created_at', 'price'];

        if (in_array($sortBy, $allowedSortColumns)) {
            if ($sortBy === 'price') {
                $query->leftJoin('product_variants as pv_sort_index', function ($join) {
                    $join->on('products.id', '=', 'pv_sort_index.product_id')
                        ->whereRaw('pv_sort_index.id = (SELECT id FROM product_variants WHERE product_id = products.id ORDER BY is_default DESC, created_at ASC LIMIT 1)');
                })
                    ->orderBy('pv_sort_index.price', $sortDir)
                    ->select('products.*');
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
        } else {
            $query->latest();
        }

        $products = $query->distinct()->paginate(15)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    private function formatCategoriesForSelect($categories, $parentId = null, $prefix = '')
    {
        $formattedCategories = [];
        $children = $categories->where('parent_id', $parentId);

        foreach ($children as $category) {
            $category->name = $prefix . $category->name;
            $formattedCategories[] = $category;
            $formattedCategories = array_merge(
                $formattedCategories,
                $this->formatCategoriesForSelect($categories, $category->id, $prefix . '-- ')
            );
        }
        return $formattedCategories;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allCategories = Category::where('status', 'active')->get();
        $categories = $this->formatCategoriesForSelect($allCategories);
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();

        // Prepare data to restore images if validation fails
        $old_images_data = [];
        $all_image_ids = [];

        $simple_gallery_ids = old('gallery_images', []);
        $simple_cover_id = old('cover_image_id');
        if ($simple_cover_id) {
            $all_image_ids[] = $simple_cover_id;
        }
        if (!empty($simple_gallery_ids)) {
            $all_image_ids = array_merge($all_image_ids, $simple_gallery_ids);
        }

        if (old('variants')) {
            foreach (old('variants') as $oldVariant) {
                $variant_image_ids = $oldVariant['image_ids'] ?? [];
                $variant_primary_id = $oldVariant['primary_image_id'] ?? null;
                if ($variant_primary_id) {
                    $all_image_ids[] = $variant_primary_id;
                }
                if (!empty($variant_image_ids)) {
                    $all_image_ids = array_merge($all_image_ids, $variant_image_ids);
                }
            }
        }

        if (!empty($all_image_ids)) {
            $unique_ids = array_unique(array_filter($all_image_ids));
            if (!empty($unique_ids)) {
                $images = UploadedFile::whereIn('id', $unique_ids)->get();
                $old_images_data = $images->keyBy('id')->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->url,
                        'alt_text' => $image->alt_text
                    ];
                })->all();
            }
        }

        return view('admin.products.create', compact('categories', 'attributes', 'old_images_data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        DB::beginTransaction();
        try {
            // Dữ liệu cho bảng 'products'
            $productData = $request->except([
                '_token', 'cover_image_id', 'gallery_images', 'variants',
                'simple_sku', 'simple_price', 'simple_sale_price', 'simple_inventories',
                'simple_sale_price_starts_at', 'simple_sale_price_ends_at', 'simple_weight',
                'simple_dimensions_length', 'simple_dimensions_width', 'simple_dimensions_height',
                'specifications'
            ]);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['created_by'] = Auth::id();

            $product = Product::create($productData);

            // Helper function để lưu thông số kỹ thuật
            $saveSpecifications = function (ProductVariant $variant, ?array $specData) {
                if (empty($specData)) {
                    $variant->specifications()->detach();
                    return;
                }
                $syncData = [];
                foreach ($specData as $specId => $specValue) {
                    if (trim($specValue) !== '') {
                        $syncData[$specId] = ['value' => trim($specValue)];
                    }
                }
                $variant->specifications()->sync($syncData);
            };

            // Xử lý sản phẩm đơn giản
            if ($request->input('type') === 'simple') {
                if ($request->has('gallery_images')) {
                    $this->syncProductImages($product, $request->input('cover_image_id'), $request->input('gallery_images'));
                }

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $request->input('simple_sku'),
                    'price' => $request->input('simple_price'),
                    'sale_price' => $request->input('simple_sale_price'),
                    'sale_price_starts_at' => $request->input('simple_sale_price_starts_at'),
                    'sale_price_ends_at' => $request->input('simple_sale_price_ends_at'),
                    'weight' => $request->input('simple_weight'),
                    'dimensions_length' => $request->input('simple_dimensions_length'),
                    'dimensions_width' => $request->input('simple_dimensions_width'),
                    'dimensions_height' => $request->input('simple_dimensions_height'),
                    'is_default' => true,
                    'status' => 'active',
                ]);

                // Đồng bộ tồn kho chi tiết và thông số kỹ thuật
                $this->syncVariantInventory($variant, $request->input('simple_inventories', []));
                $saveSpecifications($variant, $request->input('specifications', []));
            }
            // Xử lý sản phẩm có biến thể
            elseif ($request->input('type') === 'variable' && $request->has('variants')) {
                // Sử dụng hàm helper để đồng bộ tất cả các biến thể
                $this->syncProductVariants($product, $request->input('variants'), $request, $saveSpecifications);
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage() . ' tại dòng ' . $e->getLine() . ' trong file ' . $e->getFile());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi tạo sản phẩm.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('category', 'variants.attributeValues.attribute', 'coverImage', 'galleryImages');
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        $hasBeenSold = $product->variants()->whereHas('orderItems')->exists();

        $product->load([
            'category',
            'variants' => fn ($q) => $q->orderBy('is_default', 'desc')->orderBy('created_at', 'asc'),
            'variants.attributeValues.attribute',
            'variants.images',
            'variants.specifications',
            'variants.inventories',
            'coverImage',
            'galleryImages'
        ]);

        $allCategories = Category::where('status', 'active')->get();
        $categories = $this->formatCategoriesForSelect($allCategories);
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();
        // START OF CHANGES: Thêm logic phục hồi ảnh từ old()
    $old_images_data = [];
    if (session()->hasOldInput()) {
        $all_image_ids = [];

        // Lấy ảnh từ sản phẩm đơn giản (nếu có)
        $simple_gallery_ids = old('gallery_images', []);
        $simple_cover_id = old('cover_image_id');
        if ($simple_cover_id) {
            $all_image_ids[] = $simple_cover_id;
        }
        if (!empty($simple_gallery_ids)) {
            $all_image_ids = array_merge($all_image_ids, $simple_gallery_ids);
        }

        // Lấy ảnh từ các biến thể (nếu có)
        if (old('variants')) {
            foreach (old('variants') as $oldVariant) {
                $variant_image_ids = $oldVariant['image_ids'] ?? [];
                $variant_primary_id = $oldVariant['primary_image_id'] ?? null;
                if ($variant_primary_id) {
                    $all_image_ids[] = $variant_primary_id;
                }
                if (!empty($variant_image_ids)) {
                    $all_image_ids = array_merge($all_image_ids, $variant_image_ids);
                }
            }
        }

        // Truy vấn CSDL để lấy thông tin chi tiết của các ảnh
        if (!empty($all_image_ids)) {
            $unique_ids = array_unique(array_filter($all_image_ids));
            if (!empty($unique_ids)) {
                $images = UploadedFile::whereIn('id', $unique_ids)->get();
                $old_images_data = $images->keyBy('id')->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->url,
                        'alt_text' => $image->alt_text
                    ];
                })->all();
            }
        }
    }
         return view('admin.products.edit', compact('product', 'categories', 'attributes', 'hasBeenSold', 'old_images_data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $saveSpecifications = function (ProductVariant $variant, ?array $specData) {
                if (is_null($specData)) return;
                $syncData = [];
                foreach ($specData as $specId => $specValue) {
                    if (trim((string) $specValue) !== '') {
                        $syncData[$specId] = ['value' => trim($specValue)];
                    }
                }
                $variant->specifications()->sync($syncData);
            };

            $originalType = $product->type;
            $newType = $request->input('type');

            // Dữ liệu cho bảng 'products'
            $productData = $request->except([
                '_token', '_method', 'cover_image_id', 'gallery_images', 'variants', 'specifications',
                'simple_sku', 'simple_price', 'simple_sale_price', 'simple_inventories',
                'simple_sale_price_starts_at', 'simple_sale_price_ends_at', 'simple_weight',
                'simple_dimensions_length', 'simple_dimensions_width', 'simple_dimensions_height',
            ]);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['updated_by'] = Auth::id();
            $product->update($productData);

            // Nếu thay đổi loại sản phẩm, dọn dẹp dữ liệu cũ
            if ($originalType !== $newType) {
                // Gỡ liên kết ảnh của sản phẩm (nếu có)
                UploadedFile::where('attachable_id', $product->id)
                    ->where('attachable_type', Product::class)
                    ->update(['attachable_id' => null, 'attachable_type' => null, 'type' => null, 'order' => null]);
                
                // Dọn dẹp các biến thể cũ và các mối quan hệ của chúng
                $variantIds = $product->variants()->pluck('id');
                if ($variantIds->isNotEmpty()) {
                    UploadedFile::where('attachable_type', ProductVariant::class)
                        ->whereIn('attachable_id', $variantIds)
                        ->update(['attachable_id' => null, 'attachable_type' => null]);
                    
                    foreach ($product->variants as $variant) {
                        $variant->specifications()->detach();
                        $variant->attributeValues()->detach();
                        $variant->inventories()->delete();
                    }
                    $product->variants()->delete();
                }
            }

            // Xử lý dựa trên loại sản phẩm mới
            if ($newType === 'simple') {
                $variantData = [
                    'sku' => $request->input('simple_sku'),
                    'price' => $request->input('simple_price'),
                    'sale_price' => $request->input('simple_sale_price'),
                    'sale_price_starts_at' => $request->input('simple_sale_price_starts_at'),
                    'sale_price_ends_at' => $request->input('simple_sale_price_ends_at'),
                    'weight' => $request->input('simple_weight'),
                    'dimensions_length' => $request->input('simple_dimensions_length'),
                    'dimensions_width' => $request->input('simple_dimensions_width'),
                    'dimensions_height' => $request->input('simple_dimensions_height'),
                    'is_default' => true,
                    'status' => 'active',
                ];
                // Chỉ có 1 biến thể cho sản phẩm đơn giản, nên ta có thể dùng updateOrCreate
                $variant = $product->variants()->updateOrCreate(['product_id' => $product->id], $variantData);

                // SỬA LỖI: Gọi hàm đồng bộ tồn kho cho sản phẩm đơn giản
                $this->syncVariantInventory($variant, $request->input('simple_inventories', []));

                $this->syncProductImages($product, $request->input('cover_image_id'), $request->input('gallery_images', []));
                $saveSpecifications($variant, $request->input('specifications', []));
            } elseif ($newType === 'variable') {
                if (!$request->has('variants') || empty($request->input('variants'))) {
                    return back()->withInput()->with('error', 'Sản phẩm có biến thể phải có ít nhất một biến thể.');
                }
                $this->syncProductVariants($product, $request->input('variants'), $request, $saveSpecifications);
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật sản phẩm: ' . $e->getMessage() . ' tại dòng ' . $e->getLine() . ' trong file ' . $e->getFile());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi cập nhật sản phẩm.');
        }
    }

    private function syncProductImages(Product $product, $coverImageId, array $galleryImageIds)
    {
        UploadedFile::where('attachable_id', $product->id)
            ->where('attachable_type', Product::class)
            ->update(['attachable_id' => null, 'attachable_type' => null, 'type' => null, 'order' => null]);

        foreach ($galleryImageIds as $order => $imageId) {
            UploadedFile::where('id', $imageId)->update([
                'attachable_id' => $product->id,
                'attachable_type' => Product::class,
                'type' => ($imageId == $coverImageId) ? 'cover_image' : 'gallery_image',
                'order' => $order + 1
            ]);
        }
    }

    /**
     * Đồng bộ các biến thể sản phẩm, bao gồm tồn kho, thuộc tính, ảnh và thông số kỹ thuật.
     */
    private function syncProductVariants(Product $product, array $variantsData, Request $request, callable $saveSpecifications)
    {
        $existingVariantIds = $product->variants()->pluck('id')->toArray();
        $submittedVariantIds = [];
        $defaultVariantCandidateId = null;

        foreach ($variantsData as $key => $variantData) {
            // SỬA LỖI: Lấy đúng dữ liệu tồn kho và thông số từ MỖI biến thể
            $inventoriesData = $variantData['inventories'] ?? [];
            $specificationsData = $variantData['specifications'] ?? [];

            $payload = [
                'sku'                   => $variantData['sku'],
                'price'                 => $variantData['price'],
                'sale_price'            => $variantData['sale_price'] ?? null,
                'sale_price_starts_at'  => $variantData['sale_price_starts_at'] ?? null,
                'sale_price_ends_at'    => $variantData['sale_price_ends_at'] ?? null,
                'weight'                => $variantData['weight'] ?? null,
                'dimensions_length'     => $variantData['dimensions_length'] ?? null,
                'dimensions_width'      => $variantData['dimensions_width'] ?? null,
                'dimensions_height'     => $variantData['dimensions_height'] ?? null,
                'primary_image_id'      => $variantData['primary_image_id'] ?? null,
                'status'                => 'active',
            ];

            $variant = $product->variants()->updateOrCreate(
                ['id' => $variantData['id'] ?? null], // Điều kiện để tìm, nếu có 'id' thì update, không thì create
                $payload
            );

            $submittedVariantIds[] = $variant->id;

            // 1. Đồng bộ tồn kho chi tiết
            $this->syncVariantInventory($variant, $inventoriesData);

            // 2. Đồng bộ giá trị thuộc tính
            if (!empty($variantData['attributes'])) {
                $variant->attributeValues()->sync(array_values($variantData['attributes']));
            } else {
                $variant->attributeValues()->detach();
            }

            // 3. Đồng bộ ảnh của biến thể
            $this->syncVariantImages($variant, $variantData['image_ids'] ?? []);

            // 4. Đồng bộ thông số kỹ thuật
            $saveSpecifications($variant, $specificationsData);

            // 5. Xác định biến thể mặc định
            if ($request->input("variant_is_default_radio_group") == $key) {
                $defaultVariantCandidateId = $variant->id;
            }
        }

        // Xóa các biến thể không còn tồn tại trên form
        $variantsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
        if (!empty($variantsToDelete)) {
            // Dọn dẹp các file và quan hệ liên quan trước khi xóa
            UploadedFile::where('attachable_type', ProductVariant::class)
                ->whereIn('attachable_id', $variantsToDelete)
                ->update(['attachable_id' => null, 'attachable_type' => null]);
            
            DB::table('product_variant_specification')->whereIn('product_variant_id', $variantsToDelete)->delete();
            DB::table('product_variant_attribute_values')->whereIn('product_variant_id', $variantsToDelete)->delete();
            DB::table('product_inventories')->whereIn('product_variant_id', $variantsToDelete)->delete();

            ProductVariant::destroy($variantsToDelete);
        }

        // Cập nhật lại biến thể mặc định cho toàn bộ sản phẩm
        $product->refresh();
        if ($product->variants->isNotEmpty()) {
            $product->variants->each(fn($v) => $v->update(['is_default' => false]));
            $defaultVariant = $product->variants->find($defaultVariantCandidateId) ?? $product->variants->first();
            if ($defaultVariant) {
                $defaultVariant->update(['is_default' => true]);
            }
        }
    }

    private function syncVariantImages(ProductVariant $variant, array $imageIds)
    {
        UploadedFile::where('attachable_id', $variant->id)
            ->where('attachable_type', ProductVariant::class)
            ->update(['attachable_id' => null, 'attachable_type' => null]);

        if (!empty($imageIds)) {
            UploadedFile::whereIn('id', $imageIds)->update([
                'attachable_id' => $variant->id,
                'attachable_type' => ProductVariant::class,
                'type' => 'variant_image'
            ]);
        }
    }

    /**
     * HÀM HELPER: Đồng bộ tồn kho chi tiết cho một biến thể sản phẩm.
     */
    private function syncVariantInventory(ProductVariant $variant, array $inventories)
    {
        if (empty($inventories)) {
            // Nếu không có dữ liệu tồn kho gửi lên, có thể xóa hết tồn kho cũ
            $variant->inventories()->delete();
            return;
        }

        $submittedTypes = [];
        foreach ($inventories as $type => $quantity) {
            $quantity = (int) $quantity;
            $submittedTypes[] = $type;

            if ($quantity > 0) {
                $variant->inventories()->updateOrCreate(
                    ['inventory_type' => $type], // Điều kiện tìm kiếm
                    ['quantity' => $quantity]     // Dữ liệu để cập nhật hoặc tạo mới
                );
            } else {
                // Nếu số lượng là 0 hoặc không có, xóa bản ghi tồn kho đó đi
                $variant->inventories()->where('inventory_type', $type)->delete();
            }
        }
        
        // Xóa các loại tồn kho không được gửi lên (ví dụ: trước đó có hàng 'used', giờ không có)
        $variant->inventories()->whereNotIn('inventory_type', $submittedTypes)->delete();
    }

    /**
     * Move the product to trash (Soft Delete).
     */
    public function destroy(Product $product)
    {
        try {
            $product->status = 'trashed';
            $product->save();
            $product->delete();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được chuyển vào thùng rác.');
        } catch (\Exception $e) {
            Log::error("Lỗi khi chuyển sản phẩm vào thùng rác ID {$product->id}: " . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi xóa sản phẩm.');
        }
    }

    /**
     * Display a list of trashed products.
     */
    public function trash(Request $request)
    {
        $this->authorize('viewTrash', Product::class);
        $query = Product::onlyTrashed()->with([
            'category',
            'variants' => fn($q) => $q->orderBy('is_default', 'desc'),
            'variants.primaryImage',
            'coverImage',
            'deletedBy'
        ]);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('variants', function ($variantQuery) use ($searchTerm) {
                        $variantQuery->where('sku', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $sortBy = $request->input('sort_by', 'deleted_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);
        $trashedProducts = $query->paginate(15)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('admin.products.trash', compact('trashedProducts', 'categories'));
    }

    /**
     * Restore a product from the trash.
     */
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $product);
        try {
            $product->restore();
            $product->status = 'draft';
            $product->save();
            return redirect()->route('admin.products.trash')->with('success', 'Sản phẩm "' . $product->name . '" đã được khôi phục.');
        } catch (\Exception $e) {
            Log::error("Lỗi khi khôi phục sản phẩm ID {$id}: " . $e->getMessage());
            return back()->with('error', 'Không thể khôi phục sản phẩm.');
        }
    }

    /**
     * Permanently delete a product from the database.
     */
    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $product);
        $product->forceDelete();
        return redirect()->route('admin.products.trash')->with('success', 'Sản phẩm đã được xóa vĩnh viễn.');
    }

    public function getSpecificationsForCategory(Category $category)
    {
        try {
            $category->load('specificationGroups.specifications');
            return response()->json($category->specificationGroups);
        } catch (\Exception $e) {
            Log::error("Error fetching specs for category ID {$category->id}: " . $e->getMessage());
            return response()->json(['error' => 'Could not load specifications.'], 500);
        }
    }
}
