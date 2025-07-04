<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductVariant;
use App\Models\UploadedFile;
use App\Services\FileService;
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
        $query = Product::with([
            'category',
            'variants' => function ($q) {
                $q->orderBy('is_default', 'desc')->orderBy('created_at', 'asc');
            },
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
        $this->authorize('create', Product::class); // Thêm authorize nếu cần
        $allCategories = Category::where('status', 'active')->get();
        $categories = $this->formatCategoriesForSelect($allCategories);
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();
        // Chuẩn bị dữ liệu để khôi phục ảnh sau khi validation thất bại
        $old_images_data = [];
        $all_image_ids = [];
        // Lấy ID ảnh từ sản phẩm đơn giản
        $simple_gallery_ids = old('gallery_images', []);
        $simple_cover_id = old('cover_image_id');
        if ($simple_cover_id) {
            $all_image_ids[] = $simple_cover_id;
        }
        if (!empty($simple_gallery_ids)) {
            $all_image_ids = array_merge($all_image_ids, $simple_gallery_ids);
        }

        // Lấy ID ảnh từ các biến thể
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

        // Truy vấn DB một lần duy nhất để lấy thông tin tất cả các ảnh cần thiết
        if (!empty($all_image_ids)) {
            // Loại bỏ các ID trùng lặp và rỗng
            $unique_ids = array_unique(array_filter($all_image_ids));
            if (!empty($unique_ids)) {
                $images = UploadedFile::whereIn('id', $unique_ids)->get();
                // Chuyển đổi thành một map với key là ID để dễ dàng truy cập trong JS
                $old_images_data = $images->keyBy('id')->map(function ($image) {
                    // Đảm bảo có thuộc tính 'url'. Model của bạn nên có getUrlAttribute()
                    return [
                        'id' => $image->id,
                        'url' => $image->url,
                        'alt_text' => $image->alt_text
                    ];
                })->all();
            }
        }
        // --- KẾT THÚC PHẦN CODE MỚI ---

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
            // 1. Create product with basic info, excluding image and variant data handled separately
            $productData = $request->except([
                'cover_image_id',
                'gallery_images',
                'variants',
                'simple_sku',
                'simple_price',
                'simple_sale_price',
                'simple_stock_quantity',
                'simple_sale_price_starts_at',
                'simple_sale_price_ends_at',
                'simple_weight',
                'simple_dimensions_length',
                'simple_dimensions_width',
                'simple_dimensions_height'
            ]);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['created_by'] = Auth::id();

            $product = Product::create($productData);

            // 2. Handle simple product logic
            if ($request->input('type') === 'simple') {
                // **NEW**: Link pre-uploaded images
                // The main gallery_images array holds all image IDs for the simple product.
                // The cover_image_id specifies which one is the cover.
                if ($request->has('gallery_images') && is_array($request->input('gallery_images'))) {
                    $this->syncProductImages($product, $request->input('cover_image_id'), $request->input('gallery_images'));
                }

                // Create a single variant for the simple product
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $request->input('simple_sku'),
                    'price' => $request->input('simple_price'),
                    'sale_price' => $request->input('simple_sale_price'),
                    'sale_price_starts_at' => $request->input('simple_sale_price_starts_at'),
                    'sale_price_ends_at' => $request->input('simple_sale_price_ends_at'),
                    'stock_quantity' => $request->input('simple_stock_quantity'),
                    'weight' => $request->input('simple_weight'),
                    'dimensions_length' => $request->input('simple_dimensions_length'),
                    'dimensions_width' => $request->input('simple_dimensions_width'),
                    'dimensions_height' => $request->input('simple_dimensions_height'),
                    'is_default' => true,
                    'status' => 'active',
                ]);
            }
            // 3. Handle variable product logic
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
                        'stock_quantity' => $variantData['stock_quantity'],
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

                    // **NEW**: Link pre-uploaded images to the variant
                    if (isset($variantData['image_ids']) && is_array($variantData['image_ids'])) {
                        UploadedFile::whereIn('id', $variantData['image_ids'])->update([
                            'attachable_id' => $variant->id,
                            'attachable_type' => ProductVariant::class,
                            'type' => 'variant_image',
                        ]);
                    }
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

        $product->load([
            'category',
            'variants' => fn($q) => $q->orderBy('is_default', 'desc'),
            'variants.attributeValues.attribute',
            'variants.images',      // Sửa ở đây: không có .file
            'coverImage',           // Sửa ở đây: không có .file
            'galleryImages'         // Sửa ở đây: không có .file
        ]);

        $allCategories = Category::where('status', 'active')->get();
        $categories = $this->formatCategoriesForSelect($allCategories);
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'attributes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $originalType = $product->type;
            $newType = $request->input('type');

            // 1. Update basic product info
            $productData = $request->except(['_token', '_method', 'cover_image_id', 'gallery_images', 'variants', 'simple_sku', 'simple_price', 'simple_sale_price', 'simple_stock_quantity', 'simple_sale_price_starts_at', 'simple_sale_price_ends_at', 'simple_weight', 'simple_dimensions_length', 'simple_dimensions_width', 'simple_dimensions_height']);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['updated_by'] = Auth::id();
            $product->update($productData);

            // 2. Handle product type switching logic
            $typeChanged = ($originalType !== $newType);

            if ($typeChanged) {
                // When switching, we clean up the old state completely.
                // Detach all images from variants and the product itself to avoid conflicts
                UploadedFile::where('attachable_id', $product->id)
                    ->where('attachable_type', Product::class)
                    ->update(['attachable_id' => null, 'attachable_type' => null, 'type' => null, 'order' => null]);

                $variantIds = $product->variants()->pluck('id');
                if ($variantIds->isNotEmpty()) {
                    UploadedFile::where('attachable_type', ProductVariant::class)
                        ->whereIn('attachable_id', $variantIds)
                        ->update(['attachable_id' => null, 'attachable_type' => null]);
                }

                // Delete all old variants before creating new ones based on the new type
                $product->variants()->delete();
            }

            // 3. Create/Update variants and images based on the NEW type
            if ($newType === 'simple') {
                // This logic runs for both "update existing simple" and "switch to simple"
                $variantData = [
                    'sku' => $request->input('simple_sku'),
                    'price' => $request->input('simple_price'),
                    'sale_price' => $request->input('simple_sale_price'),
                    'sale_price_starts_at' => $request->input('simple_sale_price_starts_at'),
                    'sale_price_ends_at' => $request->input('simple_sale_price_ends_at'),
                    'stock_quantity' => $request->input('simple_stock_quantity'),
                    'weight' => $request->input('simple_weight'),
                    'dimensions_length' => $request->input('simple_dimensions_length'),
                    'dimensions_width' => $request->input('simple_dimensions_width'),
                    'dimensions_height' => $request->input('simple_dimensions_height'),
                    'is_default' => true,
                    'status' => 'active',
                ];

                // Use updateOrCreate to handle both existing and new simple variants after a switch
                $product->variants()->updateOrCreate(['product_id' => $product->id], $variantData);

                // Sync images for the simple product
                $this->syncProductImages($product, $request->input('cover_image_id'), $request->input('gallery_images', []));

            } elseif ($newType === 'variable' && $request->has('variants')) {
                // This logic runs for both "update existing variable" and "switch to variable"
                $this->syncProductVariants($product, $request->input('variants'), $request);
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
    private function syncProductVariants(Product $product, array $variantsData, Request $request)
    {
        $existingVariantIds = $product->variants()->pluck('id')->toArray();
        $submittedVariantIds = [];
        $defaultVariantCandidateId = null;

        foreach ($variantsData as $key => $variantData) {
            $payload = [
                'sku' => $variantData['sku'],
                'price' => $variantData['price'],
                'sale_price' => $variantData['sale_price'] ?? null,
                'sale_price_starts_at' => $variantData['sale_price_starts_at'] ?? null,
                'sale_price_ends_at' => $variantData['sale_price_ends_at'] ?? null,
                'stock_quantity' => $variantData['stock_quantity'],
                'weight' => $variantData['weight'] ?? null,
                'dimensions_length' => $variantData['dimensions_length'] ?? null,
                'dimensions_width' => $variantData['dimensions_width'] ?? null,
                'dimensions_height' => $variantData['dimensions_height'] ?? null,
                'primary_image_id' => $variantData['primary_image_id'] ?? null,
                'status' => 'active',
            ];

            // Update or Create the variant
            $variant = $product->variants()->updateOrCreate(['id' => $variantData['id'] ?? null], $payload);

            $submittedVariantIds[] = $variant->id;

            if (!empty($variantData['attributes'])) {
                $variant->attributeValues()->sync(array_values($variantData['attributes']));
            }

            $this->syncVariantImages($variant, $variantData['image_ids'] ?? []);

            if ($request->input("variant_is_default_radio_group") == $key) {
                $defaultVariantCandidateId = $variant->id;
            }
        }

        $variantsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
        if (!empty($variantsToDelete)) {
            UploadedFile::where('attachable_type', ProductVariant::class)
                ->whereIn('attachable_id', $variantsToDelete)
                ->update(['attachable_id' => null, 'attachable_type' => null]);
            ProductVariant::destroy($variantsToDelete);
        }

        $product->refresh();
        if ($product->variants->isNotEmpty()) {
            $product->variants->each(fn($v) => $v->update(['is_default' => false]));
            $defaultVariant = $product->variants->find($defaultVariantCandidateId) ?? $product->variants->first();
            $defaultVariant->update(['is_default' => true]);
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

}
