<?php

namespace Database\Factories;
use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory; // Đã import

class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    public function definition(): array
    {
        $key = $this->faker->unique()->slug(2, false);
        return [
            'key' => 'setting_' . $key,
            'value' => $this->faker->sentence,
            'group' => $this->faker->randomElement(['general', 'mail', 'payment', 'social', null]),
            'type' => 'string',
        ];
    }
}
