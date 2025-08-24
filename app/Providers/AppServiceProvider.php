<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
// Import các lớp cho xác thực người dùng
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Verified;
use App\Listeners\UpdateUserStatusAfterVerification;
// Import phần tích điểm người dùng
use App\Events\OrderDelivered;
use App\Listeners\AwardLoyaltyPoints;

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
use App\Models\OrderFulfillment;
use App\Observers\OrderFulfillmentObserver;
use App\Models\ProductVariant;
use App\Observers\ProductVariantObserver;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Address;




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
        OrderFulfillment::observe(OrderFulfillmentObserver::class);
        Paginator::useTailwind();
        // Xác định layout tự động cho mọi view (kể cả lỗi)
        View::composer('*', function ($view) {
            $prefix = request()->route()?->getPrefix();

            $layout = str_contains($prefix, 'admin')
                ? 'admin.layouts.app'
                : 'users.layouts.app';

            $view->with('layout', $layout);
        });

        // Đăng ký listener cho sự kiện Verified
        Event::listen(
            Verified::class,
            [UpdateUserStatusAfterVerification::class, 'handle'],
            OrderDelivered::class,
            [AwardLoyaltyPoints::class, 'handle']
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
            Gate::define('manage_chat', function (User $user) {
                return $user->hasRole('admin') || $user->hasRole('order_manager');
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

        // Quy tắc xác thực tùy chỉnh cho quyền sở hữu địa chỉ
        Validator::extend('address_ownership', function ($attribute, $value, $parameters, $validator) {
            if (!auth()->check()) {
                return false;
            }

            $address = Address::find($value);
            return $address && $address->user_id === auth()->id();
        }, 'Bạn không có quyền sử dụng địa chỉ này.');
        View::composer('*', function ($view) {
            $buyNowRoutes = [
                'payments/*',
                'payments',
                'buy-now/*',
                'thanh-toan/*',
            ];
        
            $cartRoutes = [
                'cart',
                'cart/*',
                'checkout/*',
            ];
        
            $currentRoute = request()->path();
        
            $isBuyNowRoute = collect($buyNowRoutes)->contains(fn($pattern) => request()->is($pattern));
            $isCartRoute = collect($cartRoutes)->contains(fn($pattern) => request()->is($pattern));
        
            // Nếu không phải trang mua ngay, xóa session mua ngay
            if (!$isBuyNowRoute && session()->has('buy_now_session')) {
                session()->forget(['buy_now_session', 'buy_now_coupon']);
            }
        });
    }
}
