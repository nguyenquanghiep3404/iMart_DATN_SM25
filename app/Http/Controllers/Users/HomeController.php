<?php

namespace App\Http\Controllers\Users;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Banner;
use App\Models\Comment;
use Illuminate\Support\Str;


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

                if (
                    $variant->sale_price &&
                    $variant->sale_price_starts_at &&
                    $variant->sale_price_ends_at &&
                    $variant->price > 0
                ) {
                    try {
                        $startDate = \Carbon\Carbon::parse($variant->sale_price_starts_at);
                        $endDate = \Carbon\Carbon::parse($variant->sale_price_ends_at);
                        $isOnSale = $now->between($startDate, $endDate);
                    } catch (\Exception $e) {
                        \Log::error('Lỗi phân tích ngày tháng: ' . $e->getMessage());
                    }
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
        ->flatMap(fn ($variant) => $variant->attributeValues)
        ->sortBy(fn ($attrValue) => $attrValue->attribute->id)
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
        $isOnSale = $variant->sale_price !== null &&
            $variant->sale_price_starts_at <= $now &&
            $variant->sale_price_ends_at >= $now;
        $displayPrice = $isOnSale ? $salePrice : $originalPrice;

        // ✅ Tạo variantKey theo đúng thứ tự trong $attributeOrder
        $variantKey = [];
        foreach ($attributeOrder as $attrName) {
            $attrValue = $variant->attributeValues->firstWhere('attribute.name', $attrName);
            $variantKey[] = $attrValue?->value ?? '';
        }
        $variantKeyStr = implode('_', $variantKey);

        $images = $variant->images->map(fn($image) => Storage::url($image->path))->toArray();
        if (empty($images)) {
            $images = [];
            if ($product->coverImage) {
                $images[] = Storage::url($product->coverImage->path);
            }
            foreach ($product->galleryImages as $galleryImage) {
                $images[] = Storage::url($galleryImage->path);
            }
        }
        $mainImage = $variant->primaryImage ? Storage::url($variant->primaryImage->path) : ($images[0] ?? null);

        $variantData[$variantKeyStr] = [
            'price' => $displayPrice,
            'original_price' => $isOnSale && $originalPrice > $salePrice ? $originalPrice : null,
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
        'attributeOrder' // 👈 THÊM DÒNG NÀY để truyền xuống view
    ));
}




    public function allProducts(Request $request, $id = null, $slug = null)
    {

        $now = Carbon::now();

        // Nếu có ID trong route, thì kiểm tra danh mục và slug
        $categoryId = null;
        if ($id) {
            $category = Category::findOrFail($id);
            $categoryId = $category->id;

            // Nếu slug sai thì redirect về đúng slug
            if ($slug !== Str::slug($category->name)) {
                return redirect()->route('products.byCategory', [
                    'id' => $category->id,
                    'slug' => Str::slug($category->name),
                ]);
            }
        }

        $query = Product::with([
            'category',
            'coverImage',
            'variants' => function ($query) use ($now, $request) {
                if ($request->has('sort') && $request->sort === 'dang_giam_gia') {
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
            'reviews' => function ($query) {
                $query->where('reviews.status', 'approved');
            }
        ])
            ->withCount([
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('reviews.status', 'approved');
                }
            ])
            ->where('status', 'published');

        // 🔍 Tìm kiếm theo tên sản phẩm
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 🗂 Lọc theo danh mục (bao gồm cả con)
        if ($categoryId) {
            $categoryIds = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
            $categoryIds[] = $categoryId;
            $query->whereIn('category_id', $categoryIds);
        }

        // ⭐ Lọc theo đánh giá
        if ($request->filled('rating')) {
            $rating = (int) $request->rating;
            $query->whereHas('reviews', function ($q) {
                $q->where('status', 'approved');
            })->withAvg([
                'reviews as approved_reviews_avg_rating' => function ($q) {
                    $q->where('status', 'approved');
                }
            ], 'rating')->having('approved_reviews_avg_rating', '>=', $rating);
        }

        // 💰 Lọc theo khoảng giá
        if ($request->filled('min_price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }
        if ($request->filled('max_price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        // 🔃 Sắp xếp theo yêu cầu
        switch ($request->sort) {
            case 'moi_nhat':
                $query->where('created_at', '>=', $now->copy()->subWeek())
                    ->orderByDesc('created_at');
                break;

            case 'giá_thấp_đến_cao':
                $query->whereHas('variants', function ($q) {
                    $q->whereNull('deleted_at');
                })->orderBy(function ($q) {
                    $q->select('price')
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
                        })
                        ->limit(1);
                });
                break;

            case 'giá_cao_đến_thấp':
                $query->whereHas('variants', function ($q) {
                    $q->whereNull('deleted_at');
                })->orderByDesc(function ($q) {
                    $q->select('price')
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
                        })
                        ->limit(1);
                });
                break;

            case 'dang_giam_gia':
                $query->whereHas('variants', function ($q) use ($now) {
                    $q->whereNotNull('sale_price')
                        ->where('sale_price', '>', 0)
                        ->where('sale_price_starts_at', '<=', $now)
                        ->where('sale_price_ends_at', '>=', $now);
                })->orderByDesc('created_at');
                break;

            case 'noi_bat':
                $query->where('is_featured', 1)->orderByDesc('created_at');
                break;

            case 'tat_ca':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        // 📄 Phân trang
        $products = $query->paginate(12);

        // 🎯 Tính đánh giá trung bình và % giảm giá
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

        // 📚 Lấy tất cả danh mục (sử dụng ở sidebar hoặc filter)
        $categories = Category::all();

        $currentCategory = $categoryId ? $category : null;

        if ($request->ajax()) {
            return view('users.partials.category_product.shop_products', compact('products'))->render();
        }

        return view('users.shop', compact('products', 'categories', 'currentCategory'));
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
        return view('users.help');
    }
    public function terms()
    {
        return view('users.terms');
    }
}
