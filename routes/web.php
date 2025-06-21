<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Users\HomeController;
use App\Http\Controllers\GoogleController;

use App\Http\Controllers\Admin\UploadedFileController;
use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;


//==========================================================================
// FRONTEND ROUTES (PUBLIC)
//==========================================================================
Route::get('/', [HomeController::class, 'index'])->name('users.home');  // Trang chủ, không cần đăng nhập
Route::get('/san-pham/{slug}', [HomeController::class, 'show'])->name('users.products.show');
Route::get('/products', [HomeController::class, 'allProducts'])->name('users.products.all');
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


//==========================================================================
// ADMIN ROUTES
//==========================================================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin,content_manager'])
    ->group(function () {
        // http://127.0.0.1:8000/admin/dashboard
        Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('dashboard');

        // Product routes
        // --- Routes cho Quản Lý Sản Phẩm ---
        Route::resource('products', ProductController::class);
        // Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        // Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        // Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        // Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        // Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        // Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        // Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/ai/generate-content', [AiController::class, 'generateContent'])
         ->name('products.ai.generate');
        //  Route::post('/ai/generate-content', [AiController::class, 'generateContent'])->name('ai.generateContent');
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
Route::get('/media/fetch', [UploadedFileController::class, 'fetchForModal'])->name('media.fetchForModal');
        // Route riêng cho việc xóa ảnh gallery của sản phẩm
        // {uploadedFile} ở đây sẽ là ID của bản ghi trong bảng uploaded_files
        // Laravel sẽ tự động thực hiện Route Model Binding nếu tham số trong controller là UploadedFile $uploadedFile
        Route::delete('products/gallery-images/{uploadedFile}', [ProductController::class, 'deleteGalleryImage'])
            ->name('products.gallery.delete');
        // URL sẽ là: /admin/products/gallery-images/{id_cua_uploaded_file}

        // Category routes
        // Route::resource('categories', CategoryController::class);
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Attribute routes
        Route::resource('attributes', AttributeController::class);

        // Routes cho quản lý Giá trị Thuộc tính (Attribute Values)
        Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
        Route::put('attributes/{attribute}/values/{value}', [AttributeController::class, 'updateValue'])->name('attributes.values.update');
        Route::delete('attributes/{attribute}/values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');

        // Review routes
        // Admin - Quản lý đánh giá
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
        Route::put('/reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

        // Thêm các resource controller khác cho Orders, Users, Banners, Posts, etc.
        // Ví dụ:
        // Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->except(['create', 'store']);
    });



// Routes xác thực được định nghĩa trong auth.php (đăng nhập, đăng ký, quên mật khẩu, etc.)
require __DIR__ . '/auth.php';
