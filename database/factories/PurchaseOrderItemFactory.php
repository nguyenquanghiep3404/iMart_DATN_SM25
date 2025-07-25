<?php
namespace Database\Factories;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderItemFactory extends Factory
{
    public function definition(): array
    {
        $variant = ProductVariant::query()->inRandomOrder()->first() ?? ProductVariant::factory()->create();
        $quantity = $this->faker->numberBetween(10, 100);
        $cost_price = $variant->cost_price > 0 ? $variant->cost_price : $variant->price * 0.7;

        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'cost_price' => $cost_price,
        ];
    }
}