<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use App\Models\AbandonedCart;
use Illuminate\Support\Facades\DB;
use App\Notifications\AbandonedCartReminder;

class DetectAbandonedCarts extends Command
{
    protected $signature = 'app:detect-abandoned-carts';
    protected $description = 'Phát hiện và lưu các giỏ hàng bị bỏ lỡ';

    public function handle()
    {
        $cutoff = now()->subMinutes(1); // Có thể là 60 hoặc 30 phút tuỳ bạn
    
        $query = Cart::with('user')
            ->where('updated_at', '<', $cutoff)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'carts.user_id')
                    ->whereNull('orders.deleted_at');
            });
    
        $carts = $query->get();
        $count = 0;
    
        foreach ($carts as $cart) {
            // Tạo hoặc cập nhật bản ghi giỏ hàng bị bỏ lỡ
            if ($cart->abandonedCart) {
                $cart->abandonedCart->update([
                    'updated_at' => now(),
                ]);
            } else {
                AbandonedCart::create([
                    'cart_id' => $cart->id,
                    'user_id' => $cart->user_id,
                    'updated_at' => now(),
                ]);
            }
    
            // Gửi thông báo nếu có user và chưa gửi lần nào
            if ($cart->user && $cart->user->unreadNotifications()
                ->where('type', AbandonedCartReminder::class)
                ->doesntExist()
            ) {
                $cart->user->notify(new AbandonedCartReminder());
            }
    
            $count++;
        }
    
        $this->info("Đã xử lý $count giỏ hàng bỏ lỡ.");
    }
    
}
