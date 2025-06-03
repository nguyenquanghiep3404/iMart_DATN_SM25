<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductVariant;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm với bộ lọc và sắp xếp
     */
    public function index(Request $request)
    {
        $query = Product::with([
            'category', 
            'variants' => function($q){ // Lấy biến thể đầu tiên để hiển thị giá/sku
                $q->orderBy('is_default', 'desc')->orderBy('created_at', 'asc');
            }, 
            'coverImage', 
            'galleryImages'
        ]);

        // --- Bắt đầu Logic Lọc ---
        // Lọc theo từ khóa (tên sản phẩm, SKU)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('variants', function ($variantQuery) use ($searchTerm) {
                      $variantQuery->where('sku', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Lọc theo trạng thái sản phẩm
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Lọc theo loại sản phẩm
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Lọc theo tình trạng kho (phức tạp hơn nếu dựa trên tổng kho của biến thể)
        // Ví dụ đơn giản: lọc sản phẩm có ít nhất 1 biến thể còn hàng
        if ($request->filled('stock_status')) {
            if ($request->input('stock_status') === 'in_stock') {
                $query->whereHas('variants', function ($variantQuery) {
                    $variantQuery->where('stock_quantity', '>', 0);
                });
            } elseif ($request->input('stock_status') === 'out_of_stock') {
                $query->whereDoesntHave('variants', function ($variantQuery) {
                    $variantQuery->where('stock_quantity', '>', 0);
                })->orWhereHas('variants', function($variantQuery){ // Bao gồm cả sp chưa có variant nào
                    $variantQuery->whereRaw('NOT EXISTS (select 1 from product_variants pv where pv.product_id = products.id and pv.stock_quantity > 0)');
                }, '<', 1);
            }
        }
        
        // Lọc theo khoảng giá (dựa trên giá của biến thể đầu tiên hoặc default)
        // Cần JOIN hoặc subquery để lọc giá hiệu quả. Cách đơn giản hơn là lọc sau khi lấy dữ liệu nhưng không hiệu quả với DB lớn.
        // Ví dụ này sẽ phức tạp, tạm thời bỏ qua để giữ controller gọn. Nếu cần, có thể làm bằng cách JOIN với product_variants.
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


        // --- Bắt đầu Logic Sắp xếp ---
        $sortBy = $request->input('sort_by', 'created_at'); // Mặc định sắp xếp theo ngày tạo
        $sortDir = $request->input('sort_dir', 'desc'); // Mặc định giảm dần

        // Các cột được phép sắp xếp để tránh lỗi SQL injection tiềm ẩn
        $allowedSortColumns = ['name', 'created_at', 'price']; // Thêm 'price' nếu bạn có cột giá ở bảng products hoặc join
        
        if (in_array($sortBy, $allowedSortColumns)) {
            if ($sortBy === 'price') {
                // Sắp xếp theo giá của biến thể đầu tiên (hoặc default)
                // Cần join hoặc subquery. Ví dụ:
                $query->leftJoin('product_variants as pv_sort', function($join) {
                    $join->on('products.id', '=', 'pv_sort.product_id')
                         ->where('pv_sort.is_default', '=', true) // Hoặc điều kiện khác để chọn variant chính
                         ->orWhereRaw('pv_sort.id = (select min(id) from product_variants where product_id = products.id)'); // Lấy variant đầu tiên nếu không có default
                })
                ->orderBy('pv_sort.price', $sortDir)
                ->select('products.*'); // Quan trọng: chỉ select cột từ bảng products để tránh trùng lặp
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
        } else {
            $query->latest(); // Mặc định nếu cột sắp xếp không hợp lệ
        }
        // --- Kết thúc Logic Sắp xếp ---

        $products = $query->distinct()->paginate(15)->withQueryString(); // Thêm distinct() nếu join gây trùng lặp
        
        // Lấy danh sách danh mục để hiển thị trong bộ lọc
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Hiển thị form tạo sản phẩm mới
     */
    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'attributes'));
    }

    /**
     * Lưu sản phẩm mới vào database
     */
    public function store(Request $request)
    {
        // --- LOGIC VALIDATION GIỮ NGUYÊN NHƯ CỦA BẠN ---
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'required|exists:categories,id',
            'short_description' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'cover_image_file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_image_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:published,draft,pending_review,trashed',
            'is_featured' => 'nullable|boolean',
            'type' => 'required|in:simple,variable',
            'sku_prefix' => 'nullable|string|max:50',
            'simple_sku' => 'required_if:type,simple|nullable|string|max:255|unique:product_variants,sku',
            'simple_price' => 'required_if:type,simple|nullable|numeric|min:0',
            'simple_sale_price' => 'nullable|numeric|min:0|lt:simple_price',
            'simple_stock_quantity' => 'required_if:type,simple|nullable|integer|min:0',
            'variants' => 'required_if:type,variable|array|min:1',
            'variants.*.sku' => 'required_if:type,variable|string|max:255|distinct|unique:product_variants,sku',
            'variants.*.price' => 'required_if:type,variable|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'required_if:type,variable|integer|min:0',
            'variants.*.attributes' => 'required_if:type,variable|array|min:1',
            'variants.*.attributes.*' => 'required|exists:attribute_values,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'tags' => 'nullable|string',
            'warranty_information' => 'nullable|string',
        ], [
            'variants.*.sku.distinct' => 'SKU của các biến thể không được trùng nhau.',
            'variants.*.sku.unique' => 'SKU của biến thể đã tồn tại.',
            'simple_sku.unique' => 'SKU sản phẩm đã tồn tại.',
            'simple_sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'cover_image_file.required' => 'Ảnh bìa là bắt buộc.',
             // ... các message khác của bạn
        ]);

        DB::beginTransaction();
        try {
            // --- LOGIC LƯU SẢN PHẨM, ẢNH, BIẾN THỂ GIỮ NGUYÊN NHƯ CỦA BẠN ---
            $productData = $request->only([
                'name', 'category_id', 'short_description', 'description',
                'status', 'type', 'sku_prefix', 'meta_title', 'meta_description', 'tags', 'warranty_information'
            ]);
            $productData['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['created_by'] = Auth::id();
            $productData['updated_by'] = Auth::id();

            $product = Product::create($productData);

            // Xử lý Upload Ảnh Bìa (giữ nguyên logic của bạn)
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

            // Xử lý Upload Thư Viện Ảnh (giữ nguyên logic của bạn)
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
            
            // Xử lý Biến thể (giữ nguyên logic của bạn)
            if ($product->type === 'simple') {
                ProductVariant::create([
                    'product_id' => $product->id, 'sku' => $request->simple_sku,
                    'price' => $request->simple_price, 'sale_price' => $request->simple_sale_price,
                    'stock_quantity' => $request->simple_stock_quantity, 'is_default' => true, 'status' => 'active',
                ]);
            } elseif ($product->type === 'variable' && $request->has('variants')) {
                $isFirstVariant = true;
                foreach ($request->variants as $variantData) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id, 'sku' => $variantData['sku'],
                        'price' => $variantData['price'], 'sale_price' => $variantData['sale_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'], 'is_default' => $isFirstVariant, 'status' => 'active',
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
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi tạo sản phẩm. Vui lòng thử lại. Chi tiết: ' . $e->getMessage());
        }
    }

    // --- CÁC PHƯƠNG THỨC show, edit, update, destroy, deleteGalleryImage GIỮ NGUYÊN NHƯ CỦA BẠN ---
    public function show(Product $product)
    {
        $product->load('category', 'variants.attributeValues.attribute', 'coverImage', 'galleryImages', 'createdBy', 'updatedBy');
        return view('admin.products.show', compact('product'));
    }

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

    public function update(Request $request, Product $product)
    {
        // --- LOGIC VALIDATION GIỮ NGUYÊN NHƯ CỦA BẠN ---
        $isSimpleProduct = $product->type === 'simple';
        $defaultVariantId = null;
        if ($isSimpleProduct) {
            $defaultVariant = $product->variants()->where('is_default', true)->first();
            $defaultVariantId = $defaultVariant ? $defaultVariant->id : null;
        }
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'category_id' => 'required|exists:categories,id',
            'short_description' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'cover_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_image_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'deleted_gallery_image_ids' => 'nullable|array',
            'deleted_gallery_image_ids.*' => 'nullable|integer|exists:uploaded_files,id',
            'status' => 'required|in:published,draft,pending_review,trashed',
            'is_featured' => 'nullable|boolean',
            'sku_prefix' => 'nullable|string|max:50',
            'simple_sku' => ['required_if:type,simple','nullable','string','max:255', Rule::unique('product_variants', 'sku')->ignore($defaultVariantId) ],
            'simple_price' => 'required_if:type,simple|nullable|numeric|min:0',
            'simple_sale_price' => 'nullable|numeric|min:0|lt:simple_price',
            'simple_stock_quantity' => 'required_if:type,simple|nullable|integer|min:0',
            'variants' => 'required_if:type,variable|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.sku' => ['required_if:type,variable', 'string', 'max:255', 'distinct',
                function ($attribute, $value, $fail) use ($request, $product) {
                    $index = explode('.', $attribute)[1];
                    $variantIdBeingChecked = $request->input("variants.{$index}.id");
                    // Check if SKU exists for OTHER products OR for OTHER variants of THIS product
                    $query = DB::table('product_variants')->where('sku', $value);
                    if ($variantIdBeingChecked) { // If updating an existing variant
                        $query->where('id', '!=', $variantIdBeingChecked); // Exclude self
                    }
                    // Check against other variants of the same product (if it's a new variant or a different existing one)
                    $query->where(function($q) use ($product, $variantIdBeingChecked) {
                        $q->where('product_id', '!=', $product->id) // SKU exists on another product
                          ->orWhere(function($q2) use ($product, $variantIdBeingChecked) { // SKU exists on another variant of THIS product
                                $q2->where('product_id', $product->id);
                                if ($variantIdBeingChecked) {
                                    $q2->where('id', '!=', $variantIdBeingChecked);
                                }
                          });
                    });

                    if ($query->exists()) {
                        $fail("SKU '{$value}' đã tồn tại.");
                    }
                }
            ],
            'variants.*.price' => 'required_if:type,variable|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'required_if:type,variable|integer|min:0',
            'variants.*.attributes' => 'required_if:type,variable|array|min:1',
            'variants.*.attributes.*' => 'required|exists:attribute_values,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'tags' => 'nullable|string',
            'warranty_information' => 'nullable|string',
        ],[
            'variants.*.sku.distinct' => 'SKU của các biến thể không được trùng nhau.',
            'simple_sale_price.lt' => 'Giá khuyến mãi của sản phẩm đơn giản phải nhỏ hơn giá gốc.',
        ]);
        
        DB::beginTransaction();
        try {
            // --- LOGIC CẬP NHẬT SẢN PHẨM, ẢNH, BIẾN THỂ GIỮ NGUYÊN NHƯ CỦA BẠN ---
            $productData = $request->only([
                'name', 'category_id', 'short_description', 'description',
                'status', 'sku_prefix', 'meta_title', 'meta_description', 'tags', 'warranty_information'
            ]);
            $productData['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['updated_by'] = Auth::id();
            $product->update($productData);

            // Xử lý Ảnh Bìa (giữ nguyên logic của bạn)
            if ($request->hasFile('cover_image_file')) {
                if ($product->coverImage) $product->coverImage->delete();
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
            // Xử lý Xóa & Thêm Gallery (giữ nguyên logic của bạn)
            if ($request->has('deleted_gallery_image_ids')) {
                UploadedFile::whereIn('id', $request->input('deleted_gallery_image_ids'))
                            ->where('attachable_id', $product->id)->where('attachable_type', Product::class)
                            ->where('type', 'gallery_image')->get()->each(fn($file) => $file->delete());
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
            // Xử lý Biến thể (giữ nguyên logic của bạn)
            if ($product->type === 'simple') {
                 $defaultVariant = $product->variants()->where('is_default', true)->first();
                 if ($defaultVariant) {
                     $defaultVariant->update([
                        'sku' => $request->simple_sku, 'price' => $request->simple_price,
                        'sale_price' => $request->simple_sale_price, 'stock_quantity' => $request->simple_stock_quantity,
                     ]);
                 } else { /* Tạo mới nếu chưa có */ }
                 if($product->wasRecentlyCreated === false && $request->type === 'simple' && $product->getOriginal('type') === 'variable') {
                     $product->variants()->where('is_default', false)->delete();
                 }
            } elseif ($product->type === 'variable' && $request->has('variants')) {
                $existingVariantIds = $product->variants->pluck('id')->toArray();
                $submittedVariantIds = [];
                $defaultVariantCandidateId = null;
                foreach ($request->variants as $key => $variantData) {
                    $variantAttributes = array_values($variantData['attributes']);
                    $isDefaultRequest = $request->input("variants.{$key}.is_default", false);
                    $variantPayload = [
                        'sku' => $variantData['sku'], 'price' => $variantData['price'],
                        'sale_price' => $variantData['sale_price'] ?? null, 'stock_quantity' => $variantData['stock_quantity'],
                        'status' => $variantData['status'] ?? 'active',
                    ];
                    if (isset($variantData['id']) && !empty($variantData['id'])) {
                        $variant = ProductVariant::find($variantData['id']);
                        if ($variant && $variant->product_id === $product->id) {
                            $variant->update($variantPayload); $variant->attributeValues()->sync($variantAttributes);
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
                if (!empty($variantsToDelete)) ProductVariant::whereIn('id', $variantsToDelete)->where('product_id', $product->id)->delete();
                
                $currentVariants = $product->refresh()->variants;
                if ($currentVariants->isNotEmpty()) {
                    $currentVariants->each(fn($v) => $v->update(['is_default' => false]));
                    $actualDefaultVariant = $defaultVariantCandidateId ? $currentVariants->find($defaultVariantCandidateId) : null;
                    if ($actualDefaultVariant) $actualDefaultVariant->update(['is_default' => true]);
                    elseif($currentVariants->isNotEmpty()) $currentVariants->first()->update(['is_default' => true]);
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật sản phẩm: ' . $e->getMessage() . ' tại dòng ' . $e->getLine() . ' trong file ' . $e->getFile());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi cập nhật sản phẩm. Vui lòng thử lại. Chi tiết: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $product->allUploadedFiles()->get()->each(fn($file) => $file->delete());
            foreach ($product->variants as $variant) {
                $variant->attributeValues()->detach();
                $variant->delete();
            }
            $product->delete();
            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa sản phẩm: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi xóa sản phẩm: ' . $e->getMessage());
        }
    }

    public function deleteGalleryImage(UploadedFile $uploadedFile)
    {
        if ($uploadedFile->attachable_type !== Product::class || $uploadedFile->type !== 'gallery_image') {
            return back()->with('error', 'File không hợp lệ hoặc không phải ảnh gallery của sản phẩm.');
        }
        try {
            $uploadedFile->delete();
            return back()->with('success', 'Ảnh gallery đã được xóa.');
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa gallery image ID {$uploadedFile->id}: " . $e->getMessage());
            return back()->with('error', 'Không thể xóa ảnh gallery. Vui lòng thử lại.');
        }
    }
}