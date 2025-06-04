<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardAdminController;
// use App\Http\Controllers\Admin\DashboardController as DashboardAdminController; // Alias để tránh trùng tên
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Users\HomeController;



//==========================================================================
// FRONTEND ROUTES (PUBLIC)
//==========================================================================
Route::get('/', [HomeController::class, 'index'])->name('users.home');

// Routes xác thực được định nghĩa trong auth.php (đăng nhập, đăng ký, quên mật khẩu, etc.)
require __DIR__ . '/auth.php';

// Routes yêu cầu xác thực cho người dùng thông thường
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard mặc định của Breeze
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile routes của Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


//==========================================================================
// ADMIN ROUTES
//==========================================================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth']) // Thêm middleware kiểm tra vai trò admin ở đây nếu cần, ví dụ: 'auth', 'role:admin'
    ->group(function () {
        // http://127.0.0.1:8000/admin/dashboard
        Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard');

        // Product routes
        // --- Routes cho Quản Lý Sản Phẩm ---
        // Route::resource('products', ProductController::class);
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{category}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{category}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{category}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{category}', [ProductController::class, 'destroy'])->name('products.destroy');

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

