<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $productVariant = ProductVariant::query()
                            ->where('status', 'active')
                            ->inRandomOrder()
                            ->first();

        if (!$productVariant) {
            $productVariant = ProductVariant::factory()->create(['status' => 'active', 'stock_quantity' => $this->faker->numberBetween(10, 50)]);
        }

        $quantity = $this->faker->numberBetween(1, 3);
        $price = $productVariant->sale_price ?? $productVariant->price;

        $variantAttributes = $productVariant->attributeValues->mapWithKeys(function ($attrValue) {
            return [$attrValue->attribute->name => $attrValue->value];
        })->all();

        return [
            'order_id' => Order::factory(),
            'product_variant_id' => $productVariant->id,
            'product_name' => $productVariant->product->name,
            'variant_attributes' => !empty($variantAttributes) ? json_encode($variantAttributes) : null,
            'quantity' => $quantity,
            'price' => $price,
            'total_price' => $quantity * $price,
        ];
    }
}
