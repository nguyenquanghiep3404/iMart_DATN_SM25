<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\WishlistItem;
use App\Models\Wishlist; // Đã import
use App\Models\ProductVariant; // Đã import


class WishlistItemFactory extends Factory
{
    protected $model = WishlistItem::class;

    public function definition(): array
    {
        $productVariant = ProductVariant::query()->where('status', 'active')->inRandomOrder()->first() ?? ProductVariant::factory()->create();
        return [
            'wishlist_id' => Wishlist::factory(),
            'product_variant_id' => $productVariant->id,
            'added_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
