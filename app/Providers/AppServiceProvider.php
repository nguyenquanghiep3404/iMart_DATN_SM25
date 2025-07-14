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
use App\Models\Order;
use App\Observers\OrderObserver;
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
        Order::observe(OrderObserver::class);
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
            // Gate của luồng shipper
            Gate::define('access_shipper_dashboard', function (User $user) {
                // Chỉ những người dùng có vai trò 'shipper' mới được phép
                return $user->hasRole('shipper');
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
                        return [
                            'title' => $notification->data['title'] ?? 'Thông báo',
                            'message' => $notification->data['message'] ?? '',
                            'icon' => $notification->data['icon'] ?? 'default',
                            'color' => $notification->data['color'] ?? 'gray',
                            'time' => $notification->created_at->diffForHumans(),
                        ];
                    });


                $view->with(compact('unreadNotificationsCount', 'recentNotifications'));
            }
        });
        // Chia sẻ danh mục hiển thị trên header (menu client)
        View::composer('*', function ($view) {
            $menuCategories = \App\Models\Category::where('show_on_homepage', true)
                ->orderBy('order')
                ->get();

            $view->with('menuCategories', $menuCategories);
        });

        View::composer('*', function ($view) {
            $totalQuantity = 0;

            if (auth()->check() && auth()->user()->cart) {
                // Người dùng đã đăng nhập -> lấy từ DB
                $totalQuantity = auth()->user()->cart->items()->sum('quantity');
            } else {
                // Khách vãng lai -> lấy từ session
                $cart = session()->get('cart', []);
                $totalQuantity = array_sum(array_column($cart, 'quantity'));
            }

            $view->with('cartItemCount', $totalQuantity);
        });
    }
}
