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

        // Định nghĩa một hàm ẩn danh để tính và gán điểm đánh giá trung bình cũng như phần trăm giảm giá cho sản phẩm
        $banners = Banner::with('desktopImage')->where('status', 'active')->orderBy('order')->get();
        $calculateAverageRating = function ($products) {
            // Duyệt qua từng sản phẩm trong tập hợp được cung cấp
            foreach ($products as $product) {
                // Tính điểm đánh giá trung bình từ các đánh giá của sản phẩm, mặc định là 0 nếu không có đánh giá
                $averageRating = $product->reviews->avg('rating') ?? 0;
                // Làm tròn điểm đánh giá trung bình đến 1 chữ số thập phân và gán vào thuộc tính của sản phẩm
                $product->average_rating = round($averageRating, 1);

                // Lấy thời gian hiện tại bằng hàm trợ giúp của Laravel
                $now = now();
                // Chọn biến thể mặc định (is_default = true) hoặc biến thể đầu tiên nếu không có biến thể mặc định
                $variant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();

                // Kiểm tra nếu biến thể tồn tại
                if ($variant) {
                    // Khởi tạo biến kiểm tra sản phẩm có đang giảm giá hay không
                    $isOnSale = false;

                    // Kiểm tra điều kiện để xác định sản phẩm có đang giảm giá
                    if (
                        $variant->sale_price // Giá giảm tồn tại
                        && $variant->sale_price_starts_at // Thời gian bắt đầu giảm giá tồn tại
                        && $variant->sale_price_ends_at // Thời gian kết thúc giảm giá tồn tại
                        && $variant->price > 0 // Giá gốc lớn hơn 0
                    ) {
                        try {
                            // Chuyển đổi thời gian bắt đầu giảm giá thành đối tượng Carbon
                            $startDate = Carbon::parse($variant->sale_price_starts_at);
                            // Chuyển đổi thời gian kết thúc giảm giá thành đối tượng Carbon
                            $endDate = Carbon::parse($variant->sale_price_ends_at);
                            // Kiểm tra xem thời gian hiện tại có nằm trong khoảng thời gian giảm giá không
                            $isOnSale = $now->between($startDate, $endDate);
                        } catch (\Exception $e) {
                            // Ghi log lỗi nếu việc phân tích ngày tháng gặp lỗi và giữ trạng thái không giảm giá
                            Log::error('Lỗi phân tích ngày tháng cho sản phẩm ' . $product->name . ': ' . $e->getMessage());
                            $isOnSale = false;
                        }
                    }

                    // Tính phần trăm giảm giá nếu sản phẩm đang giảm giá, nếu không thì gán bằng 0
                    $variant->discount_percent = $isOnSale
                        ? round(100 - ($variant->sale_price / $variant->price) * 100)
                        : 0;
                }
            }
        };

        // Truy vấn danh sách sản phẩm nổi bật trực tiếp, không sử dụng bộ nhớ đệm
        $featuredProducts = Product::with([
            // Nạp quan hệ danh mục của sản phẩm
            'category',
            // Nạp hình ảnh bìa của sản phẩm
            'coverImage',
            // Nạp các biến thể của sản phẩm với điều kiện cụ thể
            'variants' => function ($query) {
                // Lấy biến thể mặc định hoặc biến thể có id nhỏ nhất chưa bị xóa
                $query->where(function ($q) {
                    $q->where('is_default', true)
                        ->orWhereRaw('id = (
                        select min(id) 
                        from product_variants pv 
                        where pv.product_id = product_variants.product_id 
                        and pv.deleted_at is null
                    )');
                })
                    // Chỉ lấy các biến thể chưa bị xóa mềm
                    ->whereNull('deleted_at')
                    // Chỉ chọn các cột cần thiết để tối ưu hiệu suất
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
            // Nạp các đánh giá của sản phẩm với trạng thái 'approved'
            'reviews' => function ($query) {
                $query->where('reviews.status', 'approved');
            }
        ])
            // Đếm số lượng đánh giá được phê duyệt và gán vào thuộc tính approved_reviews_count
            ->withCount([
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('reviews.status', 'approved');
                }
            ])
            // Chỉ lấy các sản phẩm được đánh dấu là nổi bật
            ->where('is_featured', 1)
            // Chỉ lấy các sản phẩm đã được xuất bản
            ->where('status', 'published')
            // Lấy sản phẩm loại 'simple' hoặc có biến thể chưa bị xóa
            ->where(function ($query) {
                $query->where('type', 'simple')
                    ->orWhereHas('variants', function ($q) {
                        $q->whereNull('deleted_at');
                    });
            })
            // Sắp xếp theo thứ tự mới nhất
            ->latest()
            // Giới hạn lấy 8 sản phẩm
            ->take(8)
            // Thực thi truy vấn và lấy kết quả
            ->get();

        // Áp dụng hàm tính điểm đánh giá trung bình và phần trăm giảm giá cho sản phẩm nổi bật
        $calculateAverageRating($featuredProducts);

        // Truy vấn danh sách sản phẩm mới nhất trực tiếp, không sử dụng bộ nhớ đệm
        $latestProducts = Product::with([
            // Nạp quan hệ danh mục của sản phẩm
            'category',
            // Nạp hình ảnh bìa của sản phẩm
            'coverImage',
            // Nạp các biến thể của sản phẩm với điều kiện cụ thể
            'variants' => function ($query) {
                // Lấy biến thể mặc định hoặc biến thể có id nhỏ nhất chưa bị xóa
                $query->where('is_default', true)
                    ->orWhereRaw('id = (
                    select min(id) 
                    from product_variants pv 
                    where pv.product_id = product_variants.product_id 
                    and pv.deleted_at is null
                )')
                    // Chỉ lấy các biến thể chưa bị xóa mềm
                    ->whereNull('deleted_at');
            },
            // Nạp các đánh giá của sản phẩm với trạng thái 'approved'
            'reviews' => function ($query) {
                $query->where('reviews.status', 'approved');
            }
        ])
            // Đếm số lượng đánh giá được phê duyệt và gán vào thuộc tính approved_reviews_count
            ->withCount([
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('reviews.status', 'approved');
                }
            ])
            // Chỉ lấy các sản phẩm đã được xuất bản
            ->where('status', 'published')
            // Lấy sản phẩm loại 'simple' hoặc có biến thể chưa bị xóa
            ->where(function ($query) {
                $query->where('type', 'simple')
                    ->orWhereHas('variants', function ($q) {
                        $q->whereNull('deleted_at');
                    });
            })
            // Sắp xếp theo thứ tự mới nhất
            ->latest()
            // Giới hạn lấy 8 sản phẩm
            ->take(8)
            // Thực thi truy vấn và lấy kết quả
            ->get();

        // Áp dụng hàm tính điểm đánh giá trung bình và phần trăm giảm giá cho sản phẩm mới nhất
        $calculateAverageRating($latestProducts);


        // Trả về giao diện 'users.home' với dữ liệu sản phẩm nổi bật và mới nhất
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

        $variantKey = [];
        foreach ($variant->attributeValues as $attrValue) {
            $attrName = $attrValue->attribute->name;
            $value = $attrValue->value;
            $variantKey[$attrName] = $value;
        }
        ksort($variantKey);
        $variantKeyStr = implode('_', array_values($variantKey));

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
        $mainImage = !empty($images) ? $images[0] : null;

        $variantData[$variantKeyStr] = [
            'price' => $displayPrice,
            'original_price' => $isOnSale && $originalPrice > $salePrice ? $originalPrice : null,
            'status' => $variant->status,
            'image' => $mainImage,
            'images' => $images,
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
        'comments'
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
        case 'mới_ra_mắt':
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

        default:
            $query->where('is_featured', 1)->orderByDesc('created_at');
            break;
    }

    // 📄 Phân trang
    $products = $query->paginate(15);

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

    return view('users.shop', compact('products', 'categories', 'currentCategory'));
}


}
