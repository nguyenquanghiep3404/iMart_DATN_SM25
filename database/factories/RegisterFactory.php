<?php
namespace Database\Factories;
use App\Models\StoreLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegisterFactory extends Factory
{
    public function definition(): array {
        return [
            'store_location_id' => StoreLocation::factory(),
            'name' => 'Quầy thu ngân #' . $this->faker->unique()->numberBetween(1, 5),
            'device_uid' => $this->faker->uuid,
            'status' => 'active',
        ];
    }
}