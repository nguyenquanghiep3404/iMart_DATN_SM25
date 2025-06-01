<?php

namespace Database\Factories;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['Màu sắc', 'Kích thước', 'Dung lượng lưu trữ', 'RAM', 'Chất liệu', 'Bộ vi xử lý', 'Loại màn hình', 'Độ phân giải']);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'display_type' => $this->faker->randomElement(['select', 'radio', 'color_swatch']),
        ];
    }
}
