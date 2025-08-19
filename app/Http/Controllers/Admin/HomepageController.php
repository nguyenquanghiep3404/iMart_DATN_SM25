<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\HomepageProductBlock;

class HomepageController extends Controller
{
    /**
     * Hiển thị trang quản lý trang chủ
     */
    public function index()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        // Dữ liệu cho JS render danh mục
        $categoriesForJs = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'show_on_homepage' => $cat->show_on_homepage,
                'order' => $cat->order,
                'children' => $cat->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'show_on_homepage' => $child->show_on_homepage,
                        'order' => $child->order,
                    ];
                })->toArray()
            ];
        })->toArray();

        // Lấy các khối sản phẩm từ DB
        $productBlocks = HomepageProductBlock::with([
            'productVariants' => function ($query) { // Sử dụng mối quan hệ mới
                $query->with(['product', 'primaryImage', 'attributeValues.attribute'])->orderBy('pivot_order');
            }
        ])->orderBy('order')->get();

        // Dữ liệu cho JS
        $productBlocksForJs = $productBlocks->map(function ($block) {
            return [
                'id' => $block->id,
                'title' => $block->title,
                'order' => $block->order,
                'is_visible' => $block->is_visible,
                // ✅ Sửa từ $block->products sang $block->productVariants
                'products' => $block->productVariants->map(function ($variant) {
                    $product = $variant->product;
                    $image = $variant && $variant->primaryImage
                        ? asset('storage/' . $variant->primaryImage->path)
                        : '/images/no-image.png';

                    // Lấy thuộc tính "Dung lượng" từ biến thể để tạo tên hiển thị
                    $capacity = '';
                    $capacityAttr = $variant->attributeValues->first(function ($attrVal) {
                        return $attrVal->attribute && $attrVal->attribute->name === 'Dung lượng';
                    });
                    if ($capacityAttr) {
                        $capacity = $capacityAttr->value;
                    }
                    $displayName = $product->name . ($capacity ? ' ' . $capacity : '');

                    return [
                        // ✅ Sửa id từ $product->id thành $variant->id
                        'id' => $variant->id,
                        'product_id' => $product->id,
                        'name' => $displayName, // Sử dụng tên hiển thị mới
                        'image' => $image,
                        'price' => $variant?->price ? (string) $variant->price : null,
                        'sale_price' => $variant?->sale_price ? (string) $variant->sale_price : null,
                        'discount_percent' => ($variant && $variant->price > 0 && $variant->sale_price)
                            ? round(100 - ($variant->sale_price / $variant->price) * 100)
                            : 0,
                    ];
                })->toArray()
            ];
        })->toArray();

        // Lấy banner theo order
        $banners = Banner::with(['desktopImage', 'mobileImage'])
            ->orderBy('order')
            ->get();

        // ✅ CHUYỂN DỮ LIỆU banner sang dạng JS-friendly
        $bannerForJs = $banners->map(function ($b) {
            return [
                'id' => $b->id,
                'title' => $b->title,
                'status' => $b->status,
                'order' => $b->order,
                'start_date' => $b->start_date,
                'end_date' => $b->end_date,
                'desktop_image' => $b->desktopImage ? asset('storage/' . $b->desktopImage->path) : '/images/no-image.png',
                'mobile_image' => $b->mobileImage ? asset('storage/' . $b->mobileImage->path) : null,
            ];
        })->toArray();


        return view('admin.homepage.index', [
            'banners' => $banners, // chưa làm
            'categories' => $categories,
            'bannersForJs' => $bannerForJs,
            'categoriesForJs' => $categoriesForJs,
            'productBlocks' => $productBlocks, // cho Blade nếu cần
            'productBlocksForJs' => $productBlocksForJs, // cho JavaScript render
            'featuredPosts' => [], // chưa làm
        ]);
    }

    /**
     * Lưu toàn bộ thay đổi của trang chủ
     */
    public function update(Request $request)
    {
        $data = $request->all();

        // ✅ Lưu banners
        if (!empty($data['banners'])) {
            foreach ($data['banners'] as $index => $banner) {
                // Lưu hoặc sắp xếp lại banners nếu bạn có bảng Banner
            }
        }

        // ✅ Lưu danh mục
        if (!empty($data['categories'])) {
            foreach ($data['categories'] as $cat) {
                $category = \App\Models\Category::find($cat['id']);
                if ($category) {
                    $category->show_on_homepage = $cat['show_on_homepage'] ?? false;
                    $category->order = $cat['order'] ?? 0;
                    $category->save();
                }

                // Nếu có children:
                if (!empty($cat['children'])) {
                    foreach ($cat['children'] as $child) {
                        $childModel = \App\Models\Category::find($child['id']);
                        if ($childModel) {
                            $childModel->show_on_homepage = $child['show_on_homepage'] ?? false;
                            $childModel->order = $child['order'] ?? 0;
                            $childModel->save();
                        }
                    }
                }
            }
        }

        // ✅ Lưu các khối sản phẩm
        if (!empty($data['product_blocks'])) {
            foreach ($data['product_blocks'] as $index => $block) {
                // Lưu hoặc cập nhật block
                $productBlock = HomepageProductBlock::updateOrCreate(
                    ['id' => $block['id']],
                    [
                        'title' => $block['title'],
                        'order' => $block['order'] ?? ($index + 1),
                        'is_visible' => $block['is_visible'] ?? true
                    ]
                );

                // Gán sản phẩm vào block với thứ tự
                $syncProducts = [];
                if (!empty($block['products'])) {
                    foreach ($block['products'] as $i => $product) {
                        $syncProducts[$product['id']] = ['order' => $i + 1];
                    }
                }

                $productBlock->products()->sync($syncProducts);
            }
        }

        return response()->json(['message' => '✅ Đã lưu toàn bộ thay đổi']);
    }

    /**
     * Cập nhật trạng thái hiển thị của danh mục
     */
    public function toggleCategory(Request $request, $categoryId)
    {
        try {
            $category = Category::findOrFail($categoryId);
            $isActive = $request->input('show_on_homepage', false);

            // Kiểm tra số lượng danh mục được chọn
            $selectedCount = Category::where('show_on_homepage', true)->count();
            if ($isActive && $selectedCount >= 7 && !$category->show_on_homepage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ được chọn tối đa 7 danh mục hiển thị trên trang chủ.'
                ], 422);
            }

            $category->show_on_homepage = $isActive;
            $category->save();

            return response()->json([
                'success' => true,
                'message' => $isActive ? 'Đã bật hiển thị danh mục trên trang chủ' : 'Đã tắt hiển thị danh mục trên trang chủ',
                'show_on_homepage' => $category->show_on_homepage
            ]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi cập nhật trạng thái danh mục: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái danh mục'
            ], 500);
        }
    }

    /**
     * Cập nhật thứ tự danh mục
     */
    public function updateCategoryOrder(Request $request)
    {
        $categoryIds = $request->input('category_ids', []);
        DB::beginTransaction();
        try {
            foreach ($categoryIds as $index => $categoryId) {
                Category::where('id', $categoryId)->update(['order' => $index + 1]);
            }
            DB::commit();
            return response()->json(['message' => 'Cập nhật thứ tự danh mục thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Lỗi cập nhật thứ tự danh mục: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi cập nhật thứ tự danh mục'], 500);
        }
    }

    public function storeProductBlock(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $block = new HomepageProductBlock();
        $block->title = $validated['title'];
        $block->order = HomepageProductBlock::max('order') + 1;
        $block->is_visible = true;
        $block->save();

        return response()->json([
            'success' => true,
            'block' => [
                'id' => $block->id,
                'title' => $block->title,
                'is_visible' => $block->is_visible,
                'order' => $block->order,
                'products' => [],
            ]
        ]);
    }

    public function searchProducts(Request $request)
    {
        try {
            $query = $request->query('q');
            $filter = $request->query('filter');
            $perPage = $request->query('per_page', 15); // 15 biến thể mỗi trang, giống FlashSaleController
            $page = $request->query('page', 1);

            // Lấy tất cả biến thể sản phẩm
            $variantsQuery = ProductVariant::with([
                'product' => function ($q) {
                    $q->where('status', 'published');
                },
                'primaryImage',
                'attributeValues.attribute'
            ])->whereHas('product', function ($q) use ($query) {
                $q->where('status', 'published');
                if ($query) {
                    $q->where('name', 'like', '%' . $query . '%');
                }
            });

            // Áp dụng bộ lọc nếu có
            if ($filter === 'featured') {
                $variantsQuery->whereHas('product', function ($q) {
                    $q->where('is_featured', true);
                });
            } elseif ($filter === 'latest_10') {
                $variantsQuery->take(10); // Giữ nguyên giới hạn 10 cho bộ lọc này
            }

            // Sắp xếp mới nhất trước và phân trang
            $variants = $variantsQuery->orderBy('created_at', 'desc')->paginate($perPage);

            // Log dữ liệu attributeValues để debug
            \Log::info('Variants attributeValues:', $variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'attributeValues' => $variant->attributeValues->map(function ($attrVal) {
                        return [
                            'attribute_name' => $attrVal->attribute->name,
                            'value' => $attrVal->value
                        ];
                    })->toArray()
                ];
            })->toArray());

            // Nhóm biến thể theo product_id và Dung lượng
            $groupedVariants = $variants->getCollection()->groupBy(function ($variant) {
                $capacity = 'default';
                $capacityAttr = $variant->attributeValues->first(function ($attrVal) {
                    return $attrVal->attribute && $attrVal->attribute->name === 'Dung lượng';
                });
                if ($capacityAttr) {
                    $capacity = trim(strtolower($capacityAttr->value));
                }
                return $variant->product_id . '_' . $capacity;
            })->map(function ($group) {
                // Chọn biến thể mặc định hoặc đầu tiên
                return $group->where('is_default', true)->first() ?? $group->first();
            })->values();

            // Cập nhật collection của pagination
            $variants->setCollection($groupedVariants);

            // Map dữ liệu sang định dạng chuẩn cho frontend
            $result = $groupedVariants->map(function ($variant) {
                $product = $variant->product;
                $image = $variant->primaryImage
                    ? asset('storage/' . $variant->primaryImage->path)
                    : '';

                $totalSold = DB::table('order_items')
                    ->where('product_variant_id', $variant->id)
                    ->sum('quantity');

                // Tạo tên hiển thị: tên sản phẩm + Dung lượng
                $capacity = '';
                $capacityAttr = $variant->attributeValues->first(function ($attrVal) {
                    return $attrVal->attribute && $attrVal->attribute->name === 'Dung lượng';
                });
                if ($capacityAttr) {
                    $capacity = $capacityAttr->value;
                }
                $displayName = $product->name . ($capacity ? ' ' . $capacity : '');

                return [
                    'id' => $variant->id, // ID của biến thể đại diện
                    'product_id' => $product->id,
                    'name' => $displayName, // Tên sản phẩm + Dung lượng
                    'price' => $variant->price ?? 0,
                    'sale_price' => $variant->sale_price ?? null,
                    'discount_percent' => ($variant->price > 0 && $variant->sale_price)
                        ? round(100 - ($variant->sale_price / $variant->price) * 100)
                        : 0,
                    'image' => $image,
                    'stock_quantity' => $variant->getSellableStockAttribute() ?? 0,
                    'is_featured' => $product->is_featured,
                    'release_date' => $product->created_at->toDateString(),
                    'sold_quantity' => $totalSold,
                ];
            });

            // Trả về dữ liệu phân trang
            return response()->json([
                'data' => $result,
                'current_page' => $variants->currentPage(),
                'last_page' => $variants->lastPage(),
                'total' => $variants->total(),
                'per_page' => $variants->perPage(),
                'links' => $variants->links()->elements, // Thêm links giống Laravel
            ]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi tìm sản phẩm: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi tìm sản phẩm.'], 500);
        }
    }

    public function addProductsToBlock(Request $request, HomepageProductBlock $block)
    {
        try {
            // ✅ Nhận variant ID từ frontend
            $variantIds = $request->input('product_variant_ids', []);

            if (empty($variantIds)) {
                return response()->json(['error' => 'Không có sản phẩm nào được chọn'], 422);
            }

            // Lấy order lớn nhất hiện tại để thêm sản phẩm mới vào cuối danh sách
            $maxOrder = DB::table('homepage_block_product')
                ->where('block_id', $block->id)
                ->max('order') ?? 0;

            $syncData = [];
            foreach ($variantIds as $i => $variantId) {
                // Tạo mảng dữ liệu để thêm biến thể với order tiếp theo
                $syncData[$variantId] = ['order' => $maxOrder + $i + 1];
            }

            // ✅ Sử dụng syncWithoutDetaching với mối quan hệ mới
            $block->productVariants()->syncWithoutDetaching($syncData);

            // ✅ Tải lại các biến thể trong block sau khi thêm
            $variantsInBlock = $block->productVariants()->with([
                'product',
                'primaryImage',
                'attributeValues.attribute'
            ])->orderBy('pivot_order')->get();

            // Ánh xạ dữ liệu để đồng bộ với cấu trúc trả về của searchProducts
            $result = $variantsInBlock->map(function ($variant) {
                $product = $variant->product;
                $image = $variant?->primaryImage
                    ? asset('storage/' . $variant->primaryImage->path)
                    : '';

                // Tạo tên hiển thị (tên sản phẩm + Dung lượng)
                $capacity = '';
                $capacityAttr = $variant->attributeValues->first(function ($attrVal) {
                    return $attrVal->attribute && $attrVal->attribute->name === 'Dung lượng';
                });
                if ($capacityAttr) {
                    $capacity = $capacityAttr->value;
                }
                $displayName = $product->name . ($capacity ? ' ' . $capacity : '');

                // Lấy số lượng bán được
                $totalSold = DB::table('order_items')
                    ->where('product_variant_id', $variant->id)
                    ->sum('quantity');

                return [
                    // ✅ Sửa id từ $product->id sang $variant->id
                    'id' => $variant->id,
                    'product_id' => $product->id,
                    'name' => $displayName,
                    'price' => $variant?->price ?? 0,
                    'sale_price' => $variant?->sale_price ?? null,
                    'discount_percent' => ($variant?->price > 0 && $variant?->sale_price)
                        ? round(100 - ($variant->sale_price / $variant->price) * 100)
                        : 0,
                    'image' => $image,
                    'stock_quantity' => $variant?->getSellableStockAttribute() ?? 0,
                    'is_featured' => $product->is_featured,
                    'release_date' => $product->created_at->toDateString(),
                    'sold_quantity' => $totalSold,
                ];
            });

            return response()->json([
                'success' => true,
                'products' => $result
            ]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi thêm sản phẩm vào khối: ' . $e->getMessage());
            return response()->json(['error' => 'Thêm sản phẩm thất bại'], 500);
        }
    }

    public function destroyProductBlock($id)
    {
        try {
            $block = HomepageProductBlock::findOrFail($id);
            $block->products()->detach();
            $block->delete();

            // Cập nhật lại order
            $remainingBlocks = HomepageProductBlock::orderBy('order')->get();
            foreach ($remainingBlocks as $index => $remainingBlock) {
                $remainingBlock->order = $index + 1;
                $remainingBlock->save();
            }

            return response()->json(['message' => '✅ Đã xóa khối sản phẩm thành công']);
        } catch (\Exception $e) {
            return response()->json(['message' => '❌ Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function toggleBlockVisibility($id)
    {
        try {
            $block = HomepageProductBlock::findOrFail($id);
            $block->is_visible = !$block->is_visible;
            $block->save();

            return response()->json([
                'success' => true,
                'is_visible' => $block->is_visible,
                'message' => $block->is_visible ? 'Khối đã được hiển thị' : 'Khối đã bị ẩn',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Toggle visibility error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật trạng thái hiển thị'], 500);
        }
    }

    // HomepageController.php
    // HomepageController.php
    public function updateBlockOrder(Request $request)
    {
        $blockIds = $request->input('block_ids', []);
        DB::beginTransaction();
        try {
            foreach ($blockIds as $index => $blockId) {
                DB::table('homepage_product_blocks')
                    ->where('id', $blockId)
                    ->update(['order' => $index + 1]);
            }
            DB::commit();
            return response()->json(['message' => 'Cập nhật thứ tự khối sản phẩm thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi cập nhật thứ tự khối sản phẩm'], 500);
        }
    }

    public function updateProductOrder(Request $request, $blockId)
    {
        // ✅ Nhận ID của biến thể sản phẩm từ request
        $variantIds = $request->input('product_variant_ids', []);

        DB::beginTransaction();
        try {
            // ✅ Lặp qua mảng variant IDs
            foreach ($variantIds as $index => $variantId) {
                DB::table('homepage_block_product')
                    ->where('block_id', $blockId)
                    // ✅ Sửa điều kiện where để tìm theo 'product_variant_id'
                    ->where('product_variant_id', $variantId)
                    ->update(['order' => $index + 1]);
            }
            DB::commit();
            return response()->json(['message' => 'Cập nhật thứ tự sản phẩm thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi cập nhật thứ tự sản phẩm'], 500);
        }
    }

    public function updateBannerOrder(Request $request)
    {
        $bannerIds = $request->input('banner_ids', []);
        foreach ($bannerIds as $index => $id) {
            Banner::where('id', $id)->update(['order' => $index + 1]);
        }
        return response()->json(['message' => 'Cập nhật thứ tự banner thành công']);
    }
}
