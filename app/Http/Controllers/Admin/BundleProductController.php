<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ProductBundle;
use App\Models\ProductVariant;
use App\Http\Controllers\Controller;

class BundleProductController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductBundle::with([
            'mainProducts.productVariant.product',
            'suggestedProducts.productVariant.product'
        ])->latest()->whereNull('deleted_at');


        // Lọc theo tên gói (tên chứa chuỗi tìm kiếm)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }


        // Lọc theo trạng thái (giả sử có cột status: active/inactive)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bundles = $query->paginate(10);

        return view('admin.bundle_products.index', compact('bundles'));
    }


    public function create()
    {
        // Lấy danh sách sản phẩm cha cùng với các biến thể và attributeValues
        $products = Product::with(['variants' => function ($query) {
            $query->with('attributeValues')->orderBy('created_at', 'desc'); // Tải attributeValues và sắp xếp biến thể
        }])
            ->orderBy('created_at', 'desc') // Sắp xếp sản phẩm cha từ mới đến cũ
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'variants' => $product->variants->map(function ($variant) use ($product) {
                        // Tạo tên biến thể từ attributeValues
                        $variantName = $variant->attributeValues->pluck('value')->filter()->join(' - ');
                        // Tạo display_name: Tên sản phẩm cha + Tên biến thể
                        $displayName = $variantName ? $product->name . ' - ' . $variantName : $product->name;
                        return [
                            'id' => $variant->id,
                            'name' => $variantName, // Tên biến thể từ attributeValues
                            'display_name' => $displayName, // Tên hiển thị kết hợp
                            'sku' => $variant->sku,
                            'image' => $variant->image_url, // Sử dụng getImageUrlAttribute
                            'created_at' => $variant->created_at, // Để sắp xếp
                        ];
                    })->toArray(),
                ];
            });

        // Lấy danh sách danh mục
        $categories = Category::orderBy('name')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
            ];
        });

        return view('admin.bundle_products.create', [
            'products' => $products,
            'categories' => $categories, // Thêm biến categories
        ]);
    }

    public function getProductsByCategory(Request $request)
    {
        \Log::info("API getProductsByCategory called", [
            'category_id' => $request->input('category_id'),
            'search' => $request->input('search', '')
        ]);

        try {
            $categoryId = $request->input('category_id');
            $search = $request->input('search', '');

            if (!$categoryId) {
                \Log::info("No category_id provided, returning empty variants");
                return response()->json(['variants' => []], 200);
            }

            $query = Product::with(['variants' => function ($query) {
                $query->with('attributeValues')->orderBy('created_at', 'desc');
            }])
                ->where('category_id', $categoryId); // ✅ Sửa chỗ này

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('variants', function ($q) use ($search) {
                            $q->where('sku', 'like', "%{$search}%")
                                ->orWhereHas('attributeValues', function ($q) use ($search) {
                                    $q->where('value', 'like', "%{$search}%");
                                });
                        });
                });
            }

            $products = $query->orderBy('created_at', 'desc')->get();
            \Log::info("Found products: " . $products->count());

            // Tạo danh sách biến thể phẳng
            $variants = $products->flatMap(function ($product) {
                return $product->variants->map(function ($variant) use ($product) {
                    $variantName = $variant->attributeValues->pluck('value')->filter()->join(' - ');
                    $displayName = $variantName ? $product->name . ' - ' . $variantName : $product->name;
                    return [
                        'id' => $variant->id,
                        'display_name' => $displayName,
                        'sku' => $variant->sku,
                        'image' => $variant->image_url,
                        'created_at' => $variant->created_at,
                    ];
                });
            })->sortByDesc('created_at')->values();

            \Log::info("Returning variants", ['count' => $variants->count()]);
            return response()->json(['variants' => $variants], 200);
        } catch (\Exception $e) {
            \Log::error("Error in getProductsByCategory: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500); // ✅ log rõ lỗi thay vì chung chung
        }
    }




    public function store(Request $request)
    {
        $validated = $request->validate([
            'bundle_name' => 'required|string|max:255',
            'bundle_title' => 'nullable|string|max:255',
            'bundle_description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:on',
            'main_products' => 'required|array',
            'main_products.*' => 'exists:product_variants,id',
            'suggested_products' => 'nullable|array',
            'suggested_products.*.id' => 'exists:product_variants,id',
            // Xóa dòng discount_type
            // Xóa dòng discount_value
            'suggested_products.*.is_preselected' => 'nullable|boolean',
            'suggested_products.*.display_order' => 'nullable|integer|min:0',
        ]);

        try {
            $bundle = ProductBundle::create([
                'name' => $validated['bundle_name'],
                'display_title' => $validated['bundle_title'] ?? '',
                'description' => $validated['bundle_description'] ?? '',
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $request->has('status') ? 'active' : 'inactive',
            ]);

            // Thêm sản phẩm chính
            foreach ($validated['main_products'] as $variantId) {
                $bundle->mainProducts()->create([
                    'product_variant_id' => $variantId
                ]);
            }

            // Thêm sản phẩm gợi ý
            if (!empty($validated['suggested_products'])) {
                foreach ($validated['suggested_products'] as $index => $product) {
                    $bundle->suggestedProducts()->create([
                        'product_variant_id' => $product['id'],
                        // Xóa dòng discount_type
                        // Xóa dòng discount_value
                        'is_preselected' => isset($product['is_preselected']) ? (bool)$product['is_preselected'] : true,
                        'display_order' => $product['display_order'] ?? $index,
                    ]);
                }
            }

            return redirect()->route('admin.bundle-products.index')->with('success', 'Tạo bundle thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo bundle: ' . $e->getMessage()])->withInput();
        }
    }

    // Hàm hiển thị form chỉnh sửa bundle
    public function edit(ProductBundle $bundle)
    {
        // Nạp thêm quan hệ
        $bundle->load([
            'mainProducts.productVariant.product',
            'suggestedProducts.productVariant.product'
        ]);

        // Xử lý mainProducts
        $mainProducts = $bundle->mainProducts->map(function ($item) {
            return [
                'id' => optional($item->productVariant)->id ?? 0,
                'name' => optional($item->productVariant)->product->name ?? 'Không có tên',
                'sku' => optional($item->productVariant)->sku ?? 'N/A',
                'image' => optional($item->productVariant)->image_url ?? '',
            ];
        });

        // Xử lý suggestedProducts
        $suggestedProducts = $bundle->suggestedProducts->map(function ($item) {
            return [
                'id' => optional($item->productVariant)->id ?? 0,
                'name' => optional($item->productVariant)->product->name ?? 'Không có tên',
                'sku' => optional($item->productVariant)->sku ?? 'N/A',
                'image' => optional($item->productVariant)->image_url ?? '',
                'discount_type' => $item->discount_type ?? 'fixed_price',
                'discount_value' => $item->discount_value ?? 0,
                'is_preselected' => $item->is_preselected ?? false,
            ];
        });

        // Lấy danh sách product variants
        $productVariants = ProductVariant::with(['primaryImage', 'product', 'attributeValues'])
            ->select('product_variants.id', 'product_variants.sku', 'product_variants.primary_image_id', 'product_variants.product_id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->get()
            ->map(function ($variant) {
                $attributeString = $variant->attributeValues->pluck('value')->implode(' ');
                return [
                    'id' => $variant->id,
                    'name' => trim(($variant->product->name ?? 'Không có tên') . ' ' . $attributeString),
                    'sku' => $variant->sku,
                    'image' => $variant->image_url,
                ];
            });


        return view('admin.bundle_products.edit', compact('bundle', 'mainProducts', 'suggestedProducts', 'productVariants'));
    }

    // Hàm xử lý cập nhật bundle
    public function update(Request $request, ProductBundle $bundle)
    {
        $validated = $request->validate([
            'bundle_name' => 'required|string|max:255',
            'bundle_title' => 'nullable|string|max:255',
            'bundle_description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:on',
            'main_products' => 'required|array',
            'main_products.*' => 'exists:product_variants,id',
            'suggested_products' => 'nullable|array',
            'suggested_products.*.id' => 'exists:product_variants,id',
            'suggested_products.*.discount_type' => 'nullable|in:fixed_price,percentage_discount',
            'suggested_products.*.discount_value' => 'nullable|numeric|min:0',
            'suggested_products.*.is_preselected' => 'nullable|boolean',
            'suggested_products.*.display_order' => 'nullable|integer|min:0',
        ]);

        try {
            // Cập nhật thông tin bundle
            $bundle->update([
                'name' => $validated['bundle_name'],
                'display_title' => $validated['bundle_title'] ?? '',
                'description' => $validated['bundle_description'] ?? '',
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $request->has('status') ? 'active' : 'inactive',
            ]);

            // Xóa các sản phẩm chính hiện tại
            $bundle->mainProducts()->delete();

            // Thêm lại sản phẩm chính
            foreach ($validated['main_products'] as $variantId) {
                $bundle->mainProducts()->create([
                    'product_variant_id' => $variantId
                ]);
            }

            // Xóa các sản phẩm gợi ý hiện tại
            $bundle->suggestedProducts()->delete();

            // Thêm lại sản phẩm gợi ý
            if (!empty($validated['suggested_products'])) {
                foreach ($validated['suggested_products'] as $index => $product) {
                    $bundle->suggestedProducts()->create([
                        'product_variant_id' => $product['id'],
                        'discount_type' => $product['discount_type'] ?? 'fixed_price',
                        'discount_value' => $product['discount_value'] ?? 0,
                        'is_preselected' => isset($product['is_preselected']) ? (bool)$product['is_preselected'] : true,
                        'display_order' => $product['display_order'] ?? $index,
                    ]);
                }
            }

            return redirect()->route('admin.bundle-products.index')->with('success', 'Cập nhật bundle thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật bundle: ' . $e->getMessage()])->withInput();
        }
    }

    // Hiển thị chi tiết một gói sản phẩm
    public function show(ProductBundle $bundle)
    {
        $bundle->load([
            'mainProducts.productVariant.product',
            'suggestedProducts.productVariant.product'
        ]);

        // Main products
        $mainProducts = $bundle->mainProducts->map(function ($item) {
            $variant = $item->productVariant;
            $originalPrice = $variant?->price ?? 0;
            $discountedPrice = $variant?->sale_price ?? $originalPrice;

            return (object) [
                'variant' => $variant,
                'product' => $variant?->product,
                'sku' => $variant?->sku ?? 'N/A',
                'image' => $variant?->image_url ?? '',
                'original_price' => $originalPrice,
                'discounted_price' => $discountedPrice,
            ];
        });

        // Suggested products
        $suggestedProducts = $bundle->suggestedProducts->map(function ($item) {
            $variant = $item->productVariant;
            $originalPrice = $variant?->price ?? 0;
            $baseDiscountedPrice = $variant?->sale_price ?? $originalPrice;

            $finalDiscountedPrice = match ($item->discount_type) {
                'fixed_price' => max(0, $item->discount_value), // sửa tại đây
                'percentage' => max(0, $baseDiscountedPrice * (1 - $item->discount_value / 100)),
                default => $baseDiscountedPrice,
            };

            return (object) [
                'variant' => $variant,
                'product' => $variant?->product,
                'sku' => $variant?->sku ?? 'N/A',
                'image' => $variant?->image_url ?? '',
                'original_price' => $originalPrice,
                'base_discounted_price' => $baseDiscountedPrice,
                'final_discounted_price' => $finalDiscountedPrice,
                'discount_type' => $item->discount_type ?? 'fixed_price',
                'discount_value' => $item->discount_value ?? 0,
                'is_preselected' => $item->is_preselected ?? false,
            ];
        });


        // Tổng giá
        $totalOriginal = $mainProducts->sum('original_price') + $suggestedProducts->sum('original_price');
        $totalAfterDiscount = $mainProducts->sum('discounted_price') + $suggestedProducts->sum('final_discounted_price');

        $priceStats = [
            'total_original' => $totalOriginal,
            'total_discounted' => $totalAfterDiscount,
            'total_saved' => $totalOriginal - $totalAfterDiscount,
            'discount_percent' => $totalOriginal > 0 ? round(100 * ($totalOriginal - $totalAfterDiscount) / $totalOriginal, 1) : 0,
        ];

        return view('admin.bundle_products.show', compact('bundle', 'mainProducts', 'suggestedProducts', 'priceStats'));
    }


    // Phương thức để kích hoạt/tắt deal
    public function toggleStatus(ProductBundle $bundle)
    {
        $bundle->update(['status' => $bundle->status === 'active' ? 'inactive' : 'active']);
        return redirect()->route('admin.bundle-products.show', $bundle->id)->with('success', 'Cập nhật trạng thái thành công!');
    }

    public function destroy(ProductBundle $bundle)
    {
        $bundle->delete();
        return redirect()->route('admin.bundle-products.index')->with('success', 'Xóa bundle thành công (xóa mềm).');
    }

    public function trashed()
    {
        $trashedBundles = ProductBundle::onlyTrashed()->get();

        // Chuẩn bị dữ liệu JSON-friendly
        $bundleData = $trashedBundles->map(function ($bundle) {
            return [
                'id' => $bundle->id,
                'name' => $bundle->bundle_name ?? 'Không có tên',
                'subtitle' => $bundle->bundle_title ?? 'Không có tiêu đề',
                'deleted_at' => $bundle->deleted_at ? $bundle->deleted_at->format('d/m/Y') : 'N/A',
            ];
        });

        return view('admin.bundle_products.trashed', [
            'trashedBundles' => $trashedBundles,
            'bundleData' => $bundleData, // 👈 truyền thêm mảng JSON-friendly
        ]);
    }


    public function restore($id)
    {
        $bundle = ProductBundle::onlyTrashed()->findOrFail($id);
        $bundle->restore();

        return redirect()->route('admin.bundle-products.trashed')->with('success', 'Khôi phục thành công!');
    }

    public function forceDelete($id)
    {
        $bundle = ProductBundle::onlyTrashed()->findOrFail($id);
        $bundle->forceDelete();

        return redirect()->route('admin.bundle-products.trashed')->with('success', 'Đã xóa vĩnh viễn!');
    }

    public function restoreBulk(Request $request)
    {
        // Lấy danh sách ID từ form hoặc AJAX
        $ids = $request->input('ids', []);
        ProductBundle::onlyTrashed()->whereIn('id', $ids)->restore();

        return redirect()->route('admin.bundle-products.trashed')->with('success', 'Khôi phục thành công!');
    }

    public function forceDeleteBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        ProductBundle::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return redirect()->route('admin.bundle-products.trashed')->with('success', 'Xóa vĩnh viễn thành công!');
    }
}
