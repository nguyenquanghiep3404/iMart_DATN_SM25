<?php
namespace Database\Factories;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReturnRequestFactory extends Factory
{
    public function definition(): array
    {
        $order = Order::query()->where('status', 'delivered')->inRandomOrder()->first();
        return [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'return_code' => 'RT-' . Str::upper(Str::random(8)),
            'status' => 'pending_review',
            'reason' => $this->faker->sentence,
        ];
    }
}