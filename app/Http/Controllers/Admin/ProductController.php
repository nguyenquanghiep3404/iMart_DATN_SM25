<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest; // Sử dụng ProductRequest
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductVariant;
use App\Models\UploadedFile;
use Illuminate\Http\Request; // Request gốc vẫn cần cho một số trường hợp
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;
    // Phân quyền
     public function __construct()
    {
        // Tự động phân quyền cho tất cả các phương thức CRUD
        $this->authorizeResource(Product::class, 'product');
    }
    /**
     * Hiển thị danh sách sản phẩm với bộ lọc và sắp xếp
     * (Logic từ controller gốc của bạn)
     */
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'variants' => function ($q) {
                $q->orderBy('is_default', 'desc')->orderBy('created_at', 'asc');
            },
            'coverImage',
            'galleryImages'
        ]);

        // --- Bắt đầu Logic Lọc (Từ controller gốc của bạn) ---
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
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('stock_status')) {
            if ($request->input('stock_status') === 'in_stock') {
                $query->whereHas('variants', function ($variantQuery) {
                    $variantQuery->where('stock_quantity', '>', 0);
                });
            } elseif ($request->input('stock_status') === 'out_of_stock') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('variants', function ($variantQuery) {
                        $variantQuery->where('stock_quantity', '>', 0);
                    })->orWhereDoesntHave('variants');
                });
            }
        }
        if ($request->filled('min_price')) {
            $query->whereHas('variants', function ($variantQuery) use ($request) {
                $variantQuery->where('price', '>=', $request->input('min_price'));
            });
        }
        if ($request->filled('max_price')) {
            $query->whereHas('variants', function ($variantQuery) use ($request) {
                $variantQuery->where('price', '<=', $request->input('max_price'));
            });
        }
        // --- Kết thúc Logic Lọc ---

        // --- Bắt đầu Logic Sắp xếp (Từ controller gốc của bạn) ---
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSortColumns = ['name', 'created_at', 'price'];

        if (in_array($sortBy, $allowedSortColumns)) {
            if ($sortBy === 'price') {
                $query->leftJoin('product_variants as pv_sort_index', function($join) {
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
        // --- Kết thúc Logic Sắp xếp ---

        $products = $query->distinct()->paginate(15)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Hiển thị form tạo sản phẩm mới
     * (Logic từ controller gốc của bạn)
     */
    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'attributes'));
    }

    /**
     * Lưu sản phẩm mới vào database
     * (Sử dụng ProductRequest, logic bên trong là logic gốc của bạn)
     */
    public function store(ProductRequest $request)
    {
        // Dữ liệu đã được validate bởi ProductRequest
        // Tuy nhiên, logic gốc của bạn sử dụng $request->only, $request->input trực tiếp nhiều hơn
        // $validatedData = $request->validated(); // Có thể không cần dùng trực tiếp nếu logic cũ không dùng

        DB::beginTransaction();
        try {
            // --- LOGIC LƯU SẢN PHẨM, ẢNH, BIẾN THỂ TỪ CONTROLLER GỐC CỦA BẠN ---
            $productData = $request->only([
                'name', 'category_id', 'short_description', 'description',
                'status', 'type', 'sku_prefix', // sku_prefix lấy trực tiếp từ request
                'meta_title', 'meta_description', 'tags', 'warranty_information'
            ]);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['created_by'] = Auth::id();
            $productData['updated_by'] = Auth::id();

            $product = Product::create($productData);

            // Xử lý Upload Ảnh Bìa (Logic gốc của bạn)
            if ($request->hasFile('cover_image_file')) {
                $file = $request->file('cover_image_file');
                $originalName = $file->getClientOriginalName();
                $filePath = 'products/' . $product->id . '/covers/' . date('Y') . '/' . date('m');
                $path = $file->store($filePath, 'public');
                $filename = basename($path);
                $product->coverImage()->create([
                    'path' => $path, 'filename' => $filename, 'original_name' => $originalName,
                    'mime_type' => $file->getClientMimeType(), 'size' => $file->getSize(),
                    'disk' => 'public', 'type' => 'cover_image', 'user_id' => Auth::id(),
                ]);
            }

            // Xử lý Upload Thư Viện Ảnh (Logic gốc của bạn)
            if ($request->hasFile('gallery_image_files')) {
                foreach ($request->file('gallery_image_files') as $key => $gImageFile) {
                    $originalName = $gImageFile->getClientOriginalName();
                    $filePath = 'products/' . $product->id . '/gallery/' . date('Y') . '/' . date('m');
                    $path = $gImageFile->store($filePath, 'public');
                    $filename = basename($path);
                    $product->galleryImages()->create([
                        'path' => $path, 'filename' => $filename, 'original_name' => $originalName,
                        'mime_type' => $gImageFile->getClientMimeType(), 'size' => $gImageFile->getSize(),
                        'disk' => 'public', 'type' => 'gallery_image', 'order' => $key + 1, 'user_id' => Auth::id(),
                    ]);
                }
            }

            // Xử lý Biến thể (Logic gốc của bạn)
            // ProductRequest đã validate 'type', 'simple_sku', 'variants', etc.
            // Logic gốc của bạn sử dụng $request->input() hoặc $request->property
            if ($request->input('type') === 'simple') {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $request->input('simple_sku'),
                    'price' => $request->input('simple_price'),
                    'sale_price' => $request->input('simple_sale_price'),
                    'stock_quantity' => $request->input('simple_stock_quantity'),
                    'is_default' => true,
                    'status' => 'active',
                ]);
            } elseif ($request->input('type') === 'variable' && $request->has('variants')) {
                $isFirstVariant = true;
                foreach ($request->input('variants') as $variantData) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'],
                        'is_default' => $isFirstVariant,
                        'status' => 'active',
                    ]);
                    $variant->attributeValues()->attach(array_values($variantData['attributes']));
                    $isFirstVariant = false;
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage() . ' tại dòng ' . $e->getLine() . ' trong file ' . $e->getFile());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi tạo sản phẩm. Chi tiết: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị chi tiết sản phẩm
     * (Logic từ controller gốc của bạn)
     */
    public function show(Product $product)
    {
        $product->load('category', 'variants.attributeValues.attribute', 'coverImage', 'galleryImages', 'createdBy', 'updatedBy');
        return view('admin.products.show', compact('product'));
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm
     * (Logic từ controller gốc của bạn)
     */
    public function edit(Product $product)
    {
        $product->load('variants.attributeValues', 'coverImage', 'galleryImages');
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();
        $formattedVariants = $product->variants->map(function ($variant) {
            return [
                'id' => $variant->id, 'sku' => $variant->sku, 'price' => $variant->price,
                'sale_price' => $variant->sale_price, 'stock_quantity' => $variant->stock_quantity,
                'is_default' => $variant->is_default,
                'attributes' => $variant->attributeValues->pluck('id', 'attribute_id')->toArray(),
            ];
        });
        return view('admin.products.edit', compact('product', 'categories', 'attributes', 'formattedVariants'));
    }

    /**
     * Cập nhật sản phẩm trong database
     * (Sử dụng ProductRequest, logic bên trong là logic gốc của bạn)
     */
    public function update(ProductRequest $request, Product $product)
    {
        // $validatedData = $request->validated(); // Có thể không cần dùng trực tiếp

        DB::beginTransaction();
        try {
            // --- LOGIC CẬP NHẬT SẢN PHẨM, ẢNH, BIẾN THỂ TỪ CONTROLLER GỐC CỦA BẠN ---
            $productData = $request->only([
                'name', 'category_id', 'short_description', 'description',
                'status', 'sku_prefix', // sku_prefix lấy trực tiếp, type không thay đổi ở đây
                'meta_title', 'meta_description', 'tags', 'warranty_information'
            ]);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['updated_by'] = Auth::id();
            $product->update($productData);

            // Xử lý Ảnh Bìa (Logic gốc của bạn)
            if ($request->hasFile('cover_image_file')) {
                if ($product->coverImage) {
                    Storage::disk('public')->delete($product->coverImage->path); // Xóa file vật lý
                    $product->coverImage->delete(); // Xóa record DB
                }
                $file = $request->file('cover_image_file');
                $originalName = $file->getClientOriginalName();
                $filePath = 'products/' . $product->id . '/covers/' . date('Y') . '/' . date('m');
                $path = $file->store($filePath, 'public'); $filename = basename($path);
                $product->coverImage()->create([
                    'path' => $path, 'filename' => $filename, 'original_name' => $originalName,
                    'mime_type' => $file->getClientMimeType(), 'size' => $file->getSize(),
                    'disk' => 'public', 'type' => 'cover_image', 'user_id' => Auth::id(),
                ]);
            }

            // Xử lý Xóa & Thêm Gallery (Logic gốc của bạn)
            if ($request->has('deleted_gallery_image_ids')) {
                $idsToDelete = $request->input('deleted_gallery_image_ids');
                if (is_array($idsToDelete)) {
                    UploadedFile::whereIn('id', $idsToDelete)
                        ->where('attachable_id', $product->id)->where('attachable_type', Product::class)
                        ->where('type', 'gallery_image')->get()->each(function($file) {
                            Storage::disk($file->disk)->delete($file->path);
                            $file->delete();
                        });
                }
            }
            if ($request->hasFile('gallery_image_files')) {
                $currentMaxOrder = $product->galleryImages()->max('order') ?? 0;
                foreach ($request->file('gallery_image_files') as $key => $gImageFile) {
                    $originalName = $gImageFile->getClientOriginalName();
                    $filePath = 'products/' . $product->id . '/gallery/' . date('Y') . '/' . date('m');
                    $path = $gImageFile->store($filePath, 'public'); $filename = basename($path);
                    $product->galleryImages()->create([
                        'path' => $path, 'filename' => $filename, 'original_name' => $originalName,
                        'mime_type' => $gImageFile->getClientMimeType(), 'size' => $gImageFile->getSize(),
                        'disk' => 'public', 'type' => 'gallery_image', 'order' => $currentMaxOrder + $key + 1, 'user_id' => Auth::id(),
                    ]);
                }
            }

            // Xử lý Biến thể (Logic gốc của bạn)
            // ProductRequest đã validate các trường cần thiết
            // Type sản phẩm được giả định không thay đổi khi update trong logic gốc này
            if ($product->type === 'simple') {
                $defaultVariant = $product->variants()->where('is_default', true)->first();
                if ($defaultVariant) {
                    $defaultVariant->update([
                        'sku' => $request->input('simple_sku'),
                        'price' => $request->input('simple_price'),
                        'sale_price' => $request->input('simple_sale_price'),
                        'stock_quantity' => $request->input('simple_stock_quantity'),
                    ]);
                } else {
                    // Logic gốc của bạn không có phần else để tạo mới ở đây nếu sản phẩm simple không có variant
                }
                // Logic chuyển từ variable sang simple (từ controller gốc của bạn)
                if ($product->wasRecentlyCreated === false && $request->input('type') === 'simple' && $product->getOriginal('type') === 'variable') {
                    $product->variants()->where('is_default', false)->delete();
                }
            } elseif ($product->type === 'variable' && $request->has('variants')) {
                $existingVariantIds = $product->variants->pluck('id')->toArray();
                $submittedVariantIds = [];
                $defaultVariantCandidateId = null;

                foreach ($request->input('variants') as $key => $variantData) {
                    $variantAttributes = array_values($variantData['attributes']);
                    $isDefaultRequest = $request->input("variants.{$key}.is_default", false);

                    $variantPayload = [
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'],
                        'status' => $request->input("variants.{$key}.status", 'active'), // Lấy status từ request gốc
                    ];

                    if (isset($variantData['id']) && !empty($variantData['id'])) {
                        $variant = ProductVariant::find($variantData['id']);
                        if ($variant && $variant->product_id === $product->id) {
                            $variant->update($variantPayload);
                            $variant->attributeValues()->sync($variantAttributes);
                            $submittedVariantIds[] = $variant->id;
                            if ($isDefaultRequest) $defaultVariantCandidateId = $variant->id;
                        }
                    } else {
                        $newVariant = $product->variants()->create(array_merge($variantPayload, ['product_id' => $product->id]));
                        $newVariant->attributeValues()->attach($variantAttributes);
                        $submittedVariantIds[] = $newVariant->id;
                        if ($isDefaultRequest) $defaultVariantCandidateId = $newVariant->id;
                    }
                }
                $variantsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
                if (!empty($variantsToDelete)) {
                    ProductVariant::whereIn('id', $variantsToDelete)->where('product_id', $product->id)->delete();
                }

                $currentVariants = $product->refresh()->variants;
                if ($currentVariants->isNotEmpty()) {
                    $currentVariants->each(fn($v) => $v->update(['is_default' => false]));
                    $actualDefaultVariant = $defaultVariantCandidateId ? $currentVariants->find($defaultVariantCandidateId) : null;
                    if ($actualDefaultVariant) {
                        $actualDefaultVariant->update(['is_default' => true]);
                    } elseif ($currentVariants->isNotEmpty()) {
                        $currentVariants->first()->update(['is_default' => true]);
                    }
                }
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
     * Xóa sản phẩm
     * (Logic từ controller gốc của bạn)
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            // Xóa file vật lý và record DB của uploaded files
            $product->allUploadedFiles()->get()->each(function($file) {
                 Storage::disk($file->disk)->delete($file->path);
                 $file->delete(); // Xóa record UploadedFile
            });

            // Xóa các biến thể và liên kết
            // Nếu có ON DELETE CASCADE ở DB cho product_variants.product_id,
            // thì chỉ cần $product->delete() là đủ cho variants.
            // Nếu không, cần xóa thủ công.
            foreach ($product->variants as $variant) {
                $variant->attributeValues()->detach(); // Xóa liên kết trong bảng pivot
                // $variant->delete(); // Sẽ được xóa bởi cascade hoặc xóa ở dưới
            }
             $product->variants()->delete(); // Xóa tất cả variants của sản phẩm này

            $product->delete(); // Xóa sản phẩm

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm và các dữ liệu liên quan đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa sản phẩm: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi xóa sản phẩm: ' . $e->getMessage());
        }
    }

    /**
     * Xóa ảnh gallery
     * (Logic từ controller gốc của bạn)
     */
    public function deleteGalleryImage(UploadedFile $uploadedFile)
    {
        if ($uploadedFile->attachable_type !== Product::class || $uploadedFile->type !== 'gallery_image') {
            return back()->with('error', 'File không hợp lệ hoặc không phải ảnh gallery của sản phẩm.');
        }
        try {
            Storage::disk($uploadedFile->disk)->delete($uploadedFile->path); // Xóa file vật lý
            $uploadedFile->delete(); // Xóa record DB
            return back()->with('success', 'Ảnh gallery đã được xóa.');
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa gallery image ID {$uploadedFile->id}: " . $e->getMessage());
            return back()->with('error', 'Không thể xóa ảnh gallery. Vui lòng thử lại.');
        }
    }
}
