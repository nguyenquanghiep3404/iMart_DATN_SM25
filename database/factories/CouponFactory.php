<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed_amount']);
        $value = ($type === 'percentage') ? $this->faker->numberBetween(5, 50) : $this->faker->numberBetween(10, 500) * 1000;

        return [
            'code' => strtoupper(Str::random(4) . $this->faker->numerify('####')),
            'description' => $this->faker->sentence,
            'type' => $type,
            'value' => $value,
            'max_uses' => $this->faker->optional(0.6)->numberBetween(50, 500),
            'max_uses_per_user' => $this->faker->optional(0.8)->numberBetween(1, 5),
            'min_order_amount' => $this->faker->optional(0.5)->numberBetween(100, 2000) * 1000,
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 week'),
            'end_date' => $this->faker->dateTimeBetween('+2 weeks', '+4 months'),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'is_public' => $this->faker->boolean(75),
            'created_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'admin'))->inRandomOrder()->first()?->id,
        ];
    }
}