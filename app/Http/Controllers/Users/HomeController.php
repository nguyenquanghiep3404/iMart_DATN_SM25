<?php

namespace App\Http\Controllers\Users;

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
use Carbon\Carbon;


class HomeController extends Controller
{
    public function index()
    {
         $banners = Banner::with(['desktopImage', 'mobileImage'])->activeAndValid()->orderBy('order')->get();
        $calculateAverageRating = function ($products) {
            foreach ($products as $product) {
                $averageRating = $product->reviews->avg('rating') ?? 0;
                $product->average_rating = round($averageRating, 1);

                $now = now();
                $variant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();

                if ($variant) {
                    $isOnSale = false;

                    if (
                        $variant->sale_price
                        && $variant->sale_price_starts_at
                        && $variant->sale_price_ends_at
                        && $variant->price > 0
                    ) {
                        try {
                            $startDate = Carbon::parse($variant->sale_price_starts_at);
                            $endDate = Carbon::parse($variant->sale_price_ends_at);
                            $isOnSale = $now->between($startDate, $endDate);
                        } catch (\Exception $e) {
                            Log::error('Error parsing dates for product ' . $product->name . ': ' . $e->getMessage());
                            $isOnSale = false;
                        }
                    }

                    $variant->discount_percent = $isOnSale
                        ? round(100 - ($variant->sale_price / $variant->price) * 100)
                        : 0;
                }
            }
        };

        // Truy vấn sản phẩm nổi bật trực tiếp, không cache
        $featuredProducts = Product::with([
            'category',
            'coverImage',
            'variants' => function ($query) {
                $query->where(function ($q) {
                    $q->where('is_default', true)
                        ->orWhereRaw('id = (
                              select min(id) 
                              from product_variants pv 
                              where pv.product_id = product_variants.product_id 
                              and pv.deleted_at is null
                          )');
                })
                    ->whereNull('deleted_at')
                    ->select([
                        'id',
                        'product_id',
                        'price',
                        'sale_price',
                        'sale_price_starts_at',
                        'sale_price_ends_at',
                        'is_default'
                    ]);
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

        $calculateAverageRating($featuredProducts);

        // Truy vấn sản phẩm mới nhất trực tiếp, không cache
        $latestProducts = Product::with([
            'category',
            'coverImage',
            'variants' => function ($query) {
                $query->where('is_default', true)
                    ->orWhereRaw('id = (
                            select min(id) 
                            from product_variants pv 
                            where pv.product_id = product_variants.product_id 
                            and pv.deleted_at is null
                        )')
                    ->whereNull('deleted_at');
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

        $calculateAverageRating($latestProducts);

        return view('users.home', compact('featuredProducts', 'latestProducts', 'banners'));
    }

    public function show($slug)
    {
        // Lấy sản phẩm theo slug, kèm các quan hệ cần thiết
        $product = Product::with([
            'category',                          // Danh mục của sản phẩm
            'coverImage',                        // Ảnh đại diện
            'galleryImages',                     // Thư viện ảnh
            'variants.attributeValues.attribute', // Biến thể và các giá trị thuộc tính (VD: màu sắc, dung lượng,...)
            'variants.images' => function ($query) {
                $query->where('type', 'variant_image')->orderBy('order');
            },                                   // Lấy ảnh của biến thể
            'reviews' => function ($query) {
                // Chỉ lấy các đánh giá đã được duyệt
                $query->where('reviews.status', 'approved');
            },
        ])
            ->withCount([
                'reviews as reviews_count' => function ($query) {
                    // Đếm số lượng đánh giá đã được duyệt, gán vào alias 'reviews_count'
                    $query->where('reviews.status', 'approved');
                }
            ])
            ->where('slug', $slug)              // Tìm sản phẩm theo slug URL
            ->where('status', 'published')      // Chỉ lấy sản phẩm đã được publish
            ->firstOrFail();                    // Trả về 404 nếu không tìm thấy sản phẩm

        // Tăng lượt xem của sản phẩm lên 1
        $product->increment('view_count');

        // Tính điểm đánh giá trung bình (trung bình cộng các rating đã được duyệt)
        $averageRating = $product->reviews->avg('rating') ?? 0;
        $product->average_rating = round($averageRating, 1); // Làm tròn đến 1 chữ số thập phân

        // Đếm số lượng đánh giá theo từng mức sao (1 đến 5)
        $ratingCounts = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingCounts[$i] = $product->reviews->where('rating', $i)->count();
        }

        // Tổng số đánh giá đã được duyệt
        $totalReviews = $product->reviews_count;

        // Tính phần trăm đánh giá theo từng sao
        $ratingPercentages = [];
        foreach ($ratingCounts as $star => $count) {
            $ratingPercentages[$star] = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
        }

        // Gom tất cả các giá trị thuộc tính từ các biến thể của sản phẩm
        $attributes = $product->variants
            ->flatMap(fn($variant) => $variant->attributeValues)               // Gom toàn bộ các attributeValues
            ->groupBy(fn($attrValue) => $attrValue->attribute->name)           // Nhóm theo tên thuộc tính (ví dụ: Màu sắc, Kích thước,...)
            ->map(fn($group) => $group->unique('value'));                      // Loại bỏ giá trị trùng (VD: tránh lặp lại "11 inch")

        // Tạo mảng ánh xạ biến thể (variantData) để sử dụng trong JavaScript
        $variantData = [];
        foreach ($product->variants as $variant) {
            $now = now();
            $salePrice = (int) $variant->sale_price;
            $originalPrice = (int) $variant->price;
            $isOnSale = $variant->sale_price !== null &&
                $variant->sale_price_starts_at <= $now &&
                $variant->sale_price_ends_at >= $now;
            $displayPrice = $isOnSale ? $salePrice : $originalPrice;

            // Tạo key từ các thuộc tính của biến thể
            $variantKey = [];
            foreach ($variant->attributeValues as $attrValue) {
                $attrName = $attrValue->attribute->name;
                $attrValue = $attrValue->value;
                $variantKey[$attrName] = $attrValue;
            }
            ksort($variantKey); // Sắp xếp để đảm bảo key nhất quán
            $variantKey = implode('_', $variantKey); // Tạo key dạng "Xanh_256GB"

            // Lấy danh sách ảnh của biến thể
            $images = $variant->images->map(function ($image) {
                return Storage::url($image->path); // Chuyển đổi đường dẫn ảnh thành URL
            })->toArray();

            // Nếu không có ảnh cụ thể cho biến thể, fallback về ảnh bìa hoặc gallery mặc định
            if (empty($images)) {
                $images = [];
                if ($product->coverImage) {
                    $images[] = Storage::url($product->coverImage->path);
                }
                foreach ($product->galleryImages as $galleryImage) {
                    $images[] = Storage::url($galleryImage->path);
                }
            }

            // Lưu thông tin biến thể, bao gồm danh sách ảnh
            $variantData[$variantKey] = [
                'price' => number_format($displayPrice),
                'original_price' => $isOnSale && $originalPrice > $salePrice ? number_format($originalPrice) : null,
                'status' => $variant->status,
                'images' => $images, // Thêm danh sách ảnh vào variantData
            ];
        }

        // Lấy 4 sản phẩm liên quan (cùng category, không lấy chính sản phẩm hiện tại)
        $relatedProducts = Product::with(['category', 'coverImage'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->take(4)
            ->get();

        // hiển thị comment lên trang chi tiết sản phẩm
        $product = Product::where('slug', $slug)->firstOrFail();

        // Lấy bình luận cha đã duyệt kèm người dùng và các trả lời
        $comments = $product->comments()
            ->where('status', 'approved')
            ->whereNull('parent_id') // chỉ lấy comment cha
            ->with(['user', 'replies.user']) // eager load user và replies
            ->orderByDesc('created_at')
            ->get();


        // Trả dữ liệu về view hiển thị chi tiết sản phẩm
        // var_dump($comments);
        return view('users.show', compact(
            'product',
            'relatedProducts',
            'ratingCounts',
            'ratingPercentages',
            'totalReviews',
            'attributes',
            'variantData', // Bổ sung biến $variantData
            'comments'
        ));
    }

    public function allProducts(Request $request)
    {
        $query = Product::with([
            'category',
            'coverImage',
            'variants' => function ($query) {
                $query->where(function ($q) {
                    $q->where('is_default', true)
                        ->orWhereRaw('id = (
                        select min(id)
                        from product_variants pv
                        where pv.product_id = product_variants.product_id
                        and pv.deleted_at is null
                    )');
                })
                    ->whereNull('deleted_at');
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

        // 🔍 Tìm kiếm theo tên
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 🗂 Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
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

        $products = $query->latest()->paginate(12); // phân trang 12 sản phẩm

        // Tính rating trung bình
        foreach ($products as $product) {
            $product->average_rating = round($product->reviews->avg('rating') ?? 0, 1);
            $variant = $product->variants->first();
            if ($variant && $variant->sale_price && $variant->sale_price_starts_at && $variant->sale_price_ends_at) {
                $now = now();
                $onSale = $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);
                $variant->discount_percent = $onSale
                    ? round(100 - ($variant->sale_price / $variant->price) * 100)
                    : 0;
            }
        }

        $categories = Category::all();

        return view('users.shop', compact('products', 'categories'));
    }
}
