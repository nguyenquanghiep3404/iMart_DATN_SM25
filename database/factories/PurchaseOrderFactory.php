<?php
namespace Database\Factories;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'po_code' => 'PO-' . Str::upper(Str::random(8)),
            'status' => $this->faker->randomElement(['pending', 'completed']),
            'order_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}