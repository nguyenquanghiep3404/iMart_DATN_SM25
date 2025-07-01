<?php

namespace App\Http\Controllers\Users;

use Carbon\Carbon;

use App\Models\Post;


use App\Models\Banner;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

use App\Models\PostCategory;


use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;


class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::with('desktopImage')
            ->where('status', 'active')
            ->orderBy('order')
            ->get();

        // Hàm xử lý đánh giá và phần trăm giảm giá
        $calculateAverageRating = function ($products) {
            foreach ($products as $product) {
                $averageRating = $product->reviews->avg('rating') ?? 0;
                $product->average_rating = round($averageRating, 1);

                $now = now();
                $variant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();

                if ($variant) {
    $isOnSale = false;

    if ($variant->sale_price && $variant->price > 0) {
        $isOnSale = true;
    }

    $variant->discount_percent = $isOnSale
        ? round(100 - ($variant->sale_price / $variant->price) * 100)
        : 0;
}

            }
        };

        // === Danh sách sản phẩm nổi bật ===
        $featuredProducts = Product::with([
            'category',
            'coverImage',
            'galleryImages',
            'variants.primaryImage', // 👈 Load primaryImage
            'variants.images',        // 👈 Load images của variant nếu có
            'reviews' => function ($query) {
                $query->where('reviews.status', 'approved');
            }
        ])
            ->withCount([
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('reviews.status', 'approved');
                }
            ])
            ->where('is_featured', 1)
            ->where('status', 'published')
            ->where(function ($query) {
                $query->where('type', 'simple')
                    ->orWhereHas('variants', function ($q) {
                        $q->whereNull('deleted_at');
                    });
            })
            ->latest()
            ->take(8)
            ->get();

        // Áp dụng tính toán
        $calculateAverageRating($featuredProducts);

        // === Danh sách sản phẩm mới nhất ===
        $latestProducts = Product::with([
            'category',
            'coverImage',
            'galleryImages',
            'variants.primaryImage',
            'variants.images',
            'reviews' => function ($query) {
                $query->where('reviews.status', 'approved');
            }
        ])
            ->withCount([
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('reviews.status', 'approved');
                }
            ])
            ->where('status', 'published')
            ->where(function ($query) {
                $query->where('type', 'simple')
                    ->orWhereHas('variants', function ($q) {
                        $q->whereNull('deleted_at');
                    });
            })
            ->latest()
            ->take(8)
            ->get();

        // Tính rating & discount
        $calculateAverageRating($latestProducts);

        return view('users.home', compact('featuredProducts', 'latestProducts', 'banners'));
    }





    public function show($slug)
    {
        $product = Product::with([
            'category',
            'coverImage',
            'galleryImages',
            'variants.attributeValues.attribute',
            'variants.images' => function ($query) {
                $query->where('type', 'variant_image')->orderBy('order');
            },
            'variants.primaryImage',
            'reviews' => function ($query) {
                $query->where('reviews.status', 'approved');
            },
        ])
            ->withCount([
                'reviews as reviews_count' => function ($query) {
                    $query->where('reviews.status', 'approved');
                }
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $product->increment('view_count');

        $averageRating = $product->reviews->avg('rating') ?? 0;
        $product->average_rating = round($averageRating, 1);

        $ratingCounts = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingCounts[$i] = $product->reviews->where('rating', $i)->count();
        }

        $totalReviews = $product->reviews_count;

        $ratingPercentages = [];
        foreach ($ratingCounts as $star => $count) {
            $ratingPercentages[$star] = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
        }

        $variantData = [];
        $attributes = [];
        $availableCombinations = [];

        $defaultVariant = $product->variants->firstWhere('is_default', true);

        // ✅ Lấy thứ tự thuộc tính dựa trên attribute_id tăng dần
        $attributeOrder = $product->variants
            ->flatMap(fn($variant) => $variant->attributeValues)
            ->sortBy(fn($attrValue) => $attrValue->attribute->id)
            ->pluck('attribute.name')
            ->unique()
            ->values();

        foreach ($product->variants as $variant) {
            $combination = [];
            foreach ($variant->attributeValues as $attrValue) {
                $attrName = $attrValue->attribute->name;
                $value = $attrValue->value;
                $combination[$attrName] = $value;

                if (!isset($attributes[$attrName])) {
                    $attributes[$attrName] = collect();
                }
                if (!$attributes[$attrName]->contains('value', $value)) {
                    $attributes[$attrName]->push($attrValue);
                }
            }

            $availableCombinations[] = $combination;

            $now = now();
            $salePrice = (int) $variant->sale_price;
            $originalPrice = (int) $variant->price;

            $hasFlashTime = !empty($variant->sale_price_starts_at) && !empty($variant->sale_price_ends_at);
            $isFlashSale = false;
            if ($salePrice && $hasFlashTime) {
                $isFlashSale = $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);
            }
            $isSale = !$isFlashSale && $salePrice && $salePrice < $originalPrice;

            $displayPrice = $isFlashSale || $isSale ? $salePrice : $originalPrice;
            $displayOriginalPrice = ($isFlashSale || $isSale) && $originalPrice > $salePrice ? $originalPrice : null;

            // ✅ Tạo variantKey theo đúng thứ tự trong $attributeOrder
            $variantKey = [];
            foreach ($attributeOrder as $attrName) {
                $attrValue = $variant->attributeValues->firstWhere('attribute.name', $attrName);
                $variantKey[] = $attrValue?->value ?? '';
            }
            $variantKeyStr = implode('_', $variantKey);

            $images = $variant->images->map(fn($image) => Storage::url($image->path))->toArray();
            if (empty($images)) {
                $images = [asset('images/placeholder.jpg')];
            }
            $mainImage = $variant->primaryImage ? Storage::url($variant->primaryImage->path) : ($images[0] ?? null);
            
            $variantData[$variantKeyStr] = [
                'price' => $originalPrice,
                'sale_price' => $salePrice,
                'sale_price_starts_at' => $variant->sale_price_starts_at,
                'sale_price_ends_at' => $variant->sale_price_ends_at,
                'display_price' => $displayPrice,
                'display_original_price' => $displayOriginalPrice,
                'status' => $variant->status,
                'image' => $mainImage,
                'images' => $images,
                'primary_image_id' => $variant->primary_image_id,
                'variant_id' => $variant->id,
            ];
        }

        $relatedProducts = Product::with(['category', 'coverImage'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->take(4)
            ->get();

        $comments = $product->comments()
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->orderByDesc('created_at')
            ->get();

        $initialVariantAttributes = [];
        if ($defaultVariant) {
            foreach ($defaultVariant->attributeValues as $attrValue) {
                $initialVariantAttributes[$attrValue->attribute->name] = $attrValue->value;
            }
        }
        $attributesGrouped = collect($attributes)->map(fn($values) => $values->sortBy('value')->values());

        $variantCombinations = $availableCombinations;
        return view('users.show', compact(
            'product',
            'relatedProducts',
            'ratingCounts',
            'ratingPercentages',
            'totalReviews',
            'attributes',
            'variantData',
            'availableCombinations',
            'defaultVariant',
            'comments',
            'attributeOrder',
            'initialVariantAttributes',
            'variantCombinations',
            'attributesGrouped'
        ));
    }



    public function allProducts(Request $request, $id = null, $slug = null)
    {
        $now = Carbon::now();

        // Lấy danh mục hiện tại nếu có
        $currentCategory = null;
        if ($id) {
            $currentCategory = Category::with('parent')->findOrFail($id);

            if ($slug !== Str::slug($currentCategory->name)) {
                return redirect()->route('products.byCategory', [
                    'id' => $currentCategory->id,
                    'slug' => Str::slug($currentCategory->name),
                ]);
            }
        } else {
            // Reset $currentCategory nếu không có $id
            // Session::forget('current_category'); // Bỏ nếu không dùng session
        }

        $query = Product::with([
            'category',
            'coverImage',
            'variants' => function ($query) use ($now, $request) {
                if ($request->sort === 'dang_giam_gia') {
                    $query->where('sale_price', '>', 0)
                        ->where('sale_price_starts_at', '<=', $now)
                        ->where('sale_price_ends_at', '>=', $now)
                        ->whereNull('deleted_at')
                        ->orderBy('id');
                } else {
                    $query->where(function ($q) {
                        $q->where('is_default', true)
                            ->orWhereRaw('id = (
                            select min(id) 
                            from product_variants pv 
                            where pv.product_id = product_variants.product_id 
                            and pv.deleted_at is null
                        )');
                    })->whereNull('deleted_at');
                }
            },
            'reviews' => fn($q) => $q->where('reviews.status', 'approved')
        ])
            ->withCount([
                'reviews as approved_reviews_count' => fn($q) => $q->where('reviews.status', 'approved')
            ])
            ->where('status', 'published');

        // 🔍 Tìm kiếm
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 🗂 Lọc theo danh mục và con
        if ($currentCategory) {
            $categoryIds = Category::where('parent_id', $currentCategory->id)->pluck('id')->toArray();
            $categoryIds[] = $currentCategory->id;
            $query->whereIn('category_id', $categoryIds);
        }

        // ⭐ Lọc đánh giá
        if ($request->filled('rating')) {
            $rating = (int) $request->rating;
            $query->whereHas('reviews', fn($q) => $q->where('reviews.status', 'approved'))
                ->withAvg(['reviews as approved_reviews_avg_rating' => fn($q) => $q->where('reviews.status', 'approved')], 'rating')
                ->having('approved_reviews_avg_rating', '>=', $rating);
        }

        // 💰 Lọc giá
        if ($request->filled('min_price')) {
            $query->whereHas('variants', fn($q) => $q->where('price', '>=', $request->min_price));
        }
        if ($request->filled('max_price')) {
            $query->whereHas('variants', fn($q) => $q->where('price', '<=', $request->max_price));
        }

        // 🔃 Sắp xếp
        switch ($request->sort) {
            case 'moi_nhat':
                $query->where('created_at', '>=', $now->copy()->subWeek())->orderByDesc('created_at');
                break;

            case 'gia_thap_den_cao':
                $query->whereHas('variants', fn($q) => $q->whereNull('deleted_at'))
                    ->orderBy(fn($q) => $q->select('price')
                        ->from('product_variants')
                        ->whereColumn('product_id', 'products.id')
                        ->whereNull('deleted_at')
                        ->where(function ($q2) {
                            $q2->where('is_default', true)
                                ->orWhereRaw('id = (
                                select min(id) 
                                from product_variants pv 
                                where pv.product_id = product_variants.product_id 
                                and pv.deleted_at is null
                            )');
                        })->limit(1));
                break;

            case 'gia_cao_den_thap':
                $query->whereHas('variants', fn($q) => $q->whereNull('deleted_at'))
                    ->orderByDesc(fn($q) => $q->select('price')
                        ->from('product_variants')
                        ->whereColumn('product_id', 'products.id')
                        ->whereNull('deleted_at')
                        ->where(function ($q2) {
                            $q2->where('is_default', true)
                                ->orWhereRaw('id = (
                                select min(id) 
                                from product_variants pv 
                                where pv.product_id = product_variants.product_id 
                                and pv.deleted_at is null
                            )');
                        })->limit(1));
                break;

            case 'dang_giam_gia':
                $query->whereHas('variants', fn($q) => $q->whereNotNull('sale_price')
                    ->where('sale_price', '>', 0)
                    ->where('sale_price_starts_at', '<=', $now)
                    ->where('sale_price_ends_at', '>=', $now))
                    ->orderByDesc('created_at');
                break;

            case 'noi_bat':
                $query->where('is_featured', 1)->orderByDesc('created_at');
                break;

            default:
                $query->orderByDesc('created_at');
                break;
        }

        $products = $query->paginate(12);

        // 🎯 Tính rating + giảm giá
        foreach ($products as $product) {
            $product->average_rating = round($product->reviews->avg('rating') ?? 0, 1);
            $variant = $product->variants->first();
            if ($variant && $variant->sale_price && $variant->sale_price_starts_at && $variant->sale_price_ends_at) {
                $onSale = $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);
                $variant->discount_percent = $onSale
                    ? round(100 - ($variant->sale_price / $variant->price) * 100)
                    : 0;
            }
        }


        // 📚 Lấy tất cả danh mục hoạt động (tạm thời disable chức năng show_on_homepage)
        // $categories = Category::where('show_on_homepage', true)
        //    ->where('status', 'active')
        //    ->get();
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get();

        $currentCategory = $categoryId ? $category : null;
        $categories = Category::all();
        $parentCategories = $categories->whereNull('parent_id');


        if ($request->ajax()) {
            return response()->json([
                'sidebar' => view('users.partials.category_product.product_sidebar', compact('categories', 'parentCategories', 'currentCategory'))->render(),
                'products' => view('users.partials.category_product.shop_products', compact('products'))->render(),
                'title' => $currentCategory ? $currentCategory->name : 'Tất cả sản phẩm',
                'breadcrumb_html' => view('users.partials.category_product.breadcrumb', compact('categories', 'currentCategory'))->render(),
            ]);
        }


        return view('users.shop', compact('products', 'categories', 'parentCategories', 'currentCategory'));
    }


    /**
     * Hiển thị trang About , Help, Terms
     */
    public function about()
    {
        return view('users.about');
    }

    /**
     */
    public function help()
    {
        // Lấy danh mục "Trung Tâm Trợ Giúp" (ID = 19)
        $helpCategoryId = 19;
        // Lấy tất cả danh mục con của "Trung Tâm Trợ Giúp"
        $helpCategories = PostCategory::where('parent_id', $helpCategoryId)
            ->orderBy('name')
            ->get();
        // Lấy bài viết cho từng danh mục con
        $helpData = [];
        foreach ($helpCategories as $category) {
            $posts = Post::where('post_category_id', $category->id)
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->get();
            if ($posts->count() > 0) {
                $helpData[] = [
                    'category' => $category,
                    'posts' => $posts
                ];
            }
        }
        return view('users.help', compact('helpData'));
    }

    public function helpAnswer($slug)
    {
        // Tìm bài viết theo slug
        $post = Post::with(['postCategory', 'user', 'coverImage'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
        // Lấy các bài viết liên quan cùng danh mục
        $relatedPosts = Post::where('post_category_id', $post->post_category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->limit(5)
            ->get();
        return view('users.help-answer', compact('post', 'relatedPosts'));
    }

    public function terms()
    {
        // Lấy bài viết "Điều khoản và điều kiện" từ database
        $termsPost = Post::with(['coverImage', 'user'])
            ->where('id', 41) // ID của bài viết "Điều khoản và điều kiện"
            ->where('status', 'published')
            ->first();
        // Nếu không tìm thấy bài viết, fallback về view cũ
        if (!$termsPost) {
            return view('users.terms');
        }
        return view('users.terms', compact('termsPost'));
    }
    public function compareSuggestions(Request $request)
{
    $variantId = $request->input('variant_id');

    $variant = ProductVariant::with(['product.category.parent', 'attributeValues.attribute'])->findOrFail($variantId);
    $product = $variant->product;

    // Lấy danh mục cha (ví dụ: "Điện thoại")
    $parentCategoryId = $product->category->parent_id ?? $product->category->id;

    $currentPrice = $variant->sale_price ?: $variant->price;

    $suggestedProducts = Product::with(['variants', 'coverImage'])
        ->whereHas('category', function ($query) use ($parentCategoryId) {
            $query->where('parent_id', $parentCategoryId)
                  ->orWhere('id', $parentCategoryId);
        })
        ->where('id', '!=', $product->id)
        ->where('status', 'published')
        ->get()
        ->filter(function ($p) use ($currentPrice) {
            return $p->variants->contains(function ($v) use ($currentPrice) {
                $price = $v->sale_price ?: $v->price;
                return abs($price - $currentPrice) <= 3000000; // Chênh lệch tối đa 3 triệu
            });
        })
        ->take(5)
        ->values();

    return response()->json([
        'suggested' => $suggestedProducts,
    ]);
}
}