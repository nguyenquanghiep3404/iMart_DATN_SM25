<?php

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Users\HomeController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PostTagController;
use App\Http\Controllers\Users\WishlistController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Shipper\ShipperController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\UploadedFileController;
use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Users\CartController;

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('cart/remove', [CartController::class, 'removeItem'])->name('cart.removeItem');
Route::post('/cart/apply-voucher-ajax', [CartController::class, 'applyVoucherAjax'])->name('cart.applyVoucherAjax');





//==========================================================================
// FRONTEND ROUTES (PUBLIC)
//==========================================================================
Route::get('/', [HomeController::class, 'index'])->name('users.home');  // Trang chủ, không cần đăng nhập
Route::get('/san-pham/{slug}', [HomeController::class, 'show'])->name('users.products.show');
Route::get('/danh-muc-san-pham/{id}-{slug}', [HomeController::class, 'allProducts'])->name('products.byCategory');
Route::get('/danh-muc-san-pham', [HomeController::class, 'allProducts'])->name('users.products.all');
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('/gemini-chat', [AiController::class, 'generateContent']);
// các trang không cần đăng nhập ở dưới đây

// Routes cho người dùng (các tính năng phải đăng nhập mới dùng được. ví dụ: quản lý tài khoản phía người dùng)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{id}', [ReviewController::class, 'show'])->name('reviews.show');
});

// Hiển thị trang wishlist cho khách vãng lai và user
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::get('/shop/product/{id}', [ProductController::class, 'show'])->name('shop.product.show');
Route::post('/wishlist/remove-selected', [WishlistController::class, 'removeSelected'])->name('wishlist.removeSelected');

 // router cart
 Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
 Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
 // routes/web.php
 Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity'])->name('cart.updateQuantity');

