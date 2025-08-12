<?php

namespace App\Http\Controllers\Users;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\Banner;
use App\Models\Review;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Category;
use App\Models\Province;
use App\Models\FlashSale;
use App\Models\OrderItem;
use App\Models\DistrictOld;
use App\Models\ProvinceOld;
use Illuminate\Support\Str;
use App\Models\PostCategory;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use App\Models\ProductBundle;
use App\Models\StoreLocation;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\HomepageProductBlock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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

        $suggestedProducts = Product::with('coverImage')
            ->where('status', 'published')
            ->inRandomOrder()
            ->take(5)
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

        // === Lấy danh sách Flash Sale (theo logic quản lý) ===

        $flashSales = FlashSale::with([
            'flashSaleTimeSlots' => function ($q) {
                $q->orderBy('start_time');
            },
            'flashSaleTimeSlots.products.productVariant.attributeValues.attribute',
            'flashSaleTimeSlots.products.productVariant.product.coverImage',
        ])
            ->where('status', 'active')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->orderBy('start_time')
            ->get();

        // Xử lý format thời gian + tên biến thể đầy đủ và xác định slot đang active
        $now = now();
        $flashSales->each(function ($sale) use ($now) {
            $activeSlotId = null;
            $upcomingSlotId = null;
            $minUpcomingTime = null;
            foreach ($sale->flashSaleTimeSlots as $slot) {
                $slot->start_time = \Carbon\Carbon::parse($slot->start_time)->toIso8601String();
                $slot->end_time = \Carbon\Carbon::parse($slot->end_time)->toIso8601String();

                $start = \Carbon\Carbon::parse($slot->start_time);
                $end = \Carbon\Carbon::parse($slot->end_time);
                $isActive = $now->between($start, $end);
                $isUpcoming = $now->lt($start);
                $isPast = $now->gt($end);

                \Log::info('DEBUG_FLASH_SLOT', [
                    'slot_id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'now' => $now->toIso8601String(),
                    'isActive' => $isActive,
                    'isUpcoming' => $isUpcoming,
                    'isPast' => $isPast,
                    'activeSlotId' => $activeSlotId,
                ]);

                if ($isActive && $activeSlotId === null) {
                    $activeSlotId = $slot->id;
                }
                if ($isUpcoming && ($minUpcomingTime === null || $start->lt($minUpcomingTime))) {
                    $minUpcomingTime = $start;
                    $upcomingSlotId = $slot->id;
                }

                $slot->products->each(function ($product) {
                    $variant = $product->productVariant;
                    $productName = $variant->product->name ?? '';

                    $attributes = $variant->attributeValues ?? collect();

                    $nonColor = $attributes
                        ->filter(fn($v) => $v->attribute->name !== 'Màu sắc')
                        ->pluck('value')
                        ->join(' ');

                    $color = $attributes
                        ->firstWhere(fn($v) => $v->attribute->name === 'Màu sắc')?->value;

                    $variantName = trim($productName . ' ' . $nonColor . ' ' . $color);

                    $product->variant_name = $variantName;
                });
            }
            if ($activeSlotId) {
                $sale->active_slot_id = $activeSlotId;
            } elseif ($upcomingSlotId) {
                $sale->active_slot_id = $upcomingSlotId;
            } else {
                $sale->active_slot_id = $sale->flashSaleTimeSlots->last()->id ?? null;
            }
            \Log::info('DEBUG_FLASH_SALE_ACTIVE_SLOT', [
                'sale_id' => $sale->id,
                'active_slot_id' => $sale->active_slot_id,
            ]);
        });


        return view('users.home', compact(
            'featuredProducts',
            'blocks',
            'latestProducts',
            'banners',
            'featuredPosts',
            'unreadNotificationsCount',
            'recentNotifications',
            'flashSales',
            'suggestedProducts' // 👈 THÊM BIẾN NÀY
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

        $ratingFilter = $request->query('rating');

        $allReviews = Review::with(['user', 'images'])
            ->whereIn('product_variant_id', $variantIds)
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
            });

        if ($ratingFilter && in_array((int)$ratingFilter, [1, 2, 3, 4, 5])) {
            $allReviews = $allReviews->where('rating', (int)$ratingFilter);
        }
        $allReviews  = $allReviews->get();

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
            Log::info('✅ Combination pushed:', $combination);


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

            $images = $variant->images->map(fn($image) => $image->url)->toArray();
            if (empty($images)) {
                $images = [asset('images/placeholder.jpg')];
            }
            $mainImage = $variant->primaryImage ? $variant->primaryImage->url : ($images[0] ?? null);

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

        // --- Logic mới: Xử lý gói sản phẩm (bundles) ---
        $variantId = $request->query('variant_id');
        $selectedVariant = $variantId && $product->variants->contains('id', $variantId)
            ? $product->variants->firstWhere('id', $variantId)
            : $defaultVariant;
        $availableStock = 0;
        if ($defaultVariant) {
            $availableStock = $defaultVariant->inventories()
                ->where('inventory_type', 'new')
                ->selectRaw('COALESCE(SUM(quantity - quantity_committed), 0) as available_stock')
                ->value('available_stock');
        }
        $productBundles = ProductBundle::with([
            'mainProducts.productVariant.product.coverImage',
            'suggestedProducts.productVariant.product.coverImage'
        ])
            ->where('status', 'active')
            ->where(function ($query) use ($selectedVariant) {
                $query->where('start_date', '<=', now())
                    ->where(function ($q) {
                        $q->where('end_date', '>=', now())
                            ->orWhereNull('end_date');
                    })
                    ->whereHas('mainProducts', function ($q) use ($selectedVariant) {
                        $q->where('product_variant_id', $selectedVariant->id);
                    });
            })
            ->get()
            ->map(function ($bundle) use ($selectedVariant) {
                $mainProduct = $bundle->mainProducts->firstWhere('product_variant_id', $selectedVariant->id);
                $mainVariant = $mainProduct->productVariant;
                $mainProductData = $mainVariant->product;

                // Tính giá sản phẩm chính
                $mainPrice = (int) ($mainVariant->price ?? 0);
                $mainSalePrice = $mainVariant->sale_price !== null ? (int) $mainVariant->sale_price : null;

                $hasFlashTime = !empty($mainVariant->sale_price_starts_at) && !empty($mainVariant->sale_price_ends_at);
                $isFlashSale = $hasFlashTime && now()->between($mainVariant->sale_price_starts_at, $mainVariant->sale_price_ends_at);
                $isSale = !$isFlashSale && $mainSalePrice !== null && $mainSalePrice < $mainPrice;

                $mainDisplayPrice = ($isFlashSale || $isSale) && $mainSalePrice !== null ? $mainSalePrice : $mainPrice;
                $mainOriginalPrice = ($isFlashSale || $isSale) && $mainPrice > $mainSalePrice ? $mainPrice : null;

                Log::info('Main product pricing', [
                    'variant_id' => $mainVariant->id,
                    'product_name' => $mainProductData->name,
                    'main_price' => $mainPrice,
                    'main_sale_price' => $mainSalePrice,
                    'is_flash_sale' => $isFlashSale,
                    'is_sale' => $isSale,
                    'main_display_price' => $mainDisplayPrice,
                    'main_original_price' => $mainOriginalPrice,
                ]);


                // Lấy hình ảnh sản phẩm chính (ưu tiên primaryImage của variant, sau đó đến coverImage của product)
                $mainImage = $mainVariant && $mainVariant->primaryImage && file_exists(storage_path('app/public/' . $mainVariant->primaryImage->path)) ? Storage::url($mainVariant->primaryImage->path)
                    : ($mainProductData && $mainProductData->coverImage && file_exists(storage_path('app/public/' . $mainProductData->coverImage->path)) ? Storage::url($mainProductData->coverImage->path)
                        : asset('images/placeholder.jpg'));
                // Dữ liệu sản phẩm chính
                $mainProductItem = [
                    'variant_id' => $mainVariant->id,
                    'product_id' => $mainProductData->id,
                    'name' => $mainProductData->name,
                    'slug' => $mainProductData->slug,
                    'image' => $mainImage,
                    'price' => $mainPrice,
                    'sale_price' => $mainSalePrice,
                    'display_price' => $mainDisplayPrice,
                    'original_price' => $mainOriginalPrice,
                ];
                Log::info('Main product item', $mainProductItem);


                // Dữ liệu sản phẩm gợi ý
                $suggestedProducts = $bundle->suggestedProducts->sortBy('display_order')->map(function ($suggested) {
                    $variant = $suggested->productVariant;
                    $product = $variant->product;
                    $price = (int) $variant->price;
                    $salePrice = (int) $variant->sale_price;
                    $hasFlashTime = !empty($variant->sale_price_starts_at) && !empty($variant->sale_price_ends_at);
                    $isFlashSale = $hasFlashTime && now()->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);
                    $isSale = !$isFlashSale && $salePrice && $salePrice < $price;
                    $originalPrice = $isFlashSale || $isSale ? $price : null;


                    // Tính giá ưu đãi theo discount_type
                    if ($suggested->discount_type === 'fixed_price') {
                        $bundlePrice = (int) $suggested->discount_value;
                    } else {
                        $basePrice = $isFlashSale || $isSale ? $salePrice : $price;
                        $bundlePrice = $basePrice * (1 - $suggested->discount_value / 100);
                    }

                    return [
                        'variant_id' => $variant->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'image' => $variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path)
                            : ($product && $product->coverImage && file_exists(storage_path('app/public/' . $product->coverImage->path)) ? Storage::url($product->coverImage->path)
                                : asset('images/placeholder.jpg')),
                        'price' => $price,
                        'sale_price' => $salePrice,
                        'bundle_price' => $bundlePrice,
                        'original_price' => $originalPrice,
                        'is_preselected' => $suggested->is_preselected,
                        'display_order' => $suggested->display_order,
                    ];
                })->toArray();

                // Tính tổng giá gói (chỉ tính sản phẩm được chọn sẵn)
                $totalBundlePrice = $mainDisplayPrice;
                foreach ($suggestedProducts as $suggested) {
                    if ($suggested['is_preselected']) {
                        $totalBundlePrice += $suggested['bundle_price'];
                    }
                }

                return [
                    'id' => $bundle->id,
                    'name' => $bundle->name,
                    'display_title' => $bundle->display_title,
                    'description' => $bundle->description,
                    'main_product' => $mainProductItem,
                    'suggested_products' => $suggestedProducts,
                    'total_bundle_price' => $totalBundlePrice,
                ];
            });

        // Kết thúc xử lý gói sản phẩm

        // --- Bắt đầu logic để lấy Store Locations có sản phẩm ---
        $productVariantId = $selectedVariant ? $selectedVariant->id : ($defaultVariant ? $defaultVariant->id : null);

        if (!$productVariantId) {
            $storeLocations = collect();
            $provinces = collect();
            $districts = collect();
        } else {
            $storeLocations = StoreLocation::with(['province', 'district', 'ward'])
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->where('type', 'store')
                ->whereHas('productInventories', function ($query) use ($productVariantId) {
                    $query->where('product_variant_id', $productVariantId)
                        ->where('quantity', '>', 0)
                        ->where('inventory_type', 'new');
                })
                ->orderBy('name')
                ->get()
                ->each(function ($location) use ($productVariantId) {
                    $location->quantity = $location->productInventories()
                        ->where('product_variant_id', $productVariantId)
                        ->where('inventory_type', 'new')
                        ->sum('quantity');
                });


            $provinces = ProvinceOld::whereHas('storeLocations', function ($query) use ($productVariantId) {
                $query->where('is_active', 1)
                    ->whereNull('deleted_at')
                    ->where('type', 'store')
                    ->whereHas('productInventories', function ($subQuery) use ($productVariantId) {
                        $subQuery->where('product_variant_id', $productVariantId)
                            ->where('quantity', '>', 0)
                            ->where('inventory_type', 'new');
                    });
            })
                ->orderBy('name')
                ->get();

            $districts = collect();
        }

        // Kêt thúc logic Store Locations
       
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
        $totalReviewsCount = $allReviews->count();

        $totalCommentsCount = $allComments->count();
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
            'totalReviewsCount',
            'totalCommentsCount',
            'ratingFilter',
            'productBundles',
            'storeLocations',
            'provinces', // Thêm provinces để view có thể dùng
            'districts', // Districts sẽ được load động bằng JS
            'availableStock',
            // Thêm biến mới
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

        // 💰 Lọc giá
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    // Kiểm tra nếu có sale_price
                    $q2->where(function ($q3) use ($request) {
                        $q3->where('sale_price', '>', 0);
                        if ($request->filled('min_price')) {
                            $q3->where('sale_price', '>=', $request->min_price);
                        }
                        if ($request->filled('max_price')) {
                            $q3->where('sale_price', '<=', $request->max_price);
                        }
                    })
                        // Nếu không có sale_price, dùng giá gốc
                        ->orWhere(function ($q3) use ($request) {
                            $q3->where('sale_price', 0)->orWhereNull('sale_price');
                            if ($request->filled('min_price')) {
                                $q3->where('price', '>=', $request->min_price);
                            }
                            if ($request->filled('max_price')) {
                                $q3->where('price', '<=', $request->max_price);
                            }
                        });
                });
            });
        }

        // 🔃 Sắp xếp
        switch ($request->sort) {
            case 'moi_nhat':
                $query->where('created_at', '>=', $now->copy()->subWeek())->orderByDesc('created_at');
                break;

            case 'gia_thap_den_cao':
                $query->whereHas('variants', fn($q) => $q->whereNull('deleted_at'))
                    ->orderByRaw('(
                    SELECT COALESCE(MIN(sale_price), MIN(price))
                    FROM product_variants pv
                    WHERE pv.product_id = products.id
                    AND pv.deleted_at IS NULL
                    AND (pv.is_default = true OR pv.id = (
                        SELECT MIN(id)
                        FROM product_variants pv2
                        WHERE pv2.product_id = pv.product_id
                        AND pv2.deleted_at IS NULL
                    ))
                ) ASC');
                break;

            case 'gia_cao_den_thap':
                $query->whereHas('variants', fn($q) => $q->whereNull('deleted_at'))
                    ->orderByRaw('(
                    SELECT COALESCE(MIN(sale_price), MIN(price))
                    FROM product_variants pv
                    WHERE pv.product_id = products.id
                    AND pv.deleted_at IS NULL
                    AND (pv.is_default = true OR pv.id = (
                        SELECT MIN(id)
                        FROM product_variants pv2
                        WHERE pv2.product_id = pv.product_id
                        AND pv2.deleted_at IS NULL
                    ))
                ) DESC');
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

        if ($request->ajax() || $request->wantsJson()) {
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
        $tab = $request->input('tab', 'san-pham'); // mặc định là 'san-pham'

        if ($tab === 'bai-viet') {
            $posts = Post::with('coverImage')
                ->where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('slug', 'like', "%{$query}%")
                        ->orWhere('title', 'like', "%{$query}%");
                })
                ->paginate(10);

            return view('users.blogs.index', [
                'posts' => $posts,
                'parentCategories' => PostCategory::withCount('posts')->whereNull('parent_id')->get(),
                'featuredPosts' => Post::where('is_featured', true)->where('status', 'published')->latest()->take(5)->get(),
                'currentCategory' => null,
            ]);
        }

        // Tab mặc định: Sản phẩm
        // Tab mặc định: Sản phẩm
        $products = Product::with(['category', 'variants', 'coverImage'])
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('slug', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%");
            })
            ->paginate(12);

        // ✅ Thêm dòng này để truyền danh mục vào view
        $categories = Category::all();
        $parentCategories = $categories->whereNull('parent_id');

        return view('users.shop', [
            'products' => $products,
            'searchQuery' => $query,
            'tab' => $tab,
            'categories' => $categories,
            'parentCategories' => $parentCategories,
            'currentCategory' => null, // vì không phải xem theo danh mục
        ]);
    }

    public function searchSuggestions(Request $request)
    {
        $query = $request->input('q');

        $products = Product::with(['coverImage', 'variants'])
            ->where('status', 'published')
            ->where('name', 'like', "%{$query}%")
            ->take(5)
            ->get()
            ->map(function ($product) {
                $variants = $product->variants;

                // Lấy giá sale thấp nhất (nếu có), nếu không thì lấy giá gốc
                $minSalePrice = $variants->whereNotNull('sale_price')->min('sale_price');
                $minPrice = $variants->min('price');

                return [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $minPrice ? number_format($minPrice) . ' ₫' : null,
                    'sale_price' => $minSalePrice ? number_format($minSalePrice) . ' ₫' : null,
                    'image_url' => $product->coverImage->url ?? asset('images/no-image.png'),
                ];
            });

        return response()->json($products);
    }

    // API để lấy danh sách quận/huyện theo tỉnh
    public function getDistrictsByProvince(Request $request)
    {
        // Lấy province_code và product_variant_id từ request
        $provinceCode = $request->input('province_code');
        $productVariantId = $request->input('product_variant_id');

        // Debug: Ghi log province_code và product_variant_id
        \Log::info('getDistrictsByProvince called with province_code: ' . $provinceCode . ', product_variant_id: ' . $productVariantId);

        try {
            // Thực hiện truy vấn
            $districts = DistrictOld::where('parent_code', $provinceCode)
                ->whereHas('storeLocations', function ($query) use ($productVariantId) {
                    $query->where('is_active', 1)
                        ->whereNull('deleted_at')
                        ->where('type', 'store')
                        ->whereHas('productInventories', function ($subQuery) use ($productVariantId) {
                            $subQuery->where('product_variant_id', $productVariantId)
                                ->where('quantity', '>', 0)
                                ->where('inventory_type', 'new'); // Chỉ lấy tồn kho loại 'new'
                        });
                })
                ->orderBy('name')
                ->get(['code', 'name']);

            // Debug: Ghi log kết quả truy vấn
            \Log::info('Districts found: ' . json_encode($districts));

            return response()->json($districts);
        } catch (\Exception $e) {
            // Debug: Ghi log nếu có lỗi
            \Log::error('Error in getDistrictsByProvince: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    // API để lọc cửa hàng theo tỉnh/quận
    public function filterStoreLocations(Request $request)
    {
        $provinceCode = $request->input('province_code');
        $districtCode = $request->input('district_code');
        $productVariantId = $request->input('product_variant_id');

        // Debug: Ghi log các tham số
        \Log::info('filterStoreLocations called with province_code: ' . $provinceCode . ', district_code: ' . $districtCode . ', product_variant_id: ' . $productVariantId);

        try {
            $query = StoreLocation::with(['province', 'district', 'ward'])
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->where('type', 'store') // Chỉ lấy loại cửa hàng
                ->whereHas('productInventories', function ($query) use ($productVariantId) {
                    $query->where('product_variant_id', $productVariantId)
                        ->where('quantity', '>', 0)
                        ->where('inventory_type', 'new'); // Chỉ lấy tồn kho loại 'new'
                });

            if ($provinceCode) {
                $query->where('province_code', $provinceCode);
            }

            if ($districtCode) {
                $query->where('district_code', $districtCode);
            }

            $filteredStores = $query->orderBy('name')->get()->map(function ($location) use ($productVariantId) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'phone' => $location->phone,
                    'full_address' => $location->full_address,
                    'address' => $location->address, // thêm dòng này
                    'province' => $location->province->name ?? 'N/A',
                    'district' => $location->district->name ?? 'N/A',
                    'ward' => $location->ward->name ?? 'N/A',
                    'quantity' => $location->productInventories()
                        ->where('product_variant_id', $productVariantId)
                        ->where('inventory_type', 'new')
                        ->sum('quantity'),
                ];
            });

            return response()->json([
                'stores' => $filteredStores,
                'count' => $filteredStores->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in filterStoreLocations: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    // API để lấy danh sách tỉnh/thành phố theo biến thể sản phẩm
    public function getProvincesByVariant(Request $request)
    {
        $productVariantId = $request->input('product_variant_id');

        // Debug: Ghi log product_variant_id
        \Log::info('getProvincesByVariant called with product_variant_id: ' . $productVariantId);

        try {
            // Lấy danh sách tỉnh có sản phẩm của biến thể này
            // Sử dụng ProvinceOld vì StoreLocation liên kết với ProvinceOld
            $provinces = ProvinceOld::whereHas('storeLocations', function ($query) use ($productVariantId) {
                $query->where('is_active', 1)
                    ->whereNull('deleted_at')
                    ->where('type', 'store')
                    ->whereHas('productInventories', function ($subQuery) use ($productVariantId) {
                        $subQuery->where('product_variant_id', $productVariantId)
                            ->where('quantity', '>', 0)
                            ->where('inventory_type', 'new'); // Chỉ lấy tồn kho loại 'new'
                    });
            })
                ->orderBy('name')
                ->get(['code', 'name']);

            // Debug: Ghi log kết quả truy vấn
            \Log::info('Provinces found for variant ' . $productVariantId . ': ' . json_encode($provinces));

            return response()->json($provinces);
        } catch (\Exception $e) {
            // Debug: Ghi log nếu có lỗi
            \Log::error('Error in getProvincesByVariant: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
