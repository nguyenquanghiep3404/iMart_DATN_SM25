<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WarrantyClaim;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WarrantyClaimFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WarrantyClaim::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // SỬA ĐỔI: Tìm một OrderItem thuộc về một Order có user_id không null.
        $orderItem = OrderItem::whereHas('order', function ($query) {
            $query->whereNotNull('user_id');
        })->inRandomOrder()->first();

        // Fallback: Nếu không tìm thấy OrderItem phù hợp (ví dụ: tất cả đơn hàng đều là của khách)
        // thì tạo một bộ dữ liệu mẫu hoàn chỉnh để đảm bảo seeder không bị lỗi.
        if (!$orderItem) {
            $user = User::factory()->create(); // Tạo user mới
            $order = Order::factory()->create(['user_id' => $user->id]); // Tạo order cho user đó
            $orderItem = OrderItem::factory()->create(['order_id' => $order->id]); // Tạo order item cho order đó
        }

        return [
            'user_id' => $orderItem->order->user_id,
            'order_item_id' => $orderItem->id,
            'claim_code' => 'WC-' . Str::upper(Str::random(8)),
            'reported_defect' => 'Sản phẩm ' . $this->faker->randomElement(['bị lỗi màn hình', 'không lên nguồn', 'sạc không vào']),
            'status' => 'pending_review',
        ];
    }
}
