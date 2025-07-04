<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
// Import các lớp cho xác thực người dùng
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Verified;
use App\Listeners\UpdateUserStatusAfterVerification;

// Import phần phân quyền
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\Role;
use App\Policies\RolePolicy;
use App\Models\Attribute;
use App\Policies\AttributePolicy;
use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Models\Category;
use App\Policies\CategoryPolicy;
use Illuminate\Support\Facades\View;
use App\Models\ProductVariant;
use App\Observers\ProductVariantObserver;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Attribute::class => AttributePolicy::class,
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,

    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ProductVariant::observe(ProductVariantObserver::class);
        Paginator::useTailwind();

        // Đăng ký listener cho sự kiện Verified
        Event::listen(
            Verified::class,
            [UpdateUserStatusAfterVerification::class, 'handle']
        );

        // Phân quyền
        try {
            // Chỉ giữ lại các Gate chung, không gắn với Model CRUD
            Gate::define('access_admin_dashboard', function (User $user) {
                // Admin được vào, hoặc những ai có quyền cụ thể
                return $user->hasRole('admin') || $user->hasPermissionTo('access_admin_dashboard');
            });

            // Không còn các Gate như 'manage-users', 'manage-roles' ở đây nữa
            // vì chúng đã được chuyển vào các file Policy tương ứng.

        } catch (\Exception $e) {
            return;
        }


        View::composer('*', function ($view) {
            $user = auth()->user();
            if ($user) {
                $unreadNotificationsCount = $user->unreadNotifications()->count();

                $recentNotifications = $user->notifications()
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function ($notification) {
                        $message = $notification->data['message'] ?? 'Thông báo mới';
                        $type = class_basename($notification->type);
                        $icon = match (true) {
                            str_contains($type, 'User') => '<svg class="h-6 w-6 text-green-500" ...>...</svg>',
                            str_contains($type, 'Order') => '<svg class="h-6 w-6 text-blue-500" ...>...</svg>',
                            str_contains($type, 'Review') => '<svg class="h-6 w-6 text-yellow-500" ...>...</svg>',
                            default => '<svg class="h-6 w-6 text-gray-400" ...>...</svg>',
                        };

                        return [
                            'title' => $message,
                            'time' => $notification->created_at->diffForHumans(),
                            'icon' => $icon,
                        ];
                    });

                $view->with(compact('unreadNotificationsCount', 'recentNotifications'));
            }
        });
        // try {
        // // --- LOGIC PHÂN QUYỀN MỚI DỰA TRÊN PERMISSION ---

        // // Quy tắc "bất khả xâm phạm" cho admin: admin có tất cả các quyền.
        // // Gate::before sẽ chạy trước tất cả các Gate khác.
        // Gate::before(function (User $user, $ability) {
        //     if ($user->hasRole('admin')) {
        //         return true;
        //     }
        // });

        // // Định nghĩa Gate dựa trên Permission
        // Gate::define('manage-users', function (User $user) {
        //     return $user->hasPermissionTo('manage-users');
        // });

        // Gate::define('manage-content', function (User $user) {
        //     return $user->hasPermissionTo('manage-content');
        // });

        // Gate::define('manage-orders', function (User $user) {
        //     return $user->hasPermissionTo('manage-orders');
        // });
        // Gate::define('manage-attributes', function (User $user) {
        // // Cho phép truy cập nếu người dùng có ÍT NHẤT MỘT trong các quyền sau
        //     return $user->hasPermissionTo([
        //         'browse_attributes',
        //         'add_attributes',
        //         'edit_attributes',
        //         'delete_attributes'
        //     ]);
        // });

        // // Bạn có thể định nghĩa các quyền chi tiết hơn nữa
        // Gate::define('delete-posts', function(User $user) {
        //     return $user->hasPermissionTo('delete-posts');
        // });

        // } catch (\Exception $e) {
        //     // Bỏ qua lỗi khi migrate
        //     return;
        // }
        // App\Providers\AppServiceProvider.php
    }
}
