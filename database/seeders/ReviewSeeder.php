<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\OrderItem;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Nếu muốn xóa sạch dữ liệu cũ (cẩn thận khi dùng)
        // Review::truncate();

        $orderItems = OrderItem::query()
            ->whereHas('order', fn($q) => $q->where('status', 'delivered')) // Chỉ lấy đơn đã giao
            ->inRandomOrder()
            ->take(30)
            ->get();

        if ($orderItems->isEmpty()) {
            $this->command->warn('Không tìm thấy đơn hàng đã giao để tạo đánh giá. Tạo đánh giá ngẫu nhiên...');
            Review::factory(20)->create(); // Tạo review ngẫu nhiên nếu không có đơn phù hợp
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
        $this->command->info('Đã tạo đánh giá thành công!');
    }
}
