<?php
namespace Database\Factories;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReturnItemFactory extends Factory
{
    public function definition(): array
    {
        $returnRequest = ReturnRequest::factory()->create();
        $orderItem = $returnRequest->order->orderItems()->inRandomOrder()->first();
        return [
            'return_request_id' => $returnRequest->id,
            'order_item_id' => $orderItem->id,
            'quantity' => $this->faker->numberBetween(1, $orderItem->quantity),
            'condition' => 'opened_like_new',
            'resolution' => 'restock_as_open_box',
        ];
    }
}