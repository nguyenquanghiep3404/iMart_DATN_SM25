<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        /**
         * Hàm xử lý tính trung bình số sao cho mỗi sản phẩm.
         * Lấy trung bình các đánh giá (reviews) đã nạp sẵn trong collection $products,
         * và gán vào thuộc tính động $product->average_rating (làm tròn 1 chữ số thập phân).
         */
        $calculateAverageRating = function ($products) {
            foreach ($products as $product) {
                $averageRating = $product->reviews->avg('rating') ?? 0;
                $product->average_rating = round($averageRating, 1);
            }
        };

        /**
         * Lấy danh sách sản phẩm nổi bật (`is_featured` = 1)
         * - Đã publish
         * - Lấy thêm category, cover image, các biến thể (variants) và các đánh giá được duyệt (approved)
         * - Đếm số lượng đánh giá đã được duyệt
         */
        $featuredProducts = Product::with([
            'category',            // danh mục sản phẩm
            'coverImage',          // ảnh bìa
            'variants',            // các biến thể sản phẩm
            'reviews' => function ($query) {
                // Lọc chỉ lấy đánh giá có status = 'approved' và variant chưa bị xóa mềm
                $query->where('reviews.status', 'approved')
                    ->whereHas('productVariant', function ($q) {
                        $q->whereNull('deleted_at');
                    });
            }
        ])
            ->withCount([
                // Đếm số lượng đánh giá được duyệt (approved_reviews_count)
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('reviews.status', 'approved')
                        ->whereHas('productVariant', function ($q) {
                            $q->whereNull('deleted_at');
                        });
                }
            ])
            ->where('is_featured', 1)         // là sản phẩm nổi bật
            ->where('status', 'published')    // đã được đăng
            ->latest()                        // mới nhất
            ->take(8)                         // lấy 8 sản phẩm
            ->get();

        // Gán average_rating cho từng sản phẩm nổi bật
        $calculateAverageRating($featuredProducts);

        /**
         * Lấy các sản phẩm mới nhất
         * - Điều kiện lọc & quan hệ tương tự như trên, chỉ không có `is_featured`
         */
        $latestProducts = Product::with([
            'category',
            'coverImage',
            'variants',
            'reviews' => function ($query) {
                $query->where('reviews.status', 'approved')
                    ->whereHas('productVariant', function ($q) {
                        $q->whereNull('deleted_at');
                    });
            }
        ])
            ->withCount([
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('reviews.status', 'approved')
                        ->whereHas('productVariant', function ($q) {
                            $q->whereNull('deleted_at');
                        });
                }
            ])
            ->where('status', 'published')
            ->latest()
            ->take(8)
            ->get();

        // Gán average_rating cho từng sản phẩm mới
        $calculateAverageRating($latestProducts);

        /**
         * Lấy sản phẩm đang giảm giá (sale)
         * - Biến thể sản phẩm có sale_price và nằm trong thời gian sale
         */
        $saleProducts = Product::with([
            'category',
            'coverImage',
            'variants',
            'reviews' => function ($query) {
                $query->where('reviews.status', 'approved')
                    ->whereHas('productVariant', function ($q) {
                        $q->whereNull('deleted_at');
                    });
            }
        ])
            ->withCount([
                'reviews as approved_reviews_count' => function ($query) {
                    $query->where('reviews.status', 'approved')
                        ->whereHas('productVariant', function ($q) {
                            $q->whereNull('deleted_at');
                        });
                }
            ])
            ->whereHas('variants', function ($query) {
                $query->whereNotNull('sale_price')                  // có giá giảm
                    ->where('sale_price_starts_at', '<=', now())  // đã bắt đầu giảm giá
                    ->where('sale_price_ends_at', '>=', now());   // chưa hết giảm giá
            })
            ->where('status', 'published')
            ->latest()
            ->take(8)
            ->get();

        // Gán average_rating cho từng sản phẩm giảm giá
        $calculateAverageRating($saleProducts);

        /**
         * Trả về view trang chủ với 3 danh sách sản phẩm:
         * - featuredProducts: sản phẩm nổi bật
         * - latestProducts: sản phẩm mới nhất
         * - saleProducts: sản phẩm đang giảm giá
         */
        return view('users.home', compact('featuredProducts', 'latestProducts', 'saleProducts'));
    }


    public function show($slug)
    {
        // Lấy sản phẩm theo slug, kèm các quan hệ cần thiết
        $product = Product::with([
            'category',                          // Danh mục của sản phẩm
            'coverImage',                        // Ảnh đại diện
            'galleryImages',                     // Thư viện ảnh
            'variants.attributeValues.attribute', // Biến thể và các giá trị thuộc tính (VD: màu sắc, dung lượng,...)
            'reviews' => function ($query) {
                // Chỉ lấy các đánh giá đã được duyệt
                $query->where('reviews.status', 'approved');
            }
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
        // Sau đó nhóm theo tên thuộc tính, loại bỏ các giá trị bị trùng theo 'value'
        $attributes = $product->variants
            ->flatMap(fn($variant) => $variant->attributeValues)               // Gom toàn bộ các attributeValues
            ->groupBy(fn($attrValue) => $attrValue->attribute->name)           // Nhóm theo tên thuộc tính (ví dụ: Màu sắc, Kích thước,...)
            ->map(fn($group) => $group->unique('value'));                      // Loại bỏ giá trị trùng (VD: tránh lặp lại "11 inch")


        // Lấy 4 sản phẩm liên quan (cùng category, không lấy chính sản phẩm hiện tại)
        $relatedProducts = Product::with(['category', 'coverImage'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->take(4)
            ->get();

        // Trả dữ liệu về view hiển thị chi tiết sản phẩm
        return view('users.show', compact(
            'product',
            'relatedProducts',
            'ratingCounts',
            'ratingPercentages',
            'totalReviews',
            'attributes'
        ));
    }
}
