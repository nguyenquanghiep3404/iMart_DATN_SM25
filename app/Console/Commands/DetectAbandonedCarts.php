<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use App\Models\AbandonedCart;
use Illuminate\Support\Facades\DB;

class DetectAbandonedCarts extends Command
{
    protected $signature = 'app:detect-abandoned-carts';
    protected $description = 'Phát hiện và lưu các giỏ hàng bị bỏ lỡ';

    public function handle()
{
    $cutoff = now()->subMinutes(1);

    $query = Cart::with('user')
        ->where('updated_at', '<', $cutoff)
        // Bỏ whereDoesntHave để lấy cả giỏ hàng đã có abandonedCart
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('orders')
                ->whereColumn('orders.user_id', 'carts.user_id')
                ->whereNull('orders.deleted_at');
        });

    $carts = $query->get();

    $count = 0;

    foreach ($carts as $cart) {
        // Kiểm tra nếu chưa có abandonedCart thì tạo, ngược lại cập nhật detected_at
        if ($cart->abandonedCart) {
            $cart->abandonedCart->update([
                'updated_at' => now(),
            ]);
        } else {
            AbandonedCart::create([
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                // 'phone_number' => optional($cart->user)->phone_number,
                'updated_at' => now(),
            ]);
        }
        $count++;
    }

    $this->info("Đã phát hiện lại $count giỏ hàng bỏ lỡ.");
}

}
