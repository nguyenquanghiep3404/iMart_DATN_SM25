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
    // Lấy sản phẩm theo slug, kèm các quan hệ cần thiết
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

    // Chuẩn bị dữ liệu biến thể
    $variantData = [];
    $attributes = [];
    $availableCombinations = [];

    // Lấy biến thể mặc định
    $defaultVariant = $product->variants->firstWhere('is_default', true);

    // Đầu tiên, tạo map các tổ hợp thuộc tính có sẵn
    foreach ($product->variants as $variant) {
        $combination = [];
        foreach ($variant->attributeValues as $attrValue) {
            $attrName = $attrValue->attribute->name;
            $value = $attrValue->value;
            $combination[$attrName] = $value;

            // Thêm vào danh sách thuộc tính
            if (!isset($attributes[$attrName])) {
                $attributes[$attrName] = collect();
            }
            if (!$attributes[$attrName]->contains('value', $value)) {
                $attributes[$attrName]->push($attrValue);
            }
        }
        $availableCombinations[] = $combination;
    }

    // Sau đó, xử lý thông tin variant
    foreach ($product->variants as $variant) {
        $now = now();
        $salePrice = (int) $variant->sale_price;
        $originalPrice = (int) $variant->price;
        $isOnSale = $variant->sale_price !== null &&
            $variant->sale_price_starts_at <= $now &&
            $variant->sale_price_ends_at >= $now;
        $displayPrice = $isOnSale ? $salePrice : $originalPrice;

        // Tạo key cho variant dựa trên các thuộc tính
        $variantKey = [];
        foreach ($variant->attributeValues as $attrValue) {
            $attrName = $attrValue->attribute->name;
            $value = $attrValue->value;
            $variantKey[$attrName] = $value;
        }

        // Sắp xếp key để đảm bảo thứ tự nhất quán
        ksort($variantKey);
        $variantKeyStr = implode('_', array_values($variantKey));

        // Lấy hình ảnh cho variant
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

        // Lưu thông tin variant
        $variantData[$variantKeyStr] = [
            'price' => $displayPrice,
            'original_price' => $isOnSale && $originalPrice > $salePrice ? $originalPrice : null,
            'status' => $variant->status,
            'image' => $mainImage,
            'images' => $images,
        ];
    }

    // Lấy 4 sản phẩm liên quan
    $relatedProducts = Product::with(['category', 'coverImage'])
        ->where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->where('status', 'published')
        ->take(4)
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
        'defaultVariant' // 👈 Truyền về view
    ));
}



    public function allProducts(Request $request)
    {
        // Lấy thời điểm hiện tại sử dụng Carbon để xử lý ngày giờ
        $now = Carbon::now();

        // Khởi tạo truy vấn cơ bản để lấy danh sách sản phẩm
        $query = Product::with([
            // Nạp quan hệ với danh mục của sản phẩm
            'category',
            // Nạp hình ảnh bìa của sản phẩm
            'coverImage',
            // Nạp các biến thể của sản phẩm với điều kiện động
            'variants' => function ($query) use ($now, $request) {
                // Nếu người dùng lọc theo sản phẩm đang giảm giá
                if ($request->has('sort') && $request->sort === 'dang_giam_gia') {
                    // Chỉ lấy các biến thể đang có chương trình giảm giá hợp lệ
                    $query->where('sale_price', '>', 0) // Giá giảm giá lớn hơn 0
                        ->where('sale_price_starts_at', '<=', $now) // Bắt đầu trước hoặc tại thời điểm hiện tại
                        ->where('sale_price_ends_at', '>=', $now) // Kết thúc sau hoặc tại thời điểm hiện tại
                        ->whereNull('deleted_at') // Không lấy biến thể bị xóa mềm
                        ->orderBy('id'); // Sắp xếp theo id
                } else {
                    // Nếu không lọc theo giảm giá, lấy biến thể mặc định hoặc biến thể có id nhỏ nhất
                    $query->where(function ($q) {
                        $q->where('is_default', true)
                            ->orWhereRaw('id = (
                            select min(id) 
                            from product_variants pv 
                            where pv.product_id = product_variants.product_id 
                            and pv.deleted_at is null
                        )');
                    })
                        ->whereNull('deleted_at'); // Không lấy biến thể bị xóa mềm
                }
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
            // Chỉ lấy các sản phẩm có trạng thái 'published'
            ->where('status', 'published');

        // 🔍 Tìm kiếm theo tên sản phẩm
        if ($request->filled('search')) {
            // Thêm điều kiện tìm kiếm tên sản phẩm chứa chuỗi nhập vào
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 🗂 Lọc theo danh mục
        if ($request->filled('category_id')) {
            // Thêm điều kiện lọc sản phẩm thuộc danh mục được chọn
            $query->where('category_id', $request->category_id);
        }

        // 💰 Lọc theo khoảng giá tối thiểu
        if ($request->filled('min_price')) {
            // Lọc các sản phẩm có biến thể với giá lớn hơn hoặc bằng giá tối thiểu
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }

        // 💰 Lọc theo khoảng giá tối đa
        if ($request->filled('max_price')) {
            // Lọc các sản phẩm có biến thể với giá nhỏ hơn hoặc bằng giá tối đa
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        // Sắp xếp và lọc kết quả dựa trên giá trị của tham số 'sort' từ request
        switch ($request->sort) {
            // Trường hợp 1: Sắp xếp theo sản phẩm mới ra mắt
            case 'mới_ra_mắt':
                // Tạo biến $oneWeekAgo để lưu thời điểm cách đây 1 tuần từ hiện tại
                $oneWeekAgo = Carbon::now()->subWeek();
                // Thêm điều kiện lọc sản phẩm được tạo từ thời điểm $oneWeekAgo trở đi
                $query->where(function ($q) use ($oneWeekAgo) {
                    // Chỉ lấy các sản phẩm có thời gian tạo lớn hơn hoặc bằng 1 tuần trước
                    $q->where('created_at', '>=', $oneWeekAgo);
                })
                    // Sắp xếp kết quả theo thời gian tạo giảm dần (mới nhất lên đầu)
                    ->orderByDesc('created_at');
                // Thoát khỏi case này
                break;

            // Trường hợp 2: Sắp xếp theo giá từ thấp đến cao
            case 'giá_thấp_đến_cao':
                // Đảm bảo chỉ lấy các sản phẩm có biến thể chưa bị xóa mềm
                $query->whereHas('variants', function ($q) {
                    // Kiểm tra biến thể không bị xóa mềm (deleted_at là null)
                    $q->whereNull('deleted_at');
                })
                    // Sắp xếp sản phẩm dựa trên giá của biến thể
                    ->orderBy(function ($query) {
                        // Chọn cột 'price' từ bảng product_variants
                        $query->select('price')
                            // Lấy từ bảng product_variants
                            ->from('product_variants')
                            // Liên kết biến thể với sản phẩm thông qua product_id
                            ->whereColumn('product_id', 'products.id')
                            // Chỉ lấy biến thể chưa bị xóa mềm
                            ->whereNull('deleted_at')
                            // Lựa chọn biến thể mặc định hoặc biến thể có id nhỏ nhất
                            ->where(function ($q) {
                                // Ưu tiên biến thể được đánh dấu là mặc định
                                $q->where('is_default', true)
                                    // Nếu không có biến thể mặc định, lấy biến thể có id nhỏ nhất
                                    ->orWhereRaw('id = (
                                select min(id) 
                                from product_variants pv 
                                where pv.product_id = product_variants.product_id 
                                and pv.deleted_at is null
                            )');
                            })
                            // Giới hạn chỉ lấy 1 biến thể để sử dụng giá của nó cho việc sắp xếp
                            ->limit(1);
                    });
                // Thoát khỏi case này
                break;

            // Trường hợp 3: Sắp xếp theo giá từ cao đến thấp
            case 'giá_cao_đến_thấp':
                // Đảm bảo chỉ lấy các sản phẩm có biến thể chưa bị xóa mềm
                $query->whereHas('variants', function ($q) {
                    // Kiểm tra biến thể không bị xóa mềm (deleted_at là null)
                    $q->whereNull('deleted_at');
                })
                    // Sắp xếp sản phẩm dựa trên giá của biến thể, theo thứ tự giảm dần
                    ->orderByDesc(function ($query) {
                        // Chọn cột 'price' từ bảng product_variants
                        $query->select('price')
                            // Lấy từ bảng product_variants
                            ->from('product_variants')
                            // Liên kết biến thể với sản phẩm thông qua product_id
                            ->whereColumn('product_id', 'products.id')
                            // Chỉ lấy biến thể chưa bị xóa mềm
                            ->whereNull('deleted_at')
                            // Lựa chọn biến thể mặc định hoặc biến thể có id nhỏ nhất
                            ->where(function ($q) {
                                // Ưu tiên biến thể được đánh dấu là mặc định
                                $q->where('is_default', true)
                                    // Nếu không có biến thể mặc định, lấy biến thể có id nhỏ nhất
                                    ->orWhereRaw('id = (
                                select min(id) 
                                from product_variants pv 
                                where pv.product_id = product_variants.product_id 
                                and pv.deleted_at is null
                            )');
                            })
                            // Giới hạn chỉ lấy 1 biến thể để sử dụng giá của nó cho việc sắp xếp
                            ->limit(1);
                    });
                // Thoát khỏi case này
                break;

            // Trường hợp 4: Lọc và sắp xếp theo sản phẩm đang giảm giá
            case 'dang_giam_gia':
                // Lọc các sản phẩm có biến thể đang trong chương trình giảm giá
                $query->whereHas('variants', function ($q) use ($now) {
                    // Kiểm tra biến thể có giá giảm giá (sale_price) không null
                    $q->whereNotNull('sale_price')
                        // Kiểm tra biến thể có thời gian bắt đầu giảm giá không null
                        ->whereNotNull('sale_price_starts_at')
                        // Kiểm tra biến thể có thời gian kết thúc giảm giá không null
                        ->whereNotNull('sale_price_ends_at')
                        // Đảm bảo giá giảm giá lớn hơn 0
                        ->where('sale_price', '>', 0)
                        // Thời gian bắt đầu giảm giá phải nhỏ hơn hoặc bằng thời điểm hiện tại
                        ->where('sale_price_starts_at', '<=', $now)
                        // Thời gian kết thúc giảm giá phải lớn hơn hoặc bằng thời điểm hiện tại
                        ->where('sale_price_ends_at', '>=', $now);
                })
                    // Sắp xếp kết quả theo thời gian tạo giảm dần (mới nhất lên đầu)
                    ->orderByDesc('created_at');
                // Thoát khỏi case này
                break;

            // Trường hợp mặc định: Khi không có tham số sort hoặc giá trị không hợp lệ
            default:
                // Chỉ lấy các sản phẩm được đánh dấu là nổi bật (is_featured = 1)
                $query->where('is_featured', 1)
                    // Sắp xếp theo thời gian tạo giảm dần (mới nhất lên đầu)
                    ->orderByDesc('created_at');
                // Thoát khỏi case mặc định
                break;
        }

        // Phân trang kết quả, mỗi trang 15 sản phẩm
        $products = $query->paginate(15);

        // Tính điểm đánh giá trung bình và phần trăm giảm giá cho từng sản phẩm
        foreach ($products as $product) {
            // Tính điểm đánh giá trung bình, làm tròn đến 1 chữ số thập phân
            $product->average_rating = round($product->reviews->avg('rating') ?? 0, 1);

            // Lấy biến thể đầu tiên của sản phẩm
            $variant = $product->variants->first();
            // Kiểm tra nếu biến thể tồn tại và có thông tin giảm giá
            if ($variant && $variant->sale_price && $variant->sale_price_starts_at && $variant->sale_price_ends_at) {
                // Kiểm tra xem sản phẩm có đang trong thời gian giảm giá không
                $onSale = $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);
                // Tính phần trăm giảm giá nếu đang giảm giá, nếu không thì gán bằng 0
                $variant->discount_percent = $onSale
                    ? round(100 - ($variant->sale_price / $variant->price) * 100)
                    : 0;
            }
        }

        // Lấy tất cả danh mục để hiển thị trong giao diện (ví dụ: bộ lọc danh mục)
        $categories = Category::all();

        // Trả về giao diện 'users.shop' với dữ liệu sản phẩm và danh mục
        return view('users.shop', compact('products', 'categories'));
    }
}
