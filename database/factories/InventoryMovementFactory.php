<?php
namespace Database\Factories;
use App\Models\ProductInventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMovementFactory extends Factory
{
    public function definition(): array
    {
        $inventory = ProductInventory::query()->where('quantity', '>', 0)->inRandomOrder()->first();
        $change = $this->faker->numberBetween(-5, 5);
        
        return [
            'product_variant_id' => $inventory->product_variant_id,
            'store_location_id' => $inventory->store_location_id,
            'inventory_type' => $inventory->inventory_type,
            'quantity_change' => $change,
            'quantity_after_change' => $inventory->quantity + $change,
            'reason' => 'manual_adjustment',
            'user_id' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'admin'))->inRandomOrder()->first()->id,
            'notes' => 'Điều chỉnh thủ công từ seeder.',
        ];
    }
}