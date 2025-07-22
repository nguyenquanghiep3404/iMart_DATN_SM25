<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Users\BlogController;
use App\Http\Controllers\Users\CartController;
use App\Http\Controllers\Users\HomeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Users\CarOffController;
use App\Http\Controllers\Admin\PostTagController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Users\CommentController;
use App\Http\Controllers\Users\PaymentController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Users\WishlistController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Shipper\ShipperController;
use App\Http\Controllers\Users\UserOrderController;
use App\Http\Controllers\Admin\OrderManagerController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\UploadedFileController;
use App\Http\Controllers\Admin\SpecificationController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\ShipperManagementController;
use App\Http\Controllers\Admin\SpecificationGroupController;
use App\Http\Controllers\Admin\ContentStaffManagementController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;


Route::post('/comments/store', [CommentController::class, 'store'])->name('comments.store');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('cart/remove', [CartController::class, 'removeItem'])->name('cart.removeItem');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');
Route::post('/cart/apply-voucher-ajax', [CartController::class, 'applyVoucherAjax'])->name('cart.applyVoucherAjax');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/add-multiple', [CartController::class, 'addMultiple'])->name('cart.addMultiple');
Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');


// cart_offcanvas
Route::get('/cart/offcanvas', [CarOffController::class, 'index']);
// Route::post('/vnpay/payment', [VNPayController::class, 'createPayment'])->name('vnpay.payment');
// Route::get('/vnpay/return', [VNPayController::class, 'handleReturn'])->name('vnpay.return');


Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::post('/process', [PaymentController::class, 'processOrder'])->name('process');
    Route::get('/success', [PaymentController::class, 'success'])->name('success');

    // Routes cho VNPay - nguyenquanghiep3404
    Route::get('/vnpay-return', [PaymentController::class, 'vnpayReturn'])->name('vnpay.return');
    Route::get('/vnpay-ipn', [PaymentController::class, 'vnpayIpn'])->name('vnpay.ipn');
    // Routes cho Momo - nguyenquanghiep3404
    Route::get('/momo-return', [PaymentController::class, 'momoReturn'])->name('momo.return');
    Route::post('/momo-ipn', [PaymentController::class, 'momoIpn'])->name('momo.ipn'); // MoMo IPN dùng phương thức POST

    // Routes cho thanh toán qr tự xây- nguyenquanghiep3404
    Route::get('/bank-transfer-qr/{order}', [PaymentController::class, 'showBankTransferQr'])->name('bank_transfer_qr');
});
//==========================================================================
// FRONTEND ROUTES (PUBLIC)
//==========================================================================
Route::get('/', [HomeController::class, 'index'])->name('users.home');  // Trang chủ, không cần đăng nhập
Route::get('/san-pham/{slug}', [HomeController::class, 'show'])->name('users.products.show');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/danh-muc-san-pham/{id}-{slug}', [HomeController::class, 'allProducts'])->name('products.byCategory');
Route::get('/danh-muc-san-pham', [HomeController::class, 'allProducts'])->name('users.products.all');
Route::post('/compare-suggestions', [ProductController::class, 'compareSuggestions'])->name('products.compare_suggestions');
Route::post('/api/compare-suggestions', [HomeController::class, 'compareSuggestions']);
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('/gemini-chat', [AiController::class, 'generateContent']);
Route::get('/tim-kiem', [HomeController::class, 'search'])->name('users.products.search');
Route::get('/api/search-suggestions', [HomeController::class, 'searchSuggestions'])->name('search.suggestions');


// BLOG ROUTES (PUBLIC)
Route::prefix('blog')->group(function () {
    Route::get('/', [BlogController::class, 'home'])->name('users.blogs.home');
    Route::get('/tat-ca', [BlogController::class, 'index'])->name('users.blogs.index');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('users.blogs.show');
});
// Trang About và Help , terms
Route::get('/about', [HomeController::class, 'about'])->name('users.about');
Route::get('/help', [HomeController::class, 'help'])->name('users.help');
Route::get('/help/{slug}', [HomeController::class, 'helpAnswer'])->name('users.help.answer');
Route::get('/terms', [HomeController::class, 'terms'])->name('users.terms');
// các trang không cần đăng nhập ở dưới đây
Route::post('/notifications/mark-as-read', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return response()->json(['status' => 'success']);
})->name('notifications.markAsRead')->middleware('auth');

