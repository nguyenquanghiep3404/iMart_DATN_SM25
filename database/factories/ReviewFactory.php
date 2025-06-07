<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $user = User::query()->whereHas('roles', fn($q) => $q->where('name', 'customer'))->inRandomOrder()->first() ?? User::factory()->create();
        
        $orderItem = OrderItem::query()->whereHas('order', function($q) use ($user){
            $q->where('user_id', $user->id)->where('status', 'delivered');
        })->inRandomOrder()->first();

        $productVariant = $orderItem ? $orderItem->productVariant : (ProductVariant::query()->inRandomOrder()->first() ?? ProductVariant::factory()->create());

        return [
            'product_variant_id' => $productVariant->id,
            'user_id' => $user->id,
            'order_item_id' => $orderItem?->id,
            'rating' => $this->faker->numberBetween(3, 5),
            'title' => $this->faker->optional(0.7)->catchPhrase,
            'comment' => $this->faker->paragraph(rand(1, 4)),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'is_verified_purchase' => (bool)$orderItem,
            'created_at' => $this->faker->dateTimeBetween($productVariant->created_at ?? '-1 year', 'now'),
        ];
    }
}
