<?php

namespace Database\Factories;

use App\Models\AbandonedCart;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbandonedCartLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'abandoned_cart_id' => AbandonedCart::inRandomOrder()->first()?->id ?? AbandonedCart::factory(),
            'action' => $this->faker->randomElement([
                'sent_email',
                'sent_in_app_notification',
                'add_note',
                'manual_recovery',
            ]),
            'description' => $this->faker->sentence(),
            'causer_type' => 'App\\Models\\User',
            'causer_id' => $this->faker->numberBetween(1, 5), // chỉnh lại nếu bạn có ít admin
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