// Routes cho người dùng (các tính năng phải đăng nhập mới dùng được. ví dụ: quản lý tài khoản phía người dùng)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Route cập nhật ảnh đại diện
    Route::post('/user/avatar', [ProfileController::class, 'updateAvatar'])->name('users.avatar.update');

    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{id}', [ReviewController::class, 'show'])->name('reviews.show');
    //Routes đơn hàng của user
    Route::prefix('my-orders')->group(function () {
        Route::get('/status/{status?}', [UserOrderController::class, 'index'])->name('orders.index');
        Route::get('/{id}', [UserOrderController::class, 'show'])->name('orders.show');
        Route::get('/{id}/invoice', [UserOrderController::class, 'invoice'])->name('orders.invoice');
        Route::post('/{id}/cancel', [UserOrderController::class, 'cancel'])->name('orders.cancel');
    });
});

// Hiển thị trang wishlist cho khách vãng lai và user
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::get('/shop/product/{id}', [ProductController::class, 'show'])->name('shop.product.show');
Route::post('/wishlist/remove-selected', [WishlistController::class, 'removeSelected'])->name('wishlist.removeSelected');
Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
Route::get('/product/{id}', [ProductController::class, 'show'])
    ->name('frontend.product.show');

// router cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
// routes/web.php
Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity'])->name('cart.updateQuantity');
Route::get('/session/flush-message', function () {
    session()->forget(['success', 'error']);
    return response()->noContent(); // Trả về 204
})->name('session.flush.message');
// Áp dụng mã giảm giá
Route::post('/cart/apply-voucher', [CartController::class, 'applyVoucher'])->name('cart.apply-voucher');

// Xóa mã giảm giá
Route::post('/cart/remove-voucher', [CartController::class, 'removeVoucher'])->name('cart.remove-voucher');

// Routes cho thanh toán ( Sang PaymentController )
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.information');
Route::post('/payments/process', [PaymentController::class, 'processOrder'])->name('payments.process');
Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');

// Routes cho Buy Now - phiên thanh toán riêng biệt
Route::post('/buy-now/checkout', [PaymentController::class, 'buyNowCheckout'])->name('buy-now.checkout');
Route::get('/buy-now/information', [PaymentController::class, 'buyNowInformation'])->name('buy-now.information');
Route::post('/buy-now/process', [PaymentController::class, 'processBuyNowOrder'])->name('buy-now.process');

// LOCATION API ROUTES
//==========================================================================
Route::prefix('api/locations')->name('api.locations.')->group(function () {
    // Hệ thống địa chỉ mới
    Route::get('/provinces', [LocationController::class, 'getProvinces'])->name('provinces');
    Route::get('/wards/{provinceCode}', [LocationController::class, 'getWardsByProvince'])->name('wards');
    // Hệ thống địa chỉ cũ
    Route::get('/old/provinces', [LocationController::class, 'getOldProvinces'])->name('old.provinces');
    Route::get('/old/districts/{provinceCode}', [LocationController::class, 'getOldDistrictsByProvince'])->name('old.districts');
    Route::get('/old/wards/{districtCode}', [LocationController::class, 'getOldWardsByDistrict'])->name('old.wards');
    // Kiểm tra hỗ trợ hệ thống mới
    Route::get('/check-support/{provinceCode}', [LocationController::class, 'checkNewSystemSupport'])->name('check.support');
});

