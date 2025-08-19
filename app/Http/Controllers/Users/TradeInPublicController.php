<?php

namespace App\Http\Controllers\Users;

use App\Models\Product;
use App\Models\Category;
use App\Models\TradeInItem;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class TradeInPublicController extends Controller
{
    public function index(Request $request)
    {
        $parentCategories = Category::whereNull('parent_id')->get();

        // Các thuộc tính KHÔNG dùng để gom nhóm (ví dụ: Màu sắc)
        $excludedAttributes = ['Màu sắc', 'Màu', 'Color'];

        foreach ($parentCategories as $category) {
            $categoryIds = $category->children()->pluck('id')->toArray();
            $categoryIds[] = $category->id;

            $tradeInItems = TradeInItem::with([
                'productVariant.product',
                'productVariant.product.variants.primaryImage',
                'productVariant.product.variants.images',
                'productVariant.attributeValues.attribute',
                'productVariant.primaryImage',
                'images'
            ])
                ->where('status', 'available')
                ->whereHas('productVariant.product', function ($query) use ($categoryIds) {
                    $query->whereIn('category_id', $categoryIds);
                })
                ->latest()
                ->get();

            // Gom nhóm theo product_id + các thuộc tính (trừ màu sắc)
            $groupedItems = $tradeInItems->groupBy(function ($item) use ($excludedAttributes) {
                $variant = $item->productVariant;
                $productId = $variant->product_id;

                $filteredAttributes = $variant->attributeValues
                    ->filter(function ($attrValue) use ($excludedAttributes) {
                        return !in_array($attrValue->attribute->name, $excludedAttributes);
                    })
                    ->sortBy(fn($val) => $val->attribute->name)
                    ->map(fn($val) => $val->value)
                    ->implode('_');

                return $productId . '_' . $filteredAttributes;
            });

            // Lấy 1 item đại diện mỗi nhóm và gán ảnh chính theo biến thể mặc định
            $category->tradeInItems = $groupedItems->map(function ($group) {
                $firstItem = $group->first();
                $firstItem->item_count = $group->count();

                $product = $firstItem->productVariant->product;

                $defaultVariant = $product->variants->firstWhere('is_default', true);

                $firstItem->main_image_url = optional($defaultVariant?->primaryImage)?->url
                    ?? optional($defaultVariant?->images->first())?->url
                    ?? asset('assets/admin/img/placeholder-image.png');

                return $firstItem;
            })->values(); // reset key để foreach trong view không lỗi
        }

        return view('users.trade_in.index', compact('parentCategories'));
    }

    public function category($slug)
    {
        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->firstOrFail();

        // Lấy ID của danh mục và danh mục con (nếu có)
        $categoryIds = $category->children()->pluck('id')->toArray();
        $categoryIds[] = $category->id;

        // Các thuộc tính KHÔNG dùng để gom nhóm (màu sắc)
        $excludedAttributes = ['Màu', 'Màu sắc', 'Color'];

        // Lấy giá trị type từ request
        $typeParam = request()->input('type');

        // Xây dựng truy vấn cơ bản
        $query = TradeInItem::with([
            'productVariant.product.variants.primaryImage',
            'productVariant.product.variants.images',
            'productVariant.product',
            'productVariant.attributeValues.attribute',
            'productVariant.primaryImage',
            'images',
        ])
            ->where('status', 'available')
            ->whereHas('productVariant.product', function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
            });

        // Ánh xạ type từ số sang giá trị của trường type
        $typeMap = [
            '4' => 'open_box', // tgdd
            '5' => 'used',     // trade-in
        ];

        // Thêm điều kiện lọc theo type nếu có
        if ($typeParam && array_key_exists($typeParam, $typeMap)) {
            $query->where('type', $typeMap[$typeParam]);
        }

        // Lấy tất cả sản phẩm
        $allItems = $query->latest()->get();

        // Gom nhóm theo product_id + thuộc tính (trừ màu)
        $grouped = $allItems->groupBy(function ($item) use ($excludedAttributes) {
            $variant = $item->productVariant;
            $productId = $variant->product_id;

            $filteredAttributes = $variant->attributeValues
                ->filter(function ($attrValue) use ($excludedAttributes) {
                    return !in_array($attrValue->attribute->name, $excludedAttributes);
                })
                ->sortBy(fn($val) => $val->attribute->name)
                ->map(fn($val) => $val->value)
                ->implode('_');

            return $productId . '_' . $filteredAttributes;
        });

        // Map từng nhóm thành 1 item duy nhất
        $groupedItems = $grouped->map(function ($group) {
            $firstItem = $group->first();
            $firstItem->item_count = $group->count();

            $product = $firstItem->productVariant->product;
            $defaultVariant = $product->variants->firstWhere('is_default', true);

            $firstItem->main_image_url = optional($defaultVariant?->primaryImage)?->url
                ?? optional($defaultVariant?->images->first())?->url
                ?? asset('assets/admin/img/placeholder-image.png');

            return $firstItem;
        })->values();

        // Phân trang thủ công từ Collection
        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedItems = $groupedItems->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $tradeInItems = new LengthAwarePaginator(
            $pagedItems,
            $groupedItems->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('users.trade_in.category', compact('category', 'tradeInItems'));
    }


    public function show($categorySlug, $productSlug)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $categoryIds = $category->children()->pluck('id')->push($category->id);

        $typeParam = request()->input('type');
        $excludedAttributes = ['Màu', 'Màu sắc', 'Color']; // thuộc tính được phép khác nhau

        $query = TradeInItem::with([
            'productVariant.product.category',
            'productVariant.attributeValues.attribute',
            'images',
            'storeLocation'
        ])
            ->where('status', 'available')
            ->whereHas('productVariant.product', function ($q) use ($productSlug, $categoryIds) {
                $q->where('slug', $productSlug)
                    ->whereIn('category_id', $categoryIds);
            });

        $typeMap = [
            '4' => 'open_box',
            '5' => 'used',
        ];

        if ($typeParam && array_key_exists($typeParam, $typeMap)) {
            $query->where('type', $typeMap[$typeParam]);
        }

        $allItems = $query->latest()->get();
        abort_if($allItems->isEmpty(), 404);

        // ✅ Lấy biến thể đầu tiên làm chuẩn so sánh
        $baseItem = $allItems->first();
        $baseAttributes = collect($baseItem->productVariant->attributeValues)
            ->filter(fn($val) => $val->attribute && !in_array($val->attribute->name, $excludedAttributes))
            ->mapWithKeys(fn($val) => [$val->attribute->name => $val->value]);

        // ✅ Lọc các item giống y như base, trừ thuộc tính màu sắc
        $filteredItems = $allItems->filter(function ($item) use ($baseAttributes, $excludedAttributes) {
            $attributes = collect($item->productVariant->attributeValues)
                ->filter(fn($val) => $val->attribute && !in_array($val->attribute->name, $excludedAttributes))
                ->mapWithKeys(fn($val) => [$val->attribute->name => $val->value]);

            return $attributes == $baseAttributes;
        })->values();

        // ✅ Phân trang thủ công
        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedItems = $filteredItems->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $tradeInItems = new LengthAwarePaginator(
            $pagedItems,
            $filteredItems->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $productName = optional($tradeInItems->first()->productVariant->product)->name;

        return view('users.trade_in.show', compact('tradeInItems', 'productName', 'category', 'categorySlug', 'productSlug'));
    }


    public function detail($category, $product, Request $request)
    {
        $tradeInId = $request->query('oldid');

        if (!$tradeInId) {
            Log::error('Missing oldid parameter', ['url' => $request->fullUrl()]);
            abort(404, 'Thiếu tham số oldid.');
        }

        // Kiểm tra danh mục (có thể là danh mục cha)
        $category = Category::where('slug', $category)->first();
        if (!$category) {
            Log::error('Category not found', ['slug' => $category]);
            abort(404, 'Không tìm thấy danh mục.');
        }

        // Lấy danh mục cha và danh mục con
        $categoryIds = $category->children()->pluck('id')->push($category->id);

        // Kiểm tra sản phẩm thuộc danh mục cha hoặc danh mục con
        $product = Product::where('slug', $product)
            ->whereIn('category_id', $categoryIds)
            ->first();
        if (!$product) {
            Log::error('Product not found', ['slug' => $product, 'category_ids' => $categoryIds]);
            abort(404, 'Không tìm thấy sản phẩm.');
        }

        // Tìm item theo ID và nạp quan hệ cần thiết
        $tradeInItem = TradeInItem::with([
            'productVariant.product',
            'productVariant.specifications.group',
            'productVariant.primaryImage',
            'productVariant.images',
            'storeLocation'
        ])
            ->whereHas('productVariant.product', function ($query) use ($product) {
                $query->where('id', $product->id);
            })
            ->find($tradeInId);
        if (!$tradeInItem) {
            Log::error('TradeInItem not found', ['id' => $tradeInId, 'product_id' => $product->id]);
            abort(404, 'Không tìm thấy sản phẩm cũ.');
        }

        // Format specifications
        $specifications = $tradeInItem->productVariant->specifications
            ->groupBy(fn($spec) => $spec->group->name)
            ->map(fn($group) => $group->sortBy('order')->map(fn($spec) => [
                'name' => $spec->name,
                'value' => $spec->pivot->value,
                'type' => $spec->type,
            ]))
            ->sortBy(fn($group, $key) => \App\Models\SpecificationGroup::where('name', $key)->first()->order ?? 0);

        // Lấy mô tả chi tiết từ cột description
        $description = $tradeInItem->productVariant->product->description;
        $productName = $product->name;

        Log::info('Detail Request', [
            'category' => $category->slug,
            'product' => $product->slug,
            'oldid' => $tradeInId,
            'tradeInItem' => $tradeInItem->toArray(),
            'specifications' => $specifications->toArray(),
            'description' => $description,
        ]);

        return view('users.trade_in.detail', compact('tradeInItem', 'specifications', 'description', 'category', 'productName'))
            ->with('categorySlug', $category->slug)
            ->with('productSlug', $product->slug);
    }
}
