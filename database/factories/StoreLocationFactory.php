<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreLocationFactory extends Factory
{
    public function definition(): array {
        return [
            'name' => 'iMart ' . $this->faker->city,
            'address' => $this->faker->streetAddress,
            'phone' => $this->faker->phoneNumber,
            'is_active' => true,
        ];
    }
}