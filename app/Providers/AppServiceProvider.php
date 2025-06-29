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

    }
}
