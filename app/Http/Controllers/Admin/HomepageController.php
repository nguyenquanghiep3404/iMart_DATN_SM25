<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
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
            'products' => function ($query) {
                $query->with(['variants' => function ($q) {
                    $q->where('is_default', true)->with('primaryImage');
                }])->orderBy('pivot_order');
            }
        ])->orderBy('order')->get();

        // Dữ liệu cho JS
        $productBlocksForJs = $productBlocks->map(function ($block) {
            return [
                'id' => $block->id,
                'title' => $block->title,
                'order' => $block->order,
                'is_visible' => $block->is_visible,
                'products' => $block->products->map(function ($product) {
                    $variant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();
                    $image = $variant && $variant->primaryImage
                        ? asset('storage/' . $variant->primaryImage->path)
                        : '/images/no-image.png';
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $image, // Đồng bộ với searchProducts và addProductsToBlock
                        'price' => $variant?->price ? (string) $variant->price : null,
                        'sale_price' => $variant?->sale_price ? (string) $variant->sale_price : null,
                        'discount_percent' => ($variant && $variant->price > 0 && $variant->sale_price)
                            ? round(100 - ($variant->sale_price / $variant->price) * 100)
                            : 0,
                    ];
                })->toArray()
            ];
        })->toArray();

        return view('admin.homepage.index', [
            'banners' => [], // chưa làm
            'categories' => $categories,
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

            $productsQuery = Product::with(['variants.primaryImage'])
                ->where('status', 'published');

            // Nếu có từ khóa tìm kiếm thì thêm điều kiện LIKE
            if ($query) {
                $productsQuery->where('name', 'like', '%' . $query . '%');
            }

            // Áp dụng bộ lọc nếu có
            if ($filter === 'top_selling') {
                $products = Product::with([
                    'variants' => function ($q) {
                        $q->where('is_default', true)->with('primaryImage');
                    }
                ])
                    ->where('status', 'published')
                    ->withSum(['variants as sold_quantity' => function ($q) {
                        $q->where('is_default', true)
                            ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id');
                    }], 'order_items.quantity')
                    ->orderByDesc('sold_quantity')
                    ->take(10)
                    ->get();
            } elseif ($filter === 'featured') {
                // Top sản phẩm nổi bật
                $products = $productsQuery->where('is_featured', true)
                    ->latest()
                    ->take(10)
                    ->get();
            } elseif ($filter === 'latest_10') {
                // Top 10 sản phẩm mới nhất
                $products = $productsQuery->latest()->take(10)->get();
            } else {
                // Không có filter: lấy tối đa 20 sản phẩm phù hợp
                $products = $productsQuery->take(20)->get();
            }

            // Map dữ liệu sang định dạng chuẩn cho frontend
            $result = $products->map(function ($product) {
                $variant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();

                $image = $variant && $variant->primaryImage
                    ? asset('storage/' . $variant->primaryImage->path)
                    : 'https://via.placeholder.com/300x300?text=No+Image';

                // Ưu tiên dùng sold_quantity từ withSum nếu có
                $totalSold = $product->sold_quantity ?? DB::table('order_items')
                    ->where('product_variant_id', $variant?->id)
                    ->sum('quantity');

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $variant?->price ?? 0,
                    'sale_price' => $variant?->sale_price ?? null,
                    'discount_percent' => ($variant && $variant->price > 0 && $variant->sale_price)
                        ? round(100 - ($variant->sale_price / $variant->price) * 100)
                        : 0,
                    'image' => $image,
                    'stock_quantity' => $variant?->stock_quantity ?? 0,
                    'is_featured' => $product->is_featured,
                    'release_date' => $product->created_at->toDateString(),
                    'sold_quantity' => $totalSold,
                ];
            });

            return response()->json($result);
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi tìm sản phẩm: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi tìm sản phẩm.'], 500);
        }
    }




    public function addProductsToBlock(Request $request, HomepageProductBlock $block)
    {
        try {
            $productIds = $request->input('product_ids', []);

            if (empty($productIds)) {
                return response()->json(['error' => 'Không có sản phẩm nào được chọn'], 422);
            }

            // Lấy order lớn nhất hiện tại
            $maxOrder = $block->products()->max('homepage_block_product.order') ?? 0;

            foreach ($productIds as $i => $productId) {
                // Tránh thêm trùng
                if (!$block->products->contains($productId)) {
                    $block->products()->attach($productId, [
                        'order' => $maxOrder + $i + 1,
                    ]);
                }
            }

            // Tải lại sản phẩm với ảnh và thông tin cần thiết
            $products = $block->products()
                ->with([
                    'variants' => function ($q) {
                        $q->where('is_default', true)->with('primaryImage');
                    }
                ])
                ->orderBy('homepage_block_product.order')
                ->get()
                ->map(function ($product) {
                    $variant = $product->variants->first();

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $variant?->price ?? 0,
                        'sale_price' => $variant?->sale_price ?? null,
                        'discount_percent' => ($variant?->sale_price && $variant?->price > 0)
                            ? round(100 - ($variant->sale_price / $variant->price) * 100)
                            : 0,
                        'image' => $variant?->image_url ?? '/images/no-image.png',
                    ];
                });

            return response()->json([
                'success' => true,
                'products' => $products
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

}
