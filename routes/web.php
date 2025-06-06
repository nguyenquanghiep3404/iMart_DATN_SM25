<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Users\HomeController;



//==========================================================================
// FRONTEND ROUTES (PUBLIC)
//==========================================================================
Route::get('/', [HomeController::class, 'index'])->name('users.home');  // Trang chủ, không cần đăng nhập
// các trang không cần đăng nhập ở dưới đây

// Routes xác thực được định nghĩa trong auth.php (đăng nhập, đăng ký, quên mật khẩu, etc.)
require __DIR__ . '/auth.php';

// Routes cho người dùng (các tính năng phải đăng nhập mới dùng được. ví dụ: quản lý tài khoản phía người dùng)
Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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

        // Thêm các resource controller khác cho Orders, Users, Banners, Posts, etc.
        // Ví dụ:
        // Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->except(['create', 'store']);
    });

