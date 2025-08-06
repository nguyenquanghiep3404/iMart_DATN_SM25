<?php

use App\Models\AbandonedCart;
use App\Mail\AbandonedCartMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ReviewController;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GuestOrderConfirmation;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Users\BlogController;
use App\Http\Controllers\Users\CartController;
use App\Http\Controllers\Users\ChatController;
use App\Http\Controllers\Users\HomeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\GuestReviewController;
use App\Http\Controllers\OrderRefundController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\SerialLookupController;
use App\Http\Controllers\Users\CarOffController;
use App\Http\Controllers\Admin\PostTagController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Users\CommentController;
use App\Http\Controllers\Users\PaymentController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\RegisterController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Users\WishlistController;
use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Shipper\ShipperController;
use App\Http\Controllers\Users\AddressesController;
use App\Http\Controllers\Users\UserOrderController;
use App\Http\Controllers\Admin\SalesStaffManagement;
use App\Http\Controllers\Admin\TradeInItemController;
use App\Http\Controllers\Admin\OrderManagerController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\UploadedFileController;
use App\Http\Controllers\Users\CartRecoveryController;
use App\Http\Controllers\Users\LoyaltyPointController;
use App\Http\Controllers\Admin\AbandonedCartController;
use App\Http\Controllers\Admin\BundleProductController;
use App\Http\Controllers\Admin\CustomerGroupController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\SpecificationController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\StoreLocationController;
use App\Http\Controllers\Users\TradeInPublicController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\PackingStationController;
use App\Http\Controllers\Admin\MarketingCampaignController;
use App\Http\Controllers\Admin\ShipperManagementController;
use App\Http\Controllers\Admin\SpecificationGroupController;
use App\Http\Controllers\Admin\ContentStaffManagementController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\InventoryDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;


Route::get('/logout-guest', [AuthenticatedSessionController::class, 'logoutGuest'])->name('logout.guest');

// router khôi phục giỏ hàng
Route::get('/cart/recover', [CartRecoveryController::class, 'recover'])->name('cart.restore');
Route::get('/cart/recover-result', function () {
    return view('users.cart.recover_result');
})->name('cart.recover_result');
Route::post('/comments/store', [CommentController::class, 'store'])->name('comments.store');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('cart/remove', [CartController::class, 'removeItem'])->name('cart.removeItem');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');
Route::post('/cart/apply-voucher-ajax', [CartController::class, 'applyVoucherAjax'])->name('cart.applyVoucherAjax');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/add-multiple', [CartController::class, 'addMultiple'])->name('cart.addMultiple');
Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');

Route::post('/cart/apply-points', [CartController::class, 'applyPoints'])->name('cart.applyPoints')->middleware('auth');
Route::post('/payments/apply-points', [PaymentController::class, 'applyPoints'])->name('payments.applyPoints')->middleware('auth');


// cart_offcanvas
Route::get('/cart/offcanvas', [CarOffController::class, 'index']);

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
Route::get('/hang-doi-tra/{category}/{product}', [TradeInPublicController::class, 'show'])
    ->name('public.trade-in.show');
Route::get('/hang-doi-tra/{slug}', [TradeInPublicController::class, 'category'])
    ->name('public.trade-in.category');
Route::get('/hang-doi-tra', [TradeInPublicController::class, 'index'])
    ->name('public.trade-in.index');
Route::get('/hang-doi-tra/{category}/{product}/detail', [TradeInPublicController::class, 'detail'])
    ->name('public.trade-in.detail');
// --- Routes API cho chức năng lọc ---
// Route để lấy danh sách quận/huyện theo mã tỉnh
Route::get('/api/districts-by-province', [HomeController::class, 'getDistrictsByProvince'])->name('api.districts.by.province');

// Route để lọc danh sách cửa hàng dựa trên tỉnh và quận
Route::get('/api/filter-stores', [HomeController::class, 'filterStoreLocations'])->name('api.stores.filter');

// Route để lấy danh sách tỉnh/thành phố theo biến thể sản phẩm
Route::get('/api/provinces-by-variant', [HomeController::class, 'getProvincesByVariant'])->name('api.provinces.by.variant');