// API lấy địa chỉ GHN ( Để lại nếu không cần bỏ được để xem xét)
// Route::get('/api/ghn/provinces', function() {
//     return response()->json([ 'success' => true, 'data' => \DB::table('ghn_provinces')->get() ]);
// });
// Route::get('/api/ghn/districts/{province_id}', function($province_id) {
//     return response()->json([ 'success' => true, 'data' => \DB::table('ghn_districts')->where('province_id', $province_id)->get() ]);
// });
// Route::get('/api/ghn/wards/{district_id}', function($district_id) {
//     return response()->json([ 'success' => true, 'data' => \DB::table('ghn_wards')->where('district_id', $district_id)->get() ]);
// });
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

        // Route xóa mềm người dùng
        // Route::middleware('can:is-admin')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/trash', [UserController::class, 'trash'])->name('trash');
            Route::patch('/{user}/restore', [UserController::class, 'restore'])->name('restore');
            Route::delete('/{user}/force-delete', [UserController::class, 'forceDelete'])->name('forceDelete');
        });

        Route::get('/api/specifications-by-category/{category}', [ProductController::class, 'getSpecificationsForCategory'])->name('api.specifications.by_category');
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

        // --- Routes quản lí shipper ---
        Route::prefix('shippers')->name('shippers.')->group(function () {
            Route::get('/trash', [ShipperManagementController::class, 'trash'])->name('trash');
            Route::patch('/{shipper}/restore', [ShipperManagementController::class, 'restore'])->name('restore');
            Route::delete('/{shipper}/force-delete', [ShipperManagementController::class, 'forceDelete'])->name('force-delete');
        });
        Route::resource('shippers', ShipperManagementController::class);

        // --- Routes quản lí nhân viên content ---
        Route::prefix('content-staffs')->name('content_staffs.')->group(function () {
            Route::get('/trash', [ContentStaffManagementController::class, 'trash'])->name('trash');
            Route::patch('/{contentStaff}/restore', [ContentStaffManagementController::class, 'restore'])->name('restore');
            Route::delete('/{contentStaff}/force-delete', [ContentStaffManagementController::class, 'forceDelete'])->name('force-delete');
        });
        Route::resource('content-staffs', ContentStaffManagementController::class);

        // --- Routes cho Thư viện Media ---
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [UploadedFileController::class, 'index'])->name('index');
            Route::post('/', [UploadedFileController::class, 'store'])->name('store');
            Route::get('/fetch', [UploadedFileController::class, 'fetchForModal'])->name('fetchForModal');
            Route::post('/bulk-delete', [UploadedFileController::class, 'bulkDelete'])->name('bulk-delete');

            // Thùng rác
            Route::get('/trash', [UploadedFileController::class, 'trash'])->name('trash');
            Route::post('/restore/{id}', [UploadedFileController::class, 'restore'])->name('restore');
            Route::delete('/force-delete/{id}', [UploadedFileController::class, 'forceDelete'])->name('forceDelete');

            // Routes với tham số {uploadedFile}
            Route::patch('/{uploadedFile}', [UploadedFileController::class, 'update'])->name('update');
            Route::delete('/{uploadedFile}', [UploadedFileController::class, 'destroy'])->name('destroy');
            Route::post('/{uploadedFile}/recrop', [UploadedFileController::class, 'recrop'])->name('recrop');
        });
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

        // Route riêng cho việc xóa ảnh gallery của sản phẩm
        // {uploadedFile} ở đây sẽ là ID của bản ghi trong bảng uploaded_files
        // Laravel sẽ tự động thực hiện Route Model Binding nếu tham số trong controller là UploadedFile $uploadedFile
        // Route::middleware('can:manage-content')->group(function () {
        Route::delete('products/gallery-images/{uploadedFile}', [ProductController::class, 'deleteGalleryImage'])
            ->name('products.gallery.delete');

        // Category routes
        // Route::resource('categories', CategoryController::class);


        // Route::middleware('can:manage-content')->group(function () {
        Route::delete('products/gallery-images/{uploadedFile}', [ProductController::class, 'deleteGalleryImage'])
            ->name('products.gallery.delete');

        // Category routes
        // Route::resource('categories', CategoryController::class);


        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/trash', [CategoryController::class, 'trash'])->name('categories.trash');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categories/restore/{id}', [CategoryController::class, 'restore'])->name('categories.restore');
        Route::delete('/categories/force-delete/{id}', [CategoryController::class, 'forceDelete'])->name('categories.forceDelete');
        // Route::post('/categories/{category}/toggle-homepage', [CategoryController::class, 'toggleHomepage'])->name('categories.toggleHomepage'); // ẩn hiện danh mục trên trang chủ
        // });
        // Attribute routes
        // Route::middleware('can:manage-attributes')->group(function () {
        Route::resource('attributes', AttributeController::class);

        // Routes cho quản lý Giá trị Thuộc tính (Attribute Values)
        Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
        Route::put('attributes/{attribute}/values/{value}', [AttributeController::class, 'updateValue'])->name('attributes.values.update');
        Route::delete('attributes/{attribute}/values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');

        // --- Specification Groups ---
        Route::get('specification-groups/trashed', [SpecificationGroupController::class, 'trashed'])->name('specification-groups.trashed');
        Route::post('specification-groups/{id}/restore', [SpecificationGroupController::class, 'restore'])->name('specification-groups.restore');
        Route::delete('specification-groups/{id}/force-delete', [SpecificationGroupController::class, 'forceDelete'])->name('specification-groups.forceDelete');;
        Route::resource('specification-groups', SpecificationGroupController::class);

        // --- Specifications ---
        Route::get('specifications/trashed', [SpecificationController::class, 'trashed'])->name('specifications.trashed');
        Route::post('specifications/{id}/restore', [SpecificationController::class, 'restore'])->name('specifications.restore');
        Route::delete('specifications/{id}/force-delete', [SpecificationController::class, 'forceDelete'])->name('specifications.force-delete');
        Route::resource('specifications', SpecificationController::class);
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
        Route::get('/orders/view/{order}', [OrderController::class, 'view'])->name('orders.view');



        // Banner routes

        Route::get('/banners/trash', [BannerController::class, 'trash'])->name('banners.trash');
        Route::post('/banners/{banner}/restore', [BannerController::class, 'restore'])->name('banners.restore');
        Route::delete('/banners/{banner}/force-delete', [BannerController::class, 'forceDelete'])->name('banners.forceDelete');

        Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
        Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
        Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
        Route::get('/banners/{banner}', [BannerController::class, 'show'])->name('banners.show');
        Route::get('/banners/{banner}/edit', [BannerController::class, 'edit'])->name('banners.edit');
        Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

        // quản lý nhân viên quản lý đơn hàng
        Route::get('/order-manager', [OrderManagerController::class, 'index'])->name('order-manager.index');
        Route::get('/order-manager/create', [OrderManagerController::class, 'create'])->name('order-manager.create');
        Route::get('/order-manager/{user}', [OrderManagerController::class, 'show'])->name('order-manager.show');
        Route::get('/order-manager/{user}/edit', [OrderManagerController::class, 'edit'])->name('order-manager.edit');
        Route::put('/order-manager/{user}', [OrderManagerController::class, 'update'])->name('order-manager.update');
        Route::post('/order-manager/store', [OrderManagerController::class, 'store'])->name('order-manager.store');
        Route::delete('/order-manager/{user}', [OrderManagerController::class, 'destroy'])->name('order-manager.destroy');


        // Route khác nếu cần
        Route::get('/staff', [OrderManagerController::class, 'staffIndex'])->name('staff.index');


        // Quản lý comment
        Route::get('comments/product/{product}', [AdminCommentController::class, 'byProduct'])->name('comments.byProduct');
        Route::get('/comments', [AdminCommentController::class, 'index'])->name('comment.index');
        Route::get('products/{id}-{slug}', [ProductController::class, 'show'])->name('products.show');
        Route::get('posts/{id}-{slug}', [PostController::class, 'show'])->name('posts.show');

        Route::get('comments/{comment}', [AdminCommentController::class, 'show'])->name('comments.show');
        Route::get('comments/{comment}/edit', [AdminCommentController::class, 'edit'])->name('comments.edit');
        Route::post('comments/{comment}/status', [AdminCommentController::class, 'updateStatus'])->name('comments.updateStatus');
        Route::post('comment/replies', [AdminCommentController::class, 'replyStore'])->name('replies.store');

        //quản lý danh mục bài viết
        Route::get('categories_post/create-with-children', [PostCategoryController::class, 'createWithChildren'])
            ->name('categories_post.createWithChildren');

        // Route để lưu danh mục cha và con
        Route::post('categories_post/store-with-children', [PostCategoryController::class, 'storeWithChildren'])
            ->name('categories_post.storeWithChildren');

        // Route resource mặc định
        Route::resource('categories_post', PostCategoryController::class)
            ->names('categories_post');

        // Route quản lí trang chủ (client)
        Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage.index');
        Route::post('/homepage/update', [HomepageController::class, 'update'])->name('homepage.update');
        Route::post('/homepage/banners/sort', [HomepageController::class, 'sortBanners'])->name('admin.homepage.banners.sort');
        Route::post('/homepage/categories', [HomepageController::class, 'saveCategories'])->name('admin.homepage.categories.save');
        Route::post('/homepage/categories/sort', [HomepageController::class, 'sortCategories'])->name('admin.homepage.categories.sort');
        Route::get('/homepage/categories/list', [HomepageController::class, 'getCategories'])->name('admin.homepage.categories.list');
        Route::post('/homepage/product-blocks', [HomepageController::class, 'storeProductBlock'])->name('homepage.blocks.store');
        Route::delete('/homepage/product-blocks/{id}', [HomepageController::class, 'destroyProductBlock'])->name('homepage.blocks.destroy');
        Route::get('/homepage/products/search', [HomepageController::class, 'searchProducts'])->name('homepage.products.search');
        Route::patch('/homepage/blocks/{id}/toggle-visibility', [HomepageController::class, 'toggleBlockVisibility'])
            ->name('homepage.blocks.toggleVisibility');
        Route::post('/homepage/product-blocks/update-order', [HomepageController::class, 'updateBlockOrder'])->name('homepage.blocks.update-order');
        Route::post('/homepage/product-blocks/{blockId}/update-order', [HomepageController::class, 'updateProductOrder'])
            ->name('homepage.blocks.products.update-order');
        Route::post('/homepage/banners/update-order', [HomepageController::class, 'updateBannerOrder'])->name('homepage.banners.update-order');
        Route::post('/homepage/product-blocks/sort', [HomepageController::class, 'sortProductBlocks'])->name('homepage.blocks.sort');
        Route::post('/homepage/block/{block}/add-products', [HomepageController::class, 'addProductsToBlock'])->name('homepage.blocks.add-products');
        Route::post('/homepage/product-blocks/{block}/products', [HomepageController::class, 'addProductsToBlock'])
            ->name('homepage.blocks.add-products');
        Route::patch('/homepage/categories/{categoryId}/toggle', [HomepageController::class, 'toggleCategory'])->name('homepage.categories.toggle');
        Route::post('/homepage/categories/update-order', [HomepageController::class, 'updateCategoryOrder'])->name('homepage.categories.update-order');

        // Route Quản lí Flash Sale
        Route::resource('flash-sales', \App\Http\Controllers\Admin\FlashSaleController::class);
        Route::post('flash-sales/{flash_sale}/attach-product', [FlashSaleController::class, 'attachProduct'])
            ->name('flash-sales.attachProduct');
        Route::delete('flash-sales/{flash_sale}/detach-product/{product}', [FlashSaleController::class, 'detachProduct'])
            ->name('flash-sales.detachProduct');
        Route::post('flash-sales/{flashSale}/time-slots', [FlashSaleController::class, 'addTimeSlot'])
            ->name('admin.flash-sales.time-slots.store');
        // Route cập nhật sản phẩm trong Flash Sale
        Route::put('flash-sales/{flash_sale}/update-product/{flash_product}', [FlashSaleController::class, 'updateProduct'])
            ->name('flash-sales.updateProduct');



        // Post routes
        Route::get('posts/trashed', [PostController::class, 'trashed'])->name('posts.trashed'); // Danh sách bài đã xóa
        Route::get('posts/preview/{id}', [PostController::class, 'preview'])->name('posts.preview');
        Route::put('posts/{id}/restore', [PostController::class, 'restore'])->name('posts.restore'); // Khôi phục
        Route::delete('posts/{id}/force-delete', [PostController::class, 'forceDelete'])->name('posts.forceDelete'); // Xóa vĩnh viễn
        Route::post('posts/upload-image', [PostController::class, 'uploadImage'])->name('posts.uploadImage');

        Route::resource('posts', PostController::class);
        Route::resource('post-tags', PostTagController::class);

        // Routes Coupon
        Route::get('/coupons/trash', [CouponController::class, 'trash'])->name('coupons.trash');
        Route::resource('coupons', CouponController::class);
        Route::get('coupons/{coupon}/usage-history', [CouponController::class, 'usageHistory'])->name('coupons.usageHistory');
        Route::get('coupons/{coupon}/status/{status}', [CouponController::class, 'changeStatus'])->name('coupons.changeStatus');
        Route::post('coupons/validate', [CouponController::class, 'validateCoupon'])->name('coupons.validate');
        Route::post('/coupons/restore/{id}', [CouponController::class, 'restore'])->name('coupons.restore');
        Route::delete('/coupons/force-delete/{id}', [CouponController::class, 'forceDelete'])->name('coupons.forceDelete');


        // Route::resource('orders', OrderController::class)->except(['create', 'store']);
    });
// Group các route dành cho shipper và bảo vệ chúng
Route::prefix('shipper')
    ->name('shipper.')
    ->middleware(['auth', 'verified']) // <-- Bảo vệ toàn bộ nhóm
    ->group(function () {

        // http://127.0.0.1:8000/shipper/dashboard
        Route::get('/dashboard', [ShipperController::class, 'dashboard'])->name('dashboard')->middleware('can:access_shipper_dashboard');

        // Các route khác của shipper
        Route::get('/stats', [ShipperController::class, 'stats'])->name('stats');
        Route::get('/history', [ShipperController::class, 'history'])->name('history');
        Route::get('/profile', [ShipperController::class, 'profile'])->name('profile');
        Route::get('/orders/{order}', [ShipperController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/update-status', [ShipperController::class, 'updateStatus'])->name('orders.updateStatus');
    });
Route::get('/test-403', function () {
    abort(403);
});

// Routes xác thực được định nghĩa trong auth.php (đăng nhập, đăng ký, quên mật khẩu, etc.)
require __DIR__ . '/auth.php';
Route::post('/ajax/ghn/shipping-fee', [PaymentController::class, 'ajaxGhnShippingFee'])->name('ajax.ghn.shipping_fee');
