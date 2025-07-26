<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AbandonedCart;
use App\Models\AbandonedCartLog;

class AbandonedCartSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 5 giỏ hàng bị bỏ lỡ
        AbandonedCart::factory(5)->create()->each(function ($cart) {
            // Mỗi cart có 1–3 log
            AbandonedCartLog::factory(rand(1, 3))->create([
                'abandoned_cart_id' => $cart->id,
            ]);
        });
    }
}