Route::post('/compare-suggestions', [ProductController::class, 'compareSuggestions'])->name('products.compare_suggestions');
Route::post('/compare-suggestions', [TradeInPublicController::class, 'compareSuggestions']);
Route::post('/api/compare-suggestions', [HomeController::class, 'compareSuggestions']);
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('/gemini-chat', [AiController::class, 'generateContent']);
Route::get('/tim-kiem', [HomeController::class, 'search'])->name('users.products.search');
Route::get('/api/search-suggestions', [HomeController::class, 'searchSuggestions'])->name('search.suggestions');
Route::post('/reviews/verify-guest', [ReviewController::class, 'verifyGuest'])->name('reviews.guest.verify.post');
Route::prefix('guest-review')->controller(GuestReviewController::class)->group(function () {
    Route::post('/verify', 'verifyOrder')->name('guest.reviews.verify');
    Route::post('/submit', 'store')->name('guest.reviews.store');
});



// BLOG ROUTES (PUBLIC)
Route::prefix('blog')->group(function () {
    Route::get('/', [BlogController::class, 'home'])->name('users.blogs.home');
    Route::get('/tat-ca', [BlogController::class, 'index'])->name('users.blogs.index');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('users.blogs.show');
    Route::post('/{id}/tang-luot-xem', [BlogController::class, 'increaseViews'])
        ->name('users.blogs.increaseViews');
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

    // Routes cho quản lý địa chỉ của người dùng
    Route::resource('addresses', AddressesController::class)->except(['show']);
    Route::post('addresses/{address}/default', [AddressesController::class, 'setDefault'])->name('addresses.setDefault');
    //Routes đơn hàng của user
    Route::prefix('my-orders')->group(function () {
        Route::get('returns', [OrderRefundController::class, 'indexuser'])->name('orders.returns'); // 👈 đưa lên trên

        Route::get('/status/{status?}', [UserOrderController::class, 'index'])->name('orders.index');
        Route::get('/{id}/invoice', [UserOrderController::class, 'invoice'])->name('orders.invoice');
        Route::post('/{id}/cancel', [UserOrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/{id}', [UserOrderController::class, 'show'])->name('orders.show');
    });


    // Route lịch sử điểm thưởng
    Route::get('/my-points', [LoyaltyPointController::class, 'history'])->name('loyalty.history');
    // });

    Route::get('/refunds/{code}', [OrderRefundController::class, 'showuser'])->name('refunds.show');
    Route::post('/refunds', [OrderRefundController::class, 'store'])->name('refunds.store');
    Route::get('/refunds/create/{orderItem}', [OrderRefundController::class, 'create'])->name('refunds.create');
});
// Tách riêng route hoàn tiền ra ngoài
Route::post('/orders/refund-request', [OrderRefundController::class, 'store'])
    ->middleware(['auth']) // KHÔNG dùng 'verified'
    ->name('orders.refund.request');
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
Route::post('/cart/add-combo', [CartController::class, 'addCombo'])->name('cart.addCombo');
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

// --- Chat của khách hàng ---
Route::prefix('chat')->name('client.chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::post('/register-guest', [ChatController::class, 'registerGuest'])->name('registerGuest');
    Route::post('/send-message', [ChatController::class, 'sendMessage'])->name('sendMessage');
    Route::post('/guest-login', [ChatController::class, 'guestLogin'])->name('guestLogin');
    Route::post('/get-history', [ChatController::class, 'getHistory'])->name('getHistory');
});


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

// STORE LOCATIONS API ROUTES
//==========================================================================
Route::prefix('api/store-locations')->name('api.store-locations.')->group(function () {
    Route::get('/provinces', [PaymentController::class, 'getProvincesWithStores'])->name('provinces');
    Route::get('/districts', [PaymentController::class, 'getDistrictsWithStores'])->name('districts');
    Route::get('/stores', [PaymentController::class, 'getStoreLocations'])->name('stores');
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
        Route::post('/products/upload-image-ckeditor', [ProductController::class, 'uploadImageCkeditor'])->name('media.ckeditor_upload');
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
            Route::get('/warehouse/{warehouse}', [ShipperManagementController::class, 'showWarehouse'])->name('warehouse.show');
            Route::get('/warehouses', [ShipperManagementController::class, 'getWarehouses'])->name('warehouses');
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
        // Route::delete('products/gallery-images/{uploadedFile}', [ProductController::class, 'deleteGalleryImage'])
        //     ->name('products.gallery.delete');

        // Category routes
        // Route::resource('categories', CategoryController::class);


        // Route::middleware('can:manage-content')->group(function () {
        // Route::delete('products/gallery-images/{uploadedFile}', [ProductController::class, 'deleteGalleryImage'])
        //     ->name('products.gallery.delete');

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
        Route::post('/buy-now/clear-session', [PaymentController::class, 'handleClearBuyNowSession'])->name('buy_now.clear_session');

        // quản lý giỏ hàng lãng quên
        Route::get('/abandoned-carts', [AbandonedCartController::class, 'index'])->name('abandoned-carts.index');
        Route::get('/admin/abandoned-carts/{id}', [AbandonedCartController::class, 'show'])
            ->name('abandoned_carts.show');
        Route::post('abandoned-carts/send-inapp/{cart}', [AbandonedCartController::class, 'sendInApp'])
            ->name('abandoned_carts.send_inapp');
        Route::post('/abandoned-carts/{id}/send-email', [AbandonedCartController::class, 'sendEmail'])
            ->name('abandoned_carts.send_email');

        Route::post('/abandoned-carts/bulk-send-email', [AbandonedCartController::class, 'bulkSendEmail'])
            ->name('abandoned_carts.bulk_send_email');

        Route::post('/abandoned-carts/bulk-send-inapp', [AbandonedCartController::class, 'bulkSendInApp'])
            ->name('abandoned_carts.bulk_send_inapp');


        // quản lý máy pos
        Route::get('/registers', [RegisterController::class, 'index'])->name('registers.index');
        Route::get('registers/trashed', [RegisterController::class, 'trashed'])->name('registers.trashed');
        Route::post('/registers/save', [RegisterController::class, 'save'])->name('registers.save');
        Route::delete('/registers/{id}', [RegisterController::class, 'destroy'])->name('registers.destroy');
        Route::post('/registers/{id}/restore', [RegisterController::class, 'restore'])->name('registers.restore');
        Route::delete('/registers/{register}/force-delete', [RegisterController::class, 'forceDelete'])->name('registers.force-delete');

        // Quản Lý Nhân Viên Bán Hàng - POS
        Route::prefix('sales-staff')->name('sales-staff.')->group(function () {
            // Trang chính - Danh sách cửa hàng
            Route::get('/', [SalesStaffManagement::class, 'index'])->name('index');
            // Quản lý nhân viên theo cửa hàng
            Route::get('/stores/{storeId}/employees', [SalesStaffManagement::class, 'showEmployees'])->name('stores.employees');
            // Quản lý lịch làm việc
            Route::get('/stores/{storeId}/schedule', [SalesStaffManagement::class, 'showSchedule'])->name('stores.schedule');
            // Quản lý ca làm việc
            Route::get('/work-shifts', [SalesStaffManagement::class, 'showWorkShifts'])->name('work-shifts.index');
            
            // Routes cho thùng rác (trash)
            Route::get('/trash', [SalesStaffManagement::class, 'trash'])->name('trash');
            Route::patch('/restore/{id}', [SalesStaffManagement::class, 'restore'])->name('restore');
            Route::delete('/force-delete/{id}', [SalesStaffManagement::class, 'forceDelete'])->name('force-delete');
            
            // API Routes
            Route::prefix('api')->name('api.')->group(function () {
                // API cửa hàng
                Route::get('/stores', [SalesStaffManagement::class, 'getStores'])->name('stores');
                // API nhân viên
                Route::get('/stores/{storeId}/employees', [SalesStaffManagement::class, 'getStoreEmployees'])->name('stores.employees');
                Route::get('/stores/{storeId}/employees/{employeeId}', [SalesStaffManagement::class, 'getEmployee'])->name('stores.employees.show');
                Route::post('/employees', [SalesStaffManagement::class, 'addEmployee'])->name('employees.store');
                Route::put('/employees/{userId}', [SalesStaffManagement::class, 'updateEmployee'])->name('employees.update');
                Route::delete('/stores/{storeId}/employees/{userId}', [SalesStaffManagement::class, 'removeEmployee'])->name('employees.remove');
                // API lịch làm việc
                Route::get('/stores/{storeId}/schedule/weekly', [SalesStaffManagement::class, 'getWeeklySchedule'])->name('schedule.weekly');
                Route::post('/schedule/assign-shift', [SalesStaffManagement::class, 'assignShift'])->name('schedule.assign-shift');
                // API ca làm việc
                Route::get('/work-shifts', [SalesStaffManagement::class, 'getWorkShifts'])->name('work-shifts.list');
                Route::get('/work-shifts/{workShiftId}', [SalesStaffManagement::class, 'getWorkShift'])->name('work-shifts.show');
                Route::post('/work-shifts', [SalesStaffManagement::class, 'addWorkShift'])->name('work-shifts.store');
                Route::put('/work-shifts/{workShiftId}', [SalesStaffManagement::class, 'updateWorkShift'])->name('work-shifts.update');
                Route::delete('/work-shifts/{workShiftId}', [SalesStaffManagement::class, 'deleteWorkShift'])->name('work-shifts.destroy');
                Route::post('/work-shifts/create-default', [SalesStaffManagement::class, 'createDefaultWorkShifts'])->name('work-shifts.create-default');
                // API thống kê
                Route::get('/statistics', [SalesStaffManagement::class, 'getStaffStatistics'])->name('statistics');
            });
        });


        // quản lý khách hàng
        Route::get('customer-groups', [CustomerGroupController::class, 'index'])->name('customer-groups.index');
        Route::post('/customer-groups', [CustomerGroupController::class, 'save']);
        Route::put('/customer-groups/{id}', [CustomerGroupController::class, 'save']);
        Route::delete('/customer-groups/{id}', [CustomerGroupController::class, 'destroy']);
        Route::get('/trashed', [CustomerGroupController::class, 'trashed'])->name('trashed');
        Route::post('/customer-groups/{id}/restore', [CustomerGroupController::class, 'restore'])->name('customer-groups.restore');
        Route::post('/customer-groups/{id}/restore', [CustomerGroupController::class, 'restore'])->name('customer-groups.restore');
        Route::delete('/customer-groups/{id}/force-delete', [CustomerGroupController::class, 'forceDelete'])->name('customer-groups.forceDelete');

        // chiến dịch marketing
        Route::get('marketing-campaigns', [MarketingCampaignController::class, 'index'])->name('marketing_campaigns.index');
        Route::get('/campaigns/create', [MarketingCampaignController::class, 'create'])->name('campaigns.create');
        Route::post('/campaigns', [MarketingCampaignController::class, 'store'])->name('marketing_campaigns.store');
        Route::post('/marketing-campaigns/draft', [MarketingCampaignController::class, 'storeDraft'])->name('marketing_campaigns.storeDraft');
        Route::delete('marketing_campaigns/{id}', [MarketingCampaignController::class, 'destroy'])->name('marketing_campaigns.destroy');
        Route::get('/marketing_campaigns/trash', [MarketingCampaignController::class, 'trash'])->name('marketing_campaigns.trash');
        Route::post('marketing_campaigns/{id}/restore', [MarketingCampaignController::class, 'restore'])->name('marketing_campaigns.restore');
        Route::delete('marketing_campaigns/{id}/force-delete', [MarketingCampaignController::class, 'forceDelete'])->name('marketing_campaigns.forceDelete');
        Route::get('/marketing_campaigns/{id}', [MarketingCampaignController::class, 'show'])->name('marketing_campaigns.show');
        Route::get('/marketing_campaigns/{id}/edit', [MarketingCampaignController::class, 'edit'])->name('marketing_campaigns.edit');
        Route::put('/marketing_campaigns/{id}', [MarketingCampaignController::class, 'update'])->name('marketing_campaigns.update');
        Route::post('/marketing_campaigns/{id}/send', [MarketingCampaignController::class, 'send'])->name('marketing_campaigns.send');

        // quản lý tồn kho
        Route::get('/dashboard/inventory', [InventoryDashboardController::class, 'index'])
            ->name('dashboard.inventory');

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

        // Route quản lý Gói sản phẩm (Bundle Products)
        // Soft Delete Feature
        Route::get('bundle-products/trashed', [BundleProductController::class, 'trashed'])->name('bundle-products.trashed');
        Route::patch('bundle-products/{id}/restore', [BundleProductController::class, 'restore'])->name('bundle-products.restore');
        Route::delete('bundle-products/{id}/force-delete', [BundleProductController::class, 'forceDelete'])->name('bundle-products.forceDelete');
        Route::patch('bundle-products/restore-bulk', [BundleProductController::class, 'restoreBulk'])->name('bundle-products.restore.bulk');
        Route::delete('bundle-products/force-delete-bulk', [BundleProductController::class, 'forceDeleteBulk'])->name('bundle-products.forceDelete.bulk');

        // Routes cho tra cứu số serial
        Route::get('/serials/lookup', [SerialLookupController::class, 'showForm'])->name('serial.lookup.form');
        Route::post('/serials/lookup', [SerialLookupController::class, 'lookup'])->name('serial.lookup');

        // Gói sản phẩm
        Route::get('bundle-products', [BundleProductController::class, 'index'])->name('bundle-products.index');
        Route::get('bundle-products/create', [BundleProductController::class, 'create'])->name('bundle-products.create');
        Route::post('bundle-products', [BundleProductController::class, 'store'])->name('bundle-products.store');
        Route::get('bundle-products/{bundle}/edit', [BundleProductController::class, 'edit'])->name('bundle-products.edit');
        Route::put('bundle-products/{bundle}', [BundleProductController::class, 'update'])->name('bundle-products.update');
        Route::delete('bundle-products/{bundle}', [BundleProductController::class, 'destroy'])->name('bundle-products.destroy');
        Route::get('bundle-products/{bundle}', [BundleProductController::class, 'show'])->name('bundle-products.show');
        Route::patch('bundle-products/{bundle}/toggle-status', [BundleProductController::class, 'toggleStatus'])->name('bundle-products.toggle-status');

        // Xóa mềm gói sản phẩm

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


        Route::prefix('chat')->name('chat.')->group(function () {
            Route::get('/', [AdminChatController::class, 'index'])->name('dashboard');
            Route::post('/create-internal', [AdminChatController::class, 'createInternalChat'])->name('createInternal');
            Route::post('/{conversation}/send-message', [AdminChatController::class, 'sendMessage'])->name('sendMessage');
            Route::post('/{conversation}/close', [AdminChatController::class, 'close'])->name('close');
            Route::post('/{conversation}/invite-admin', [AdminChatController::class, 'inviteAdmin'])->name('inviteAdmin');
            Route::get('/{conversation}', [AdminChatController::class, 'show'])->name('show');
           
        });

        // Quản lý thu cũ và hàng mở hộp
        Route::get('trade-in-items/trash', [TradeInItemController::class, 'trash'])->name('trade-in-items.trash');
        Route::post('trade-in-items/{id}/restore', [TradeInItemController::class, 'restore'])->name('trade-in-items.restore');
        Route::delete('trade-in-items/{id}/force-delete', [TradeInItemController::class, 'forceDelete'])->name('trade-in-items.force-delete');
        Route::resource('trade-in-items', TradeInItemController::class);

        // Quản lí địa điểm cử hàng
        // Bắt đầu với route index, nó vẫn trả về view với dữ liệu ban đầu
        Route::get('store-locations', [StoreLocationController::class, 'index'])->name('store-locations.index');

        // Các routes AJAX cho việc thêm, sửa, xóa, lấy dữ liệu chỉnh sửa
        // POST để tạo mới
        Route::post('store-locations', [StoreLocationController::class, 'store'])->name('store-locations.store');
        // PUT/PATCH để cập nhật
        Route::put('store-locations/{storeLocation}', [StoreLocationController::class, 'update'])->name('store-locations.update');
        // DELETE để xóa mềm (destroy)
        Route::delete('store-locations/{storeLocation}', [StoreLocationController::class, 'destroy'])->name('store-locations.destroy');
        // GET để lấy dữ liệu cho modal chỉnh sửa
        Route::get('store-locations/{storeLocation}/edit', [StoreLocationController::class, 'edit'])->name('store-locations.edit');
        // PATCH để bật/tắt trạng thái
        Route::patch('store-locations/{storeLocation}/toggle-active', [StoreLocationController::class, 'toggleActive'])->name('store-locations.toggle-active');

        // Routes cho thùng rác (trashed items), khôi phục, và xóa vĩnh viễn
        Route::resource('store-locations', StoreLocationController::class)->except(['create', 'show']);
        // Các route AJAX riêng biệt vẫn giữ nguyên để tương tác động
        Route::get('store-locations/{storeLocation}/edit-data', [StoreLocationController::class, 'edit'])->name('store-locations.edit-data');
        Route::delete('store-locations/{storeLocation}/soft-delete', [StoreLocationController::class, 'destroy'])->name('store-locations.soft-delete');
        Route::get('store-locations/trashed', [StoreLocationController::class, 'trashed'])->name('store-locations.trashed');
        Route::post('store-locations/{id}/restore', [StoreLocationController::class, 'restore'])->name('store-locations.restore');
        Route::delete('store-locations/{id}/force-delete', [StoreLocationController::class, 'forceDelete'])->name('store-locations.force-delete');

        // Các routes API cho địa chỉ động (quận/huyện, phường/xã)
        Route::get('api/districts', [StoreLocationController::class, 'getDistrictsByProvince'])->name('api.districts');
        Route::get('api/wards', [StoreLocationController::class, 'getWardsByDistrict'])->name('api.wards');
        // API mới để lấy tất cả store locations cho Alpine.js
        Route::get('api/store-locations', [StoreLocationController::class, 'apiIndex'])->name('api.store-locations.index');
        // Quản lý nhà cung cấp
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/trash', [SupplierController::class, 'trash'])->name('trash');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [SupplierController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [SupplierController::class, 'forceDelete'])->name('forceDelete');
        });
        Route::prefix('refunds')->name('refunds.')->group(function () {
            Route::get('/', [OrderRefundController::class, 'index'])->name('index');
            Route::get('/{id}', [OrderRefundController::class, 'show'])->name('show');
            Route::put('/{id}/note', [OrderRefundController::class, 'updateNote'])->name('note');
            Route::put('/{id}/status', [OrderRefundController::class, 'updateStatus'])->name('update_status');
        });
        // QUẢN LÝ NHẬP KHO (PURCHASE ORDERS)
        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            // Route để tìm kiếm sản phẩm (dùng cho AJAX khi thêm sản phẩm vào phiếu)
            Route::get('/search-products', [PurchaseOrderController::class, 'searchProducts'])->name('search-products');

            // Route để nhận hàng vào kho
            Route::post('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveItems'])->name('receive');


            // --- ROUTES QUẢN LÝ ĐIỂM THƯỞNG ---
            Route::get('/loyalty-points', [App\Http\Controllers\Admin\LoyaltyPointController::class, 'index'])->name('loyalty.index');
            Route::post('/loyalty-points/adjust', [App\Http\Controllers\Admin\LoyaltyPointController::class, 'adjust'])->name('loyalty.adjust');

            // Route để hiển thị trang tiếp nhận hàng
            Route::get('/receiving', [PurchaseOrderController::class, 'showReceivingPage'])->name('receiving.index');
            // Route API để lấy danh sách PO đang chờ
            Route::get('/api/pending', [PurchaseOrderController::class, 'getPendingPurchaseOrders'])->name('api.pending');
        });
        Route::resource('purchase-orders', PurchaseOrderController::class);
        // Route để hiển thị trang Trạm Đóng Gói
        Route::prefix('packing-station')->name('packing-station.')->group(function () {
            // Route để hiển thị trang chính
            Route::get('/', [PackingStationController::class, 'index'])->name('index');

            // ==== API routes for the packing station interface ====
            // Route để lấy danh sách đơn hàng chờ đóng gói
            Route::get('/orders', [PackingStationController::class, 'getOrdersForPacking'])->name('get-orders');

            // Route để lấy chi tiết một đơn hàng
            Route::get('/orders/{id}', [PackingStationController::class, 'getOrderDetails'])->name('get-order-details');

            // Route để xác thực IMEI/Serial
            Route::post('/validate-imei', [PackingStationController::class, 'validateImei'])->name('validate-imei');

            // Route để xác nhận hoàn tất đóng gói
            Route::post('/orders/{orderId}/confirm-packing', [PackingStationController::class, 'confirmPacking'])->name('confirm-packing');
        });
        // QUẢN LÝ CHUYỂN KHO (STOCK TRANSFERS)
        Route::prefix('stock-transfers')->name('stock-transfers.')->group(function () {
            Route::get('/search-products', [StockTransferController::class, 'searchProducts'])->name('search-products');
            Route::get('/dispatch', [StockTransferController::class, 'showDispatchPage'])->name('dispatch.index');
            Route::get('/{stockTransfer}/dispatch', [StockTransferController::class, 'showDispatchPage'])->name('dispatch.show');
            Route::get('/api/pending', [StockTransferController::class, 'getPendingTransfers'])->name('api.pending');
            Route::post('/{stockTransfer}/dispatch', [StockTransferController::class, 'processDispatch'])->name('dispatch.process');
        });
        Route::resource('stock-transfers', StockTransferController::class);
        Route::get('/test-my-role', function () {
            if (!Auth::check()) {
                return 'Bạn chưa đăng nhập.';
            }

            $user = Auth::user();

            echo "<h2>Kiểm tra vai trò cho User ID: {$user->id} - {$user->name}</h2>";

            // Kiểm tra trực tiếp
            $hasRole = $user->hasAnyRole(['admin', 'super_admin']);

            echo 'Kết quả của `hasAnyRole([\'admin\', \'super_admin\'])`: ';
            echo $hasRole ? '<b>TRUE (ĐÚNG)</b>' : '<b>FALSE (SAI)</b>';

            echo "<hr>";

            // In ra tất cả các vai trò mà user này có
            $roles = $user->roles;
            if ($roles->isEmpty()) {
                echo 'User này KHÔNG có vai trò nào được gán trong bảng `role_user`.';
            } else {
                echo '<h4>Các vai trò của user này:</h4>';
                echo '<ul>';
                foreach ($roles as $role) {
                    echo "<li>ID: {$role->id} - Tên: {$role->name}</li>";
                }
                echo '</ul>';
            }
        });
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

// Test route để debug GHN API
// Route::get('/test-ghn', function() {
//     return response()->json([
//         'success' => true,
//         'message' => 'GHN route is working',
//         'timestamp' => now()
//     ]);
// });
// Route::post('/test-ghn-post', function() {
//     return response()->json([
//         'success' => true,
//         'message' => 'GHN POST route is working',
//         'data' => request()->all(),
//         'timestamp' => now()
//     ]);
// });

Route::post('/ajax/ghn/shipping-fee', [PaymentController::class, 'ajaxGhnShippingFee'])->name('ajax.ghn.shipping_fee');
// Route::get('api/old-provinces', [AddressesController::class, 'getOldProvinces']);
// Route::get('api/old-districts/{province_code}', [AddressesController::class, 'getOldDistricts']);
// Route::get('api/old-wards/{district_code}', [AddressesController::class, 'getOldWards']);
// Route::get('/bot/register-webhook', function () {
//     $url = config('app.url') . '/api/bot/webhook';
//     $response = Telegram::setWebhook(['url' => $url]);
//     return 'Webhook setup: ' . $response->getDescription();
// });
Route::get('/payments/confirm/{token}', [PaymentController::class, 'confirmPaymentByToken'])->name('payments.confirm');
