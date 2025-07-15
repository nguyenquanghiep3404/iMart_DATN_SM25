<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductVariant;
use App\Models\UploadedFile;
use App\Models\SpecificationGroup;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\OrderItem;

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

        // Lọc ra các danh mục con của $parentId
        $children = $categories->where('parent_id', $parentId);

        foreach ($children as $category) {
            // Thêm tiền tố vào tên để tạo hiệu ứng phân cấp
            $category->name = $prefix . $category->name;

            // Thêm danh mục đã định dạng vào mảng kết quả
            $formattedCategories[] = $category;

            // Gọi đệ quy để lấy các danh mục con của danh mục này
            // và nối chúng vào mảng kết quả
            $formattedCategories = array_merge(
                $formattedCategories,
                $this->formatCategoriesForSelect($categories, $category->id, $prefix . '-- ')
            );
        }

        return $formattedCategories;
    }


    /**
     * Show the form for creating a new resource.
     * MODIFIED: This method now fetches data for pre-selected images if validation fails.
     */
    public function create()
    {
        $allCategories = Category::where('status', 'active')->get();
        $categories = $this->formatCategoriesForSelect($allCategories);
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();

        // Prepare data to restore images if validation fails
        $old_images_data = [];
        $all_image_ids = [];

        // Get image IDs from simple product fields
        $simple_gallery_ids = old('gallery_images', []);
        $simple_cover_id = old('cover_image_id');
        if ($simple_cover_id) {
            $all_image_ids[] = $simple_cover_id;
        }
        if (!empty($simple_gallery_ids)) {
            $all_image_ids = array_merge($all_image_ids, $simple_gallery_ids);
        }

        // Get image IDs from variant fields
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

        // Query the DB once to get info for all required images
        if (!empty($all_image_ids)) {
            $unique_ids = array_unique(array_filter($all_image_ids));
            if (!empty($unique_ids)) {
                $images = UploadedFile::whereIn('id', $unique_ids)->get();
                // Map to an ID-keyed array for easy JS access
                $old_images_data = $images->keyBy('id')->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->url, // Assumes getUrlAttribute exists on the model
                        'alt_text' => $image->alt_text
                    ];
                })->all();
            }
        }

        return view('admin.products.create', compact('categories', 'attributes', 'old_images_data'));
    }

    /**
     * Store a newly created resource in storage.
     * MODIFIED: This method now works with pre-uploaded image IDs.
     */
    public function store(ProductRequest $request)
    {
        DB::beginTransaction();
        try {
            $productData = $request->except([
                'cover_image_id',
                'gallery_images',
                'variants',
                'simple_sku',
                'simple_price',
                'simple_sale_price',
                'simple_inventories',
                'simple_sale_price_starts_at',
                'simple_sale_price_ends_at',
                'simple_weight',
                'simple_dimensions_length',
                'simple_dimensions_width',
                'simple_dimensions_height',
                'specifications'
            ]);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['created_by'] = Auth::id();

            $product = Product::create($productData);

            // Helper function to save specifications for a variant
            $saveSpecifications = function (ProductVariant $variant, ?array $specData) {
                if (empty($specData)) {
                    $variant->specifications()->detach(); // Clear old relations if no new data
                    return;
                }
                $syncData = [];
                foreach ($specData as $specId => $specValue) {
                    // Only save if the value is not empty
                    if (trim($specValue) !== '') {
                        $syncData[$specId] = ['value' => trim($specValue)];
                    }
                }
                $variant->specifications()->sync($syncData);
            };

            // Handle simple product logic
            if ($request->input('type') === 'simple') {
                if ($request->has('gallery_images') && is_array($request->input('gallery_images'))) {
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

                // Đồng bộ tồn kho chi tiết
                $this->syncVariantInventory($variant, $request->input('simple_inventories', []));
                $saveSpecifications($variant, $request->input('specifications', []));
            }
            // Handle variable product logic
            elseif ($request->input('type') === 'variable' && $request->has('variants')) {
                $defaultVariantKey = $request->input('variant_is_default_radio_group');

                foreach ($request->input('variants') as $key => $variantData) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'sale_price_starts_at' => $variantData['sale_price_starts_at'] ?? null,
                        'sale_price_ends_at' => $variantData['sale_price_ends_at'] ?? null,
                        'weight' => $variantData['weight'] ?? null,
                        'dimensions_length' => $variantData['dimensions_length'] ?? null,
                        'dimensions_width' => $variantData['dimensions_width'] ?? null,
                        'dimensions_height' => $variantData['dimensions_height'] ?? null,
                        'primary_image_id' => $variantData['primary_image_id'] ?? null,
                        'is_default' => ($defaultVariantKey == $key),
                        'status' => 'active',
                    ]);

                    if (!empty($variantData['attributes'])) {
                        $variant->attributeValues()->attach(array_values($variantData['attributes']));
                    }

                    if (isset($variantData['image_ids']) && is_array($variantData['image_ids'])) {
                        UploadedFile::whereIn('id', $variantData['image_ids'])->update([
                            'attachable_id' => $variant->id,
                            'attachable_type' => ProductVariant::class,
                            'type' => 'variant_image',
                        ]);
                    }
                    // Đồng bộ tồn kho chi tiết
                    $this->syncVariantInventory($variant, $request->input('simple_inventories', []));
                    $saveSpecifications($variant, $request->input('specifications', []));
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage() . ' tại dòng ' . $e->getLine() . ' trong file ' . $e->getFile());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi tạo sản phẩm. Vui lòng kiểm tra lại dữ liệu.');
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
            'variants' => fn($q) => $q->orderBy('is_default', 'desc'),
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

        return view('admin.products.edit', compact('product', 'categories', 'attributes', 'hasBeenSold'));
    }


    /**
     * Update the specified resource in storage.
     * MODIFIED: Handle saving of specifications.
     */
    public function update(ProductRequest $request, Product $product)
    {
        DB::beginTransaction();
        try {
            // Helper function to save specifications for a variant
            $saveSpecifications = function (ProductVariant $variant, ?array $specData) {
                if (is_null($specData)) {
                    return; // Do nothing if spec data is not provided
                }
                $syncData = [];
                foreach ($specData as $specId => $specValue) {
                    // Only save if the value is not empty or null
                    if (trim((string) $specValue) !== '') {
                        $syncData[$specId] = ['value' => trim($specValue)];
                    }
                }
                $variant->specifications()->sync($syncData);
            };

            $originalType = $product->type;
            $newType = $request->input('type');

            // 1. Update basic product info
            $productData = $request->except(['_token', '_method', 'cover_image_id', 'gallery_images', 'variants', 'simple_sku', 'simple_price', 'simple_sale_price', 'simple_inventories', 'simple_sale_price_starts_at', 'simple_sale_price_ends_at', 'simple_weight', 'simple_dimensions_length', 'simple_dimensions_width', 'simple_dimensions_height', 'specifications']);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['updated_by'] = Auth::id();
            $product->update($productData);

            $typeChanged = ($originalType !== $newType);

            if ($typeChanged) {
                // Clean up old state completely when switching types
                UploadedFile::where('attachable_id', $product->id)
                    ->where('attachable_type', Product::class)
                    ->update(['attachable_id' => null, 'attachable_type' => null, 'type' => null, 'order' => null]);

                $variantIds = $product->variants()->pluck('id');
                if ($variantIds->isNotEmpty()) {
                    UploadedFile::where('attachable_type', ProductVariant::class)
                        ->whereIn('attachable_id', $variantIds)
                        ->update(['attachable_id' => null, 'attachable_type' => null]);
                }
                // Detach specifications before deleting variants
                foreach ($product->variants as $variant) {
                    $variant->specifications()->detach();
                }
                $product->variants()->delete();
            }

            // 3. Create/Update variants based on the NEW type
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
                $variant = $product->variants()->updateOrCreate(['product_id' => $product->id], $variantData);
                $this->syncProductImages($product, $request->input('cover_image_id'), $request->input('gallery_images', []));

                // Save specifications for the simple product's variant
                $saveSpecifications($variant, $request->input('specifications', []));

            } elseif ($newType === 'variable') {
                if (!$request->has('variants') || empty($request->input('variants'))) {
                    return back()->withInput()->with('error', 'Sản phẩm có biến thể phải có ít nhất một biến thể.');
                }
                // Pass the helper function to syncProductVariants
                $this->syncProductVariants($product, $request->input('variants'), $request, $saveSpecifications);
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật sản phẩm: ' . $e->getMessage() . ' tại dòng ' . $e->getLine() . ' trong file ' . $e->getFile());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi cập nhật sản phẩm. Chi tiết: ' . $e->getMessage());
        }
    }

    /**
     * Sync cover and gallery images for a product.
     */
    private function syncProductImages(Product $product, $coverImageId, array $galleryImageIds)
    {
        // Detach all old images first
        UploadedFile::where('attachable_id', $product->id)
            ->where('attachable_type', Product::class)
            ->update(['attachable_id' => null, 'attachable_type' => null, 'type' => null, 'order' => null]);

        // Re-attach submitted images with correct types and order
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
     * Sync variants and their images.
     */
    /**
     * Đồng bộ các biến thể sản phẩm, bao gồm tồn kho, thuộc tính, ảnh và thông số kỹ thuật.
     *
     * @param Product $product Sản phẩm cha.
     * @param array $variantsData Dữ liệu các biến thể từ request.
     * @param Request $request Đối tượng request để lấy variant mặc định.
     * @param callable $saveSpecifications Một hàm closure để lưu thông số kỹ thuật.
     */
    private function syncProductVariants(Product $product, array $variantsData, Request $request, callable $saveSpecifications)
    {
        $existingVariantIds = $product->variants()->pluck('id')->toArray();
        $submittedVariantIds = [];
        $defaultVariantCandidateId = null;

        foreach ($variantsData as $key => $variantData) {
            // Tách dữ liệu tồn kho và thông số kỹ thuật ra khỏi payload chính
            $inventoriesData = $variantData['inventories'] ?? [];
            $specificationsData = $variantData['specifications'] ?? [];

            // Dữ liệu để cập nhật hoặc tạo mới cho bảng product_variants
            $payload = [
                'sku' => $variantData['sku'],
                'price' => $variantData['price'],
                'sale_price' => $variantData['sale_price'] ?? null,
                'sale_price_starts_at' => $variantData['sale_price_starts_at'] ?? null,
                'sale_price_ends_at' => $variantData['sale_price_ends_at'] ?? null,
                'weight' => $variantData['weight'] ?? null,
                'dimensions_length' => $variantData['dimensions_length'] ?? null,
                'dimensions_width' => $variantData['dimensions_width'] ?? null,
                'dimensions_height' => $variantData['dimensions_height'] ?? null,
                'primary_image_id' => $variantData['primary_image_id'] ?? null,
                'status' => 'active',
            ];

            // Cập nhật hoặc Tạo mới biến thể
            $variant = $product->variants()->updateOrCreate(
                ['id' => $variantData['id'] ?? null],
                $payload
            );

            $submittedVariantIds[] = $variant->id;

            // 1. Đồng bộ tồn kho chi tiết bằng hàm helper
            $this->syncVariantInventory($variant, $inventoriesData);

            // 2. Đồng bộ giá trị thuộc tính
            if (!empty($variantData['attributes'])) {
                $variant->attributeValues()->sync(array_values($variantData['attributes']));
            } else {
                $variant->attributeValues()->detach(); // Xóa thuộc tính nếu không có gì được gửi lên
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
    /**
     * Sync images for a specific variant.
     */
    private function syncVariantImages(ProductVariant $variant, array $imageIds)
    {
        // Detach old images
        UploadedFile::where('attachable_id', $variant->id)
            ->where('attachable_type', ProductVariant::class)
            ->update(['attachable_id' => null, 'attachable_type' => null]);

        // Attach new images
        if (!empty($imageIds)) {
            UploadedFile::whereIn('id', $imageIds)->update([
                'attachable_id' => $variant->id,
                'attachable_type' => ProductVariant::class,
                'type' => 'variant_image'
            ]);
        }
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
        // $this->authorize('viewTrash', Product::class);

        $query = Product::onlyTrashed()->with([
            'category',
            'variants' => fn($q) => $q->orderBy('is_default', 'desc'),
            'variants.primaryImage',
            'coverImage',
            'deletedBy'
        ]);

        // Handle Search
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('variants', function ($variantQuery) use ($searchTerm) {
                        $variantQuery->where('sku', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Handle Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Handle Sorting
        $sortBy = $request->input('sort_by', 'deleted_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSortColumns = ['name', 'deleted_at'];

        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('deleted_at', 'desc');
        }

        $trashedProducts = $query->paginate(15)->withQueryString();

        // Fetch categories for the filter dropdown
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('admin.products.trash', compact('trashedProducts', 'categories'));
    }

    /**
     * Restore a product from the trash.
     */
    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        try {
            // Restore the product (clears deleted_at)
            $product->restore();

            // Revert status to 'draft' for review
            $product->status = 'draft';
            // $product->deleted_by = null; // Clear deleted_by info
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
        // $this->authorize('forceDelete', $product);

        $product->forceDelete();

        return redirect()->route('admin.products.trash')->with('success', 'Sản phẩm đã được xóa vĩnh viễn.');
    }
    public function getSpecificationsForCategory(Category $category)
    {
        try {
            // Eager load the necessary relationships
            $category->load('specificationGroups.specifications');

            // Return the data as JSON
            return response()->json($category->specificationGroups);

        } catch (\Exception $e) {
            Log::error("Error fetching specs for category ID {$category->id}: " . $e->getMessage());
            return response()->json(['error' => 'Could not load specifications.'], 500);
        }
    }
    private function syncVariantInventory(ProductVariant $variant, array $inventories)
    {
        if (empty($inventories)) {
            return; // Không có dữ liệu kho để xử lý
        }

        // Cập nhật hoặc tạo mới các bản ghi tồn kho từ dữ liệu được gửi lên
        foreach ($inventories as $type => $quantity) {
            $quantity = (int) $quantity;

            if ($quantity > 0) {
                // Sử dụng updateOrCreate để vừa cập nhật, vừa tạo mới nếu chưa có
                $variant->inventories()->updateOrCreate(
                    [
                        'inventory_type' => $type,
                    ],
                    [
                        'quantity' => $quantity,
                    ]
                );
            } else {
                // Nếu số lượng là 0, xóa bản ghi tồn kho đó đi
                $variant->inventories()->where('inventory_type', $type)->delete();
            }
        }
    }

}