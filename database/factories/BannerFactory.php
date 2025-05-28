<?php

namespace Database\Factories;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->catchPhrase,
            'link_url' => $this->faker->optional(0.8)->url,
            'position' => $this->faker->randomElement(['homepage_slider', 'sidebar_top', 'category_page_banner', 'product_detail_sidebar']),
            'order' => $this->faker->numberBetween(0, 10),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'start_date' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', '+1 week'),
            'end_date' => $this->faker->optional(0.7)->dateTimeBetween('+2 weeks', '+3 months'),
            'created_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'content_manager'))->inRandomOrder()->first()?->id,
            'updated_by' => User::query()->whereHas('roles', fn($q) => $q->where('name', 'content_manager'))->inRandomOrder()->first()?->id,
        ];
    }
}
