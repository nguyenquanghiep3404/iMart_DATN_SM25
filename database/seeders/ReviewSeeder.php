<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\OrderItem;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Review::truncate(); // Cẩn thận
        $orderItems = OrderItem::query()
            ->whereHas('order', fn($q) => $q->where('status', 'delivered')) // Chỉ đánh giá đơn đã giao
            ->inRandomOrder()
            ->take(30) // Giới hạn số lượng đánh giá
            ->get();

        if ($orderItems->isEmpty()) {
            $this->command->warn('No delivered order items found to create reviews. Seeding some random reviews.');
            Review::factory(20)->create(); // Tạo review ngẫu nhiên nếu không có order item phù hợp
            return;
        }

        foreach ($orderItems as $item) {
            // Mỗi item chỉ có 1 review
            if (!Review::where('order_item_id', $item->id)->exists()) {
                 Review::factory()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'user_id' => $item->order->user_id,
                    'order_item_id' => $item->id,
                    'is_verified_purchase' => true,
                    'status' => 'approved', // Tự động duyệt review từ seeder
                ]);
            }
        }
        $this->command->info('Reviews seeded successfully!');
    }
}
