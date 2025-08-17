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
                        : '';

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

    /**
     * Tạo một khối sản phẩm mới
     */
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

    /**
     * Tìm kiếm sản phẩm theo tên hoặc bộ lọc
     */
    public function searchProducts(Request $request)
    {
        try {
            $query = $request->query('q');
            $filter = $request->query('filter');
            $perPage = $request->query('per_page', 15); // 15 biến thể mỗi trang
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

            // Xử lý bộ lọc
            if ($filter === 'featured') {
                $variantsQuery->whereHas('product', function ($q) {
                    $q->where('is_featured', true);
                });
                // Lấy tất cả biến thể và phân trang
                $variants = $variantsQuery->orderBy('created_at', 'desc')->paginate($perPage);
            } elseif ($filter === 'latest_10') {
                // Lấy tất cả biến thể trước
                $allVariants = $variantsQuery->orderBy('created_at', 'desc')->get();

                // Nhóm biến thể theo product_id và Dung lượng
                $groupedVariants = $allVariants->groupBy(function ($variant) {
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
                })->sortByDesc(function ($variant) {
                    // Sắp xếp theo created_at của sản phẩm
                    return $variant->product->created_at;
                })->values()->take(10); // Lấy 10 mục mới nhất

                // Log số lượng mục sau khi nhóm
                \Log::info('Number of grouped variants for latest_10:', ['count' => $groupedVariants->count()]);

                // Tạo LengthAwarePaginator thủ công
                $totalItems = $groupedVariants->count();
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $groupedVariants->forPage($page, $perPage),
                    $totalItems,
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                // Gán lại $variants để sử dụng ở bước tiếp theo
                $variants = $paginator;
            } else {
                // Bộ lọc mặc định (all) hoặc không có filter
                $variants = $variantsQuery->orderBy('created_at', 'desc')->paginate($perPage);
            }

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

            // Nhóm biến thể theo product_id và Dung lượng (đối với filter != latest_10)
            if ($filter !== 'latest_10') {
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
            }

            // Map dữ liệu sang định dạng chuẩn cho frontend
            $result = $variants->getCollection()->map(function ($variant) {
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
                'links' => $variants->links()->elements,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi tìm sản phẩm: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi tìm sản phẩm.'], 500);
        }
    }

    /**
     * Thêm sản phẩm vào khối sản phẩm
     */
    public function addProductsToBlock(Request $request, HomepageProductBlock $block)
    {
        try {
            // Nhận variant ID từ frontend
            $variantIds = $request->input('product_variant_ids', []);

            // Lấy order lớn nhất hiện tại để thêm sản phẩm mới vào cuối danh sách
            $maxOrder = DB::table('homepage_block_product')
                ->where('block_id', $block->id)
                ->max('order') ?? 0;

            $syncData = [];
            foreach ($variantIds as $i => $variantId) {
                // Tạo mảng dữ liệu để thêm biến thể với order tiếp theo
                $syncData[$variantId] = ['order' => $i + 1]; // Sửa maxOrder + $i + 1 thành $i + 1 để reset order
            }

            // Sử dụng sync thay vì syncWithoutDetaching để xóa các sản phẩm không có trong danh sách
            $block->productVariants()->sync($syncData);

            // Tải lại các biến thể trong block sau khi sync
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

    /**
     * Xóa khối sản phẩm
     */
    public function destroyProductBlock($id)
    {
        try {
            $block = HomepageProductBlock::findOrFail($id);
            // ✅ Sửa products() thành productVariants()
            $block->productVariants()->detach();
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

    /**
     * Cập nhật thứ tự của các khối sản phẩm
     */
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

    /**
     * Cập nhật thứ tự của các banner
     */
    public function updateBannerOrder(Request $request)
    {
        $bannerIds = $request->input('banner_ids', []);
        foreach ($bannerIds as $index => $id) {
            Banner::where('id', $id)->update(['order' => $index + 1]);
        }
        return response()->json(['message' => 'Cập nhật thứ tự banner thành công']);
    }

    /**
     * Xóa sản phẩm khỏi khối
     */
    public function removeProductFromBlock($blockId, $variantId)
    {
        try {
            // Kiểm tra khối sản phẩm
            $block = HomepageProductBlock::find($blockId);
            if (!$block) {
                return response()->json([
                    'success' => false,
                    'message' => 'Khối sản phẩm không tồn tại'
                ], 404);
            }

            // Kiểm tra biến thể sản phẩm
            $variant = ProductVariant::find($variantId);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại'
                ], 404);
            }

            // Kiểm tra xem biến thể có thuộc khối không
            if (!$block->productVariants()->where('product_variant_id', $variantId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không thuộc khối này'
                ], 404);
            }

            // Gỡ bỏ sản phẩm biến thể
            $block->productVariants()->detach($variantId);

            // Cập nhật order của các sản phẩm còn lại
            $remainingVariants = $block->productVariants()->orderBy('pivot_order')->get();
            foreach ($remainingVariants as $index => $remainingVariant) {
                $block->productVariants()->updateExistingPivot($remainingVariant->id, ['order' => $index + 1]);
            }

            return response()->json([
                'success' => true,
                'message' => '✅ Đã xóa sản phẩm khỏi khối thành công'
            ]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi xóa sản phẩm khỏi khối: BlockID=' . $blockId . ', VariantID=' . $variantId . ', Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật thứ tự của các khối sản phẩm
     */
    public function updateBlockOrder(Request $request)
    {
        try {
            $blockIds = $request->input('block_ids', []);
            if (empty($blockIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Danh sách ID khối không hợp lệ'
                ], 400);
            }

            // Kiểm tra tất cả blockIds có tồn tại
            $existingBlocks = HomepageProductBlock::whereIn('id', $blockIds)->pluck('id')->toArray();
            if (count($existingBlocks) !== count($blockIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Một hoặc nhiều khối không tồn tại'
                ], 404);
            }

            // Cập nhật thứ tự
            foreach ($blockIds as $index => $blockId) {
                HomepageProductBlock::where('id', $blockId)->update(['order' => $index + 1]);
            }

            return response()->json([
                'success' => true,
                'message' => '✅ Cập nhật thứ tự khối sản phẩm thành công'
            ]);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi cập nhật thứ tự khối sản phẩm: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật tiêu đề của khối sản phẩm
     */
    public function updateBlockTitle(Request $request, $id)
    {
        $block = HomepageProductBlock::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $block->title = $validated['title'];
        $block->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tiêu đề thành công',
            'title' => $block->title
        ]);
    }
}
