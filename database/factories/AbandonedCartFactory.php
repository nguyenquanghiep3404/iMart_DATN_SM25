<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AbandonedCartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cart_id' => Cart::inRandomOrder()->first()?->id ?? Cart::factory()->create()->id, 
            'user_id' => $this->faker->boolean(70) ? User::inRandomOrder()->first()?->id : null,
            'guest_email' => $this->faker->safeEmail(),
            'status' => $this->faker->randomElement(['pending', 'recovered', 'archived']),
            'email_status' => $this->faker->randomElement(['unsent', 'sent']),
            'in_app_notification_status' => $this->faker->randomElement(['unsent', 'sent']),
            'recovery_token' => Str::random(40),
            'last_notified_at' => $this->faker->optional(0.5)->dateTimeBetween('-7 days', 'now'), // 50% xác suất là null
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