//==========================================================================
// ADMIN ROUTES
//==========================================================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin,content_manager', 'check.content.access'])
    ->middleware(['auth', 'verified'])
    ->group(function () {
        // http://127.0.0.1:8000/admin/dashboard
        Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('dashboard')->middleware('can:access_admin_dashboard');

        // --- Routes cho Quản Lý Sản Phẩm ---
        Route::get('/products/trash', [ProductController::class, 'trash'])->name('products.trash');
        Route::patch('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
        Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force-delete');
        Route::post('/products/ai/generate-content', [AiController::class, 'generateContent'])
            ->name('products.ai.generate'); 
        // Route riêng cho việc xóa ảnh gallery
        Route::delete('products/gallery-images/{uploadedFile}', [ProductController::class, 'deleteGalleryImage'])
            ->name('products.gallery.delete');
        Route::resource('products', ProductController::class);
        // User routes
        // --- Routes cho Quản Lí Người Dùng ---
        // Route::resource('users', UserController::class);
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // --- Routes cho Thư viện Media ---
        // 1. Route hiển thị trang chính của thư viện
        Route::get('/media', [UploadedFileController::class, 'index'])->name('media.index');
        // 2. Route xử lý việc tải file lên (sẽ được gọi bằng AJAX)
        Route::post('/media', [UploadedFileController::class, 'store'])->name('media.store');
        // 3. Route xử lý việc cập nhật thông tin file (sửa alt text, v.v. - AJAX)
        Route::patch('/media/{uploadedFile}', [UploadedFileController::class, 'update'])->name('media.update');
        // 4. Route xử lý việc xóa một file (AJAX)
        Route::delete('/media/{uploadedFile}', [UploadedFileController::class, 'destroy'])->name('media.destroy');
        Route::get('/media/fetch', [UploadedFileController::class, 'fetchForModal'])->name('admin.media.fetch');

        // Route quản lí vai trò
        Route::resource('roles', RoleController::class);

        // 1. Route hiển thị trang chính của thư viện
        Route::get('/media', [UploadedFileController::class, 'index'])->name('media.index');
        // 2. Route xử lý việc tải file lên (sẽ được gọi bằng AJAX)
        Route::post('/media', [UploadedFileController::class, 'store'])->name('media.store');
        // 3. Route xử lý việc cập nhật thông tin file (sửa alt text, v.v. - AJAX)
        Route::patch('/media/{uploadedFile}', [UploadedFileController::class, 'update'])->name('media.update');
        Route::delete('/media/{uploadedFile}', [UploadedFileController::class, 'destroy'])->name('media.destroy');
        Route::get('/media/fetch', [UploadedFileController::class, 'fetchForModal'])->name('media.fetchForModal');
        Route::get('/media/trash', [UploadedFileController::class, 'trash'])->name('media.trash');
        Route::post('/media/restore/{id}', [UploadedFileController::class, 'restore'])->name('media.restore');
        Route::delete('/media/force-delete/{id}', [UploadedFileController::class, 'forceDelete'])->name('media.forceDelete');
        Route::post('media/bulk-delete', [UploadedFileController::class, 'bulkDelete'])->name('media.bulk-delete');

        // Route::middleware('can:manage-content')->group(function () {
        Route::delete('products/gallery-images/{uploadedFile}', [ProductController::class, 'deleteGalleryImage'])
                ->name('products.gallery.delete');

        // Category routes
        // Route::resource('categories', CategoryController::class);
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
            Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        // });
        // Attribute routes
        // Route::middleware('can:manage-attributes')->group(function () {
        Route::resource('attributes', AttributeController::class);

        // Routes cho quản lý Giá trị Thuộc tính (Attribute Values)
        Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
        Route::put('attributes/{attribute}/values/{value}', [AttributeController::class, 'updateValue'])->name('attributes.values.update');
        Route::delete('attributes/{attribute}/values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');
        // Review routes
        // Admin - Quản lý đánh giá
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
        Route::post('/reviews/{review}/update-status', [AdminReviewController::class, 'updateStatus'])->name('admin.reviews.updateStatus');


        // Routes Order
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::get('/orders/shippers/list', [OrderController::class, 'getShippers'])->name('orders.shippers');
        Route::patch('/orders/{order}/assign-shipper', [OrderController::class, 'assignShipper'])->name('orders.assignShipper');



        // Banner routes
        Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
        Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
        Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
        Route::get('/banners/{banner}', [BannerController::class, 'show'])->name('banners.show');
        Route::get('/banners/{banner}/edit', [BannerController::class, 'edit'])->name('banners.edit');
        Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');



        // Quản lý comment
        Route::get('/comments', [CommentController::class, 'index'])->name('comment.index');
        Route::get('products/{id}-{slug}', [ProductController::class, 'show'])->name('products.show');
        Route::get('posts/{id}-{slug}', [PostController::class, 'show'])->name('posts.show');

        Route::get('comments/{comment}', [CommentController::class, 'show'])->name('comments.show');
        Route::get('comments/{comment}/edit', [CommentController::class, 'edit'])->name('comments.edit');
        Route::post('comments/{comment}/status', [CommentController::class, 'updateStatus'])->name('comments.updateStatus');
        Route::post('comment/replies', [CommentController::class, 'replyStore'])->name('replies.store');

        //quản lý danh mục bài viết
        Route::get('categories_post/create-with-children', [PostCategoryController::class, 'createWithChildren'])
        ->name('categories_post.createWithChildren');

        // Route để lưu danh mục cha và con
        Route::post('categories_post/store-with-children', [PostCategoryController::class, 'storeWithChildren'])
        ->name('categories_post.storeWithChildren');

        // Route resource mặc định
        Route::resource('categories_post', PostCategoryController::class)
        ->names('categories_post');

        // Post routes
        Route::get('posts/trashed', [PostController::class, 'trashed'])->name('posts.trashed'); // Danh sách bài đã xóa
        Route::get('posts/preview/{id}', [PostController::class, 'preview'])->name('posts.preview');
        Route::put('posts/{id}/restore', [PostController::class, 'restore'])->name('posts.restore'); // Khôi phục
        Route::delete('posts/{id}/force-delete', [PostController::class, 'forceDelete'])->name('posts.forceDelete'); // Xóa vĩnh viễn
        Route::post('posts/upload-image', [PostController::class, 'uploadImage'])->name('posts.uploadImage');

        Route::resource('posts', PostController::class);
        Route::resource('post-tags', PostTagController::class);

        // Routes Coupon
        Route::resource('coupons', CouponController::class);
        Route::get('coupons/{coupon}/usage-history', [CouponController::class, 'usageHistory'])->name('coupons.usageHistory');
        Route::get('coupons/{coupon}/status/{status}', [CouponController::class, 'changeStatus'])->name('coupons.changeStatus');
        Route::post('coupons/validate', [CouponController::class, 'validateCoupon'])->name('coupons.validate');

    });

        // Group các route dành cho shipper và bảo vệ chúng
        Route::middleware(['auth', 'verified'])->prefix('shipper')->name('shipper.')->group(function () {

            // Màn hình Dashboard chính
            Route::get('/dashboard', [ShipperController::class, 'dashboard'])->name('dashboard');
            // Route để lấy thông tin chi tiết của một đơn hàng (dùng cho AJAX)
            Route::get('/orders/{order}', [ShipperController::class, 'show'])->name('orders.show');
            // Route để cập nhật trạng thái đơn hàng (dùng cho AJAX)
            Route::patch('/orders/{order}/update-status', [ShipperController::class, 'updateStatus'])->name('orders.updateStatus');

        });



// Routes xác thực được định nghĩa trong auth.php (đăng nhập, đăng ký, quên mật khẩu, etc.)
require __DIR__ . '/auth.php';
