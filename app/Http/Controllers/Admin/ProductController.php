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

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm.
     * Logic lọc và sắp xếp được giữ nguyên.
     */
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'variants' => function ($q) {
                $q->orderBy('is_default', 'desc')->orderBy('created_at', 'asc');
            },
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
        if (in_array($sortBy, ['name', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $products = $query->distinct()->paginate(15)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Hiển thị form tạo sản phẩm mới.
     */
    public function create()
    {
        try {
        $tempFileIds = session('temp_uploaded_file_ids', []);
        
        Log::info('--- Bắt đầu phiên tạo sản phẩm mới ---');
        Log::info('Các ID file tạm trong session:', $tempFileIds);

        if (!empty($tempFileIds)) {
            $filesToDelete = UploadedFile::whereIn('id', $tempFileIds)
                                         ->whereNull('attachable_id')
                                         ->get();

            Log::info("Tìm thấy " . $filesToDelete->count() . " file cần xóa.");

            foreach ($filesToDelete as $file) {
                Log::info("Đang yêu cầu xóa file ID: {$file->id}, Path: {$file->path}");
                // Hàm deleteFile trong service của bạn sẽ gọi $file->delete()
                // và kích hoạt event 'deleting' trong Model ở Bước 1.
                app(FileService::class)->deleteFile($file);
            }
        }
    } catch (\Exception $e) {
        Log::error('Lỗi nghiêm trọng khi dọn dẹp file tạm: ' . $e->getMessage());
    } finally {
        // Luôn luôn xóa session key này khi vào trang tạo mới
        session()->forget('temp_uploaded_file_ids');
        Log::info('Đã dọn dẹp session temp_uploaded_file_ids.');
        Log::info('--- Kết thúc phiên tạo sản phẩm mới ---');
    }
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'attributes'));
    }

    /**
     * Sửa đổi phương thức store để xử lý ID ảnh thay vì upload file.
     */
    /**
     * Lưu sản phẩm mới vào CSDL.
     * Logic đã được cập nhật để xử lý ảnh cho từng biến thể.
     */
    public function store(ProductRequest $request)
    {
        DB::beginTransaction();
        try {
            // 1. Tạo sản phẩm với các thông tin cơ bản
            $productData = $request->except([
                'cover_image_id',
                'gallery_images',
                'variants',
                'simple_sku',
                'simple_price',
                'simple_sale_price',
                'simple_stock_quantity'
            ]);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['created_by'] = Auth::id();

            $product = Product::create($productData);

            // 2. Xử lý logic cho sản phẩm đơn giản (bao gồm cả ảnh)
            if ($request->input('type') === 'simple') {
                // Đính kèm ảnh bìa
                if ($request->filled('cover_image_id')) {
                    $coverImage = UploadedFile::find($request->input('cover_image_id'));
                    if ($coverImage) {
                        $coverImage->update([
                            'attachable_id' => $product->id,
                            'attachable_type' => Product::class,
                            'type' => 'cover_image'
                        ]);
                    }
                }

                // Đính kèm thư viện ảnh
                if ($request->has('gallery_images') && is_array($request->input('gallery_images'))) {
                    foreach ($request->input('gallery_images') as $order => $imageId) {
                        $galleryImage = UploadedFile::find($imageId);
                        if ($galleryImage) {
                            $galleryImage->update([
                                'attachable_id' => $product->id,
                                'attachable_type' => Product::class,
                                'type' => 'gallery_image',
                                'order' => $order + 1
                            ]);
                        }
                    }
                }

                // Tạo một biến thể duy nhất cho sản phẩm đơn giản
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $request->input('simple_sku'),
                    'price' => $request->input('simple_price'),
                    'sale_price' => $request->input('simple_sale_price'),
                    'stock_quantity' => $request->input('simple_stock_quantity'),
                    'is_default' => true,
                    'status' => 'active',
                ]);

            }
            // 3. Xử lý logic cho sản phẩm có biến thể
            elseif ($request->input('type') === 'variable' && $request->has('variants')) {
                // Lấy index của biến thể được chọn làm mặc định từ form
                $defaultVariantKey = $request->input('variant_is_default_radio_group');

                foreach ($request->input('variants') as $key => $variantData) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'],
                        // Cải thiện logic: Dựa vào input từ radio button thay vì key=0
                        'is_default' => ($defaultVariantKey == $key),
                        'status' => 'active',
                    ]);

                    // Gắn các giá trị thuộc tính cho biến thể
                    $variant->attributeValues()->attach(array_values($variantData['attributes']));

                    // =========================================================
                    // === BẮT ĐẦU LOGIC MỚI: XỬ LÝ ẢNH CHO BIẾN THỂ ===
                    // =========================================================
                    // Giả định frontend sẽ gửi lên 'image_ids' và 'primary_image_id' cho mỗi biến thể
                    if (isset($variantData['image_ids']) && is_array($variantData['image_ids'])) {

                        $primaryImageId = $variantData['primary_image_id'] ?? null;

                        // Lấy tất cả các file hợp lệ một lần để tối ưu truy vấn
                        $images = UploadedFile::whereIn('id', $variantData['image_ids'])->get();

                        foreach ($images as $order => $image) {
                            $isPrimary = ($image->id == $primaryImageId);

                            // Cập nhật bản ghi trong bảng uploaded_files để liên kết nó với biến thể này
                            $image->update([
                                'attachable_id' => $variant->id,
                                'attachable_type' => ProductVariant::class,
                                'type' => 'variant_image',
                                'order' => $order + 1,
                            ]);

                            // Nếu là ảnh chính, cập nhật cột `primary_image_id` của biến thể
                            // LƯU Ý: Bạn cần tạo một migration để thêm cột này vào bảng `product_variants`
                            // Ví dụ: `Schema::table('product_variants', function (Blueprint $table) { $table->foreignId('primary_image_id')->nullable()->constrained('uploaded_files')->onDelete('set null'); });`
                            if ($isPrimary && $variant->getConnection()->getSchemaBuilder()->hasColumn($variant->getTable(), 'primary_image_id')) {
                                $variant->update(['primary_image_id' => $image->id]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            $request->session()->forget('temp_uploaded_file_ids');
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo sản phẩm: ' . $e->getMessage() . ' tại dòng ' . $e->getLine() . ' trong file ' . $e->getFile());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi tạo sản phẩm. Vui lòng kiểm tra lại dữ liệu.');
        }
    }

    /**
     * Hiển thị chi tiết sản phẩm.
     */
    public function show(Product $product)
    {
        $product->load('category', 'variants.attributeValues.attribute', 'coverImage', 'galleryImages');
        return view('admin.products.show', compact('product'));
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm.
     */
    public function edit(Product $product)
    {
        $product->load('variants.attributeValues', 'coverImage', 'galleryImages');
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'attributes'));
    }

    /**
     * Cập nhật sản phẩm, logic ảnh được sửa đổi để đồng bộ hóa.
     */
    public function update(ProductRequest $request, Product $product)
    {
        DB::beginTransaction();
        try {
            // 1. Cập nhật thông tin sản phẩm
            $productData = $request->except(['_token', '_method', 'cover_image_id', 'gallery_images', 'variants', 'simple_sku', 'simple_price', 'simple_sale_price', 'simple_stock_quantity']);
            $productData['slug'] = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('name'));
            $productData['is_featured'] = $request->boolean('is_featured');
            $productData['updated_by'] = Auth::id();
            $product->update($productData);

            // 2. Đồng bộ Ảnh bìa
            $newCoverImageId = $request->input('cover_image_id');
            $currentCoverImage = $product->coverImage;

            if ($currentCoverImage && $currentCoverImage->id != $newCoverImageId) {
                // Hủy đính kèm ảnh bìa cũ
                $currentCoverImage->update(['attachable_id' => null, 'attachable_type' => null]);
            }
            if ($newCoverImageId && (!$currentCoverImage || $currentCoverImage->id != $newCoverImageId)) {
                $newCoverImage = UploadedFile::find($newCoverImageId);
                if ($newCoverImage) {
                    $newCoverImage->update([
                        'attachable_id' => $product->id,
                        'attachable_type' => Product::class,
                        'type' => 'cover_image'
                    ]);
                }
            }

            // 3. Đồng bộ Thư viện ảnh
            $submittedGalleryIds = $request->input('gallery_images', []);
            if (!is_array($submittedGalleryIds))
                $submittedGalleryIds = [];

            $currentGalleryIds = $product->galleryImages()->pluck('id')->toArray();

            // Hủy đính kèm các ảnh cũ không còn trong danh sách gửi lên
            $idsToDetach = array_diff($currentGalleryIds, $submittedGalleryIds);
            if (!empty($idsToDetach)) {
                UploadedFile::whereIn('id', $idsToDetach)->update(['attachable_id' => null, 'attachable_type' => null]);
            }

            // Đính kèm các ảnh mới và cập nhật thứ tự
            foreach ($submittedGalleryIds as $order => $imageId) {
                $galleryImage = UploadedFile::find($imageId);
                if ($galleryImage) {
                    $galleryImage->update([
                        'attachable_id' => $product->id,
                        'attachable_type' => Product::class,
                        'type' => 'gallery_image',
                        'order' => $order + 1
                    ]);
                }
            }

            // 4. Xử lý Biến thể
            if ($product->type === 'simple') {
                $defaultVariant = $product->variants()->first();
                if ($defaultVariant) {
                    $defaultVariant->update([
                        'sku' => $request->input('simple_sku'),
                        'price' => $request->input('simple_price'),
                        'sale_price' => $request->input('simple_sale_price'),
                        'stock_quantity' => $request->input('simple_stock_quantity'),
                    ]);
                }
            } elseif ($product->type === 'variable' && $request->has('variants')) {
                $existingVariantIds = $product->variants->pluck('id')->toArray();
                $submittedVariantIds = [];
                $defaultVariantCandidateId = null;

                foreach ($request->input('variants') as $key => $variantData) {
                    $variantAttributes = array_values($variantData['attributes']);
                    $isDefaultRequest = ($request->input("variant_is_default_radio_group") == $key);

                    $variantPayload = [
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'stock_quantity' => $variantData['stock_quantity'],
                        'status' => 'active',
                    ];

                    if (isset($variantData['id']) && !empty($variantData['id'])) {
                        $variant = ProductVariant::find($variantData['id']);
                        if ($variant && $variant->product_id === $product->id) {
                            $variant->update($variantPayload);
                            $variant->attributeValues()->sync($variantAttributes);
                            $submittedVariantIds[] = $variant->id;
                            if ($isDefaultRequest)
                                $defaultVariantCandidateId = $variant->id;
                        }
                    } else {
                        $newVariant = $product->variants()->create($variantPayload);
                        $newVariant->attributeValues()->attach($variantAttributes);
                        $submittedVariantIds[] = $newVariant->id;
                        if ($isDefaultRequest)
                            $defaultVariantCandidateId = $newVariant->id;
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
                    } else {
                        $currentVariants->first()->update(['is_default' => true]);
                    }
                }
            }


            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật sản phẩm: ' . $e->getMessage() . ' tại dòng ' . $e->getLine());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi cập nhật sản phẩm.');
        }
    }

    /**
     * Xóa sản phẩm và hủy đính kèm các file liên quan.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            // Hủy đính kèm tất cả các file thay vì xóa hẳn
            UploadedFile::where('attachable_id', $product->id)
                ->where('attachable_type', Product::class)
                ->update(['attachable_id' => null, 'attachable_type' => null]);

            $product->variants()->delete();
            $product->delete();

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa sản phẩm: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi xóa sản phẩm.');
        }
    }

    /**
     * Xóa một ảnh gallery cụ thể (hủy đính kèm nó khỏi sản phẩm)
     */
    public function deleteGalleryImage(UploadedFile $uploadedFile)
    {
        if ($uploadedFile->attachable_type !== Product::class) {
            return back()->with('error', 'File không hợp lệ.');
        }

        try {
            $uploadedFile->update(['attachable_id' => null, 'attachable_type' => null]);
            return back()->with('success', 'Đã gỡ ảnh khỏi gallery.');
        } catch (\Exception $e) {
            Log::error("Lỗi khi gỡ ảnh gallery ID {$uploadedFile->id}: " . $e->getMessage());
            return back()->with('error', 'Không thể gỡ ảnh. Vui lòng thử lại.');
        }
    }
}
