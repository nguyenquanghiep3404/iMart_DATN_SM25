<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerGroupFactory extends Factory
{
    protected $model = \App\Models\CustomerGroup::class;

    public function definition()
    {
        $name = $this->faker->unique()->randomElement([
            'Khách hàng mới',
            'Khách hàng thân thiết',
            'VIP',
            'Bạc',
            'Vàng',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'min_order_count' => $this->faker->numberBetween(0, 10),
            'min_total_spent' => $this->faker->randomFloat(2, 0, 10000000),
            'priority' => $this->faker->numberBetween(1, 5),
        ];
    }
}
