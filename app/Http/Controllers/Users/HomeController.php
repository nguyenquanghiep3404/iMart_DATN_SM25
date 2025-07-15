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
use App\Models\HomepageProductBlock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\WishlistItem;
use App\Models\Review;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy danh sách banner
        $banners = Banner::with('desktopImage')
            ->where('status', 'active')
            ->orderBy('order')
            ->get();

        $blocks = HomepageProductBlock::where('is_visible', true)
            ->orderBy('order')
            ->with(['products' => function ($query) {
                $query->where('products.status', 'published') // ✅ fix lỗi ambiguous
                    ->with([
                        'category',
                        'coverImage',
                        'galleryImages',
                        'variants.primaryImage',
                        'variants.images',
                        'reviews' => function ($query) {
                            $query->where('reviews.status', 'approved'); // ✅ fix luôn
                        },
                    ])
                    ->withCount([
                        'reviews as approved_reviews_count' => function ($query) {
                            $query->where('reviews.status', 'approved'); // ✅
                        },
                    ]);
            }])
            ->get();


        // Hàm xử lý đánh giá và phần trăm giảm giá
        $calculateAverageRating = function ($products) {
            foreach ($products as $product) {
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

        // Lấy danh sách sản phẩm nổi bật từ cache hoặc database
        if (auth()->check()) {
            $unreadNotificationsCount = auth()->user()->unreadNotifications()->count();

            $recentNotifications = auth()->user()->notifications()
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'title' => $notification->data['title'] ?? 'Thông báo',
                        'message' => $notification->data['message'] ?? '',
                        'icon' => $notification->data['icon'] ?? 'default',
                        'color' => $notification->data['color'] ?? 'gray',
                        'time' => $notification->created_at->diffForHumans(),
                    ];
                });
        } else {
            $unreadNotificationsCount = 0;
            $recentNotifications = collect();
        }


        $featuredPosts = Post::with('coverImage')
            ->where('status', 'published')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('users.home', compact(
            'featuredProducts',
            'blocks',
            'latestProducts',
            'banners',
            'featuredPosts',
            'unreadNotificationsCount',
            'recentNotifications',
        ));
    }

    public function show(Request $request, $slug)
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

        $variantIds = $product->variants->pluck('id');
        $allReviews = Review::with(['user', 'images'])
            ->whereIn('product_variant_id', $variantIds)
            ->where('status', 'approved')
            ->get();
        // 2. Lấy TẤT CẢ bình luận (comments) gốc đã được duyệt
        // (Giữ nguyên logic query comment phức tạp của bạn)
        $allComments = $product->comments()
            ->whereNull('parent_id')
            ->where(function ($query) {
                $query->where('status', 'approved');

                if (Auth::check()) {
                    $query->orWhere(function ($q) {
                        $q->where('user_id', Auth::id())
                            ->whereIn('status', ['pending', 'rejected', 'spam']);
                    });

                    if (Auth::user()->hasRole('admin')) {
                        $query->orWhereIn('status', ['pending', 'rejected', 'spam']);
                    }
                }
            })
            ->with(['user', 'replies.user'])
            ->get();

        // 3. Gộp 2 danh sách lại và chuẩn hóa cấu trúc để sắp xếp
        $combinedList = collect();

        foreach ($allReviews as $review) {
            $combinedList->push((object)[
                'type' => 'review',        // Thêm trường 'type' để phân biệt trong view
                'data' => $review,         // Dữ liệu gốc
                'sort_date' => $review->created_at // Dùng để sắp xếp chung
            ]);
        }

        foreach ($allComments as $comment) {
            $combinedList->push((object)[
                'type' => 'comment',
                'data' => $comment,
                'sort_date' => $comment->created_at
            ]);
        }

        // 4. Sắp xếp danh sách đã gộp theo ngày tạo mới nhất
        $sortedList = $combinedList->sortByDesc('sort_date');

        // 5. Tự tạo phân trang bằng tay
        $perPage = 5; // Số mục trên mỗi trang (ví dụ: 3 bình luận + 2 đánh giá)
        $currentPage = request()->get('page', 1);
        $currentPageItems = $sortedList->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedItems = new LengthAwarePaginator(
            $currentPageItems,
            $sortedList->count(),
            $perPage,
            $currentPage,
            // Giữ lại các query string khác trên URL khi chuyển trang
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Gán lại biến $totalReviews và $commentsCount để hiển thị số lượng đúng
        $totalReviews = $allReviews->count();
        $commentsCount = $allComments->where('status', 'approved')->count();

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

        $variantSpecs = [];
        foreach ($product->variants as $variant) {
            $variantKey = [];
            foreach ($attributeOrder as $attrName) {
                $attrValue = $variant->attributeValues->firstWhere('attribute.name', $attrName);
                $variantKey[] = $attrValue?->value ?? '';
            }
            $variantKeyStr = implode('_', $variantKey);

            $groupedSpecs = [];
            foreach ($variant->specifications as $spec) {
                $groupName = $spec->group->name ?? 'Other';
                $groupedSpecs[$groupName][$spec->name] = $spec->pivot->value;
            }

            $variantSpecs[$variantKeyStr] = $groupedSpecs;
        }


        $relatedProducts = Product::with(['category', 'coverImage'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->take(4)
            ->get();

        // // $comments = $product->comments()
        // //     ->whereNull('parent_id')
        // //     ->where(function ($query) {
        // //         $query->where('status', 'approved');

        //         if (Auth::check()) {
        //             $query->orWhere(function ($q) {
        //                 $q->where('user_id', Auth::id())
        //                     ->whereIn('status', ['pending', 'rejected', 'spam']);
        //             });

        //             if (Auth::user()->hasRole('admin')) {
        //                 $query->orWhereIn('status', ['pending', 'rejected', 'spam']);
        //             }
        //         }
        //     })
        //     ->with(['user', 'replies.user'])
        //     ->orderByDesc('created_at')
        //     ->get();



        $initialVariantAttributes = [];
        if ($defaultVariant) {
            foreach ($defaultVariant->attributeValues as $attrValue) {
                $initialVariantAttributes[$attrValue->attribute->name] = $attrValue->value;
            }
        }
        $attributesGrouped = collect($attributes)->map(fn($values) => $values->sortBy('value')->values());

        $variantCombinations = $availableCombinations;
        // ✅ Lấy thông số kỹ thuật theo nhóm (chỉ lấy từ biến thể mặc định)
        $specGroupsData = [];
        if ($defaultVariant) {
            foreach ($defaultVariant->specifications as $spec) {
                $groupName = $spec->group->name ?? 'Khác';
                $specGroupsData[$groupName][$spec->name] = $spec->pivot->value;
            }
        }
        $userId = Auth::id();
        $wishlistVariantIds = [];

        if ($userId) {
            $wishlistVariantIds = WishlistItem::whereHas('wishlist', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->pluck('product_variant_id')->toArray();
        }
        // lấy variant_id trong query param
        $variantId = $request->query('variant_id');

        // Lấy product, ... (giữ nguyên code load product và quan hệ như cũ)

        // Lấy biến thể mặc định hoặc biến thể theo variant_id
        $defaultVariant = null;
        if ($variantId) {
            $defaultVariant = $product->variants->firstWhere('id', $variantId);
        }
        if (!$defaultVariant) {
            $defaultVariant = $product->variants->firstWhere('is_default', true);
        }
        // ... các phần khác giữ nguyên như bạn có

        // Lấy initialVariantAttributes để view dùng hiển thị đúng biến thể
        $initialVariantAttributes = [];
        if ($defaultVariant) {
            foreach ($defaultVariant->attributeValues as $attrValue) {
                $initialVariantAttributes[$attrValue->attribute->name] = $attrValue->value;
            }
        }
        $commentsCount = $product->comments()
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->count();

        // --- Bắt đầu đoạn code ĐÃ THÊM để lấy order_item_id ---
        $orderItemId = null; // Khởi tạo biến này là null

        $totalReviews = $product->reviews_count ?? 0;
        $averageRating = round($product->reviews->avg('rating') ?? 0, 1);
        $reviewsData = []; // ✅ khai báo để tránh undefined
        $starRatingsCount = [];
        for ($i = 5; $i >= 1; $i--) {
            $starRatingsCount[$i] = $product->reviews->where('rating', $i)->count();
        }
        $hasReviewed = false; // ✅ Khởi tạo mặc định trước

        // Chỉ tìm kiếm order_item_id nếu người dùng đã đăng nhập
        if (Auth::check()) {
            $userId = Auth::id(); // Lấy ID của người dùng hiện tại
            $productVariantIdToFind = null;

            // Ưu tiên variant_id từ query parameter
            if ($request->has('variant_id') && $product->variants->contains('id', $request->query('variant_id'))) {
                $productVariantIdToFind = $request->query('variant_id');
            } elseif ($defaultVariant) {
                $productVariantIdToFind = $defaultVariant->id;
            }

            if ($productVariantIdToFind) { // Đảm bảo có product_variant_id để tìm kiếm
                // Tìm OrderItem mà người dùng hiện tại đã mua của biến thể sản phẩm này.
                // Điều kiện:
                // 1. Phải thuộc về người dùng hiện tại (qua mối quan hệ order.user_id)
                // 2. Phải có product_variant_id tương ứng
                // 3. Đơn hàng phải ở trạng thái "completed" (hoặc trạng thái bạn cho phép đánh giá)
                $orderItem = OrderItem::where('product_variant_id', $productVariantIdToFind)
                    ->whereHas('order', function ($query) use ($userId) {
                        $query->where('user_id', $userId)
                            ->where('status', 'delivered'); // Ví dụ: chỉ cho phép đánh giá khi đơn hàng đã hoàn thành
                    })
                    ->latest() // Lấy order item gần nhất nếu có nhiều
                    ->first();

                if ($orderItem) {
                    $orderItemId = $orderItem->id;
                }
            }
            // Lấy dữ liệu đánh giá
            $variantIds = $product->variants->pluck('id');

            $reviewsData = Review::whereIn('product_variant_id', $variantIds) // <-- CHANGE THIS LINE
                ->where('status', 'approved') // Assuming you only want approved reviews
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();
            $totalReviews = array_sum($reviewsData); // Tính tổng số đánh giá
            if ($totalReviews > 0) {
                $sumRatings = 0;
                foreach ($reviewsData as $rating => $count) {
                    $sumRatings += ($rating * $count);
                }
                $averageRating = $sumRatings / $totalReviews;
            }

            for ($i = 5; $i >= 1; $i--) {
                $starRatingsCount[$i] = $reviewsData[$i] ?? 0;
            }
            $hasReviewed = false;

            if ($orderItemId) {
                $hasReviewed = Review::where('order_item_id', $orderItemId)->exists();
            }
        }
        // --- Kết thúc đoạn code ĐÃ THÊM ---

        return view('users.products.show', compact(
            'product',
            'relatedProducts',
            'ratingCounts',
            'ratingPercentages',
            'attributes',
            'variantData',
            'availableCombinations',
            'defaultVariant',
            'attributeOrder',
            'initialVariantAttributes',
            'variantCombinations',
            'attributesGrouped',
            'specGroupsData',
            'variantSpecs',
            'wishlistVariantIds',
            'commentsCount',
            'orderItemId',
            'averageRating',
            'totalReviews',
            'starRatingsCount',
            'hasReviewed',
            'paginatedItems',
            'allComments',
            'allReviews',
            
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
        try {
            $variantId = $request->input('variant_id');
            $recentProductIds = $request->input('recent_product_ids', []);

            \Log::info('📥 Nhận được danh sách sản phẩm đã xem:', [
                'variant_id' => $variantId,
                'recent_product_ids' => $recentProductIds,
            ]);

            if (empty($recentProductIds)) {
                return response()->json([
                    'suggested' => [],
                    'count' => 0,
                    'message' => 'Chưa có sản phẩm nào đã xem gần đây.'
                ]);
            }

            $currentVariant = ProductVariant::find($variantId);
            $currentProductId = $currentVariant?->product_id;

            $filtered = collect($recentProductIds)
                ->filter(fn($item) => isset($item['id']))
                ->unique(fn($item) => $item['id'] . '_' . $item['variant_key']) // tránh trùng
                ->take(5); // không đảo ngược thứ tự

            $results = collect();

            foreach ($filtered as $item) {
                $product = Product::with([
                    'variants.attributeValues.attribute',
                    'coverImage',
                    'variants.primaryImage',
                    'variants.specifications' // Tải thông số kỹ thuật của biến thể
                ])
                    ->where('id', $item['id'])
                    ->where('status', 'published')
                    ->first();

                if (!$product) continue;

                $variantKey = $item['variant_key'] ?? null;
                $variant = null;

                // 1. Nếu có variant_key → tìm đúng biến thể
                if (!empty($variantKey)) {
                    $variant = $product->variants->first(function ($v) use ($variantKey) {
                        $key = $v->attributeValues
                            ->sortBy(fn($attr) => $attr->attribute->id)
                            ->pluck('value')
                            ->implode('_');
                        return $key === $variantKey;
                    });
                }

                // 2. Nếu không có variant_key và là sản phẩm đơn giản → lấy biến thể duy nhất
                if (!$variant && $product->type === 'simple' && $product->variants->count() === 1) {
                    $variant = $product->variants->first();
                }

                if (!$variant) continue; // Bỏ qua nếu không có biến thể nào phù hợp

                $variantName = $variant->attributeValues
                    ->sortBy(fn($attr) => $attr->attribute->id)
                    ->pluck('value')
                    ->implode(' ');

                $imageUrl = $variant->primaryImage?->path
                    ?? $variant->image?->path
                    ?? $product->coverImage?->path;

                // Định dạng specs theo cấu trúc mong muốn
                $specs = $variant->specifications ? $this->formatSpecs($variant->specifications) : [];

                $results->push([
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'variant_id' => $variant->id,
                    'variant_name' => $variantName,
                    'variant_key' => $variantKey,
                    'cover_image' => $imageUrl ? Storage::url($imageUrl) : asset('/images/no-image.png'),
                    'price' => (int) $variant->price,
                    'sale_price' => $variant->sale_price !== null ? (int) $variant->sale_price : null,
                    'specs' => $specs // Thêm specs vào phản hồi
                ]);
            }

            return response()->json([
                'suggested' => $results,
                'count' => $results->count(),
                'message' => 'Hiển thị sản phẩm đã xem gần đây.'
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ Lỗi compareSuggestions:', ['msg' => $e->getMessage()]);
            return response()->json(['error' => 'Đã xảy ra lỗi khi xử lý.'], 500);
        }
    }

    private function formatSpecs($specs)
    {
        $formatted = [];
        foreach ($specs as $spec) {
            $groupName = $spec->group_name ?? 'Thông số chung';
            if (!isset($formatted[$groupName])) {
                $formatted[$groupName] = [];
            }
            $formatted[$groupName][$spec->name] = $spec->value;
        }
        return $formatted;
    }


    private function getVariantKey(?ProductVariant $variant): string
    {
        if (!$variant) return '';
        return $variant->attributeValues
            ->sortBy(fn($attr) => $attr->attribute->id)
            ->pluck('value')
            ->implode('_');
    }
    public function search(Request $request)
    {
        $query = $request->input('q');

        $products = Product::with('category')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->paginate(12);

        return view('users.shop', [
            'products' => $products,
            'categories' => Category::all(), // để sidebar hoạt động
            'searchQuery' => $query,
            'currentCategory' => null, // để tránh breadcrumb danh mục
        ]);
    }
}
