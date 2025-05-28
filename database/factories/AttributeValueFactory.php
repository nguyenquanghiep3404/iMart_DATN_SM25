<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attribute;
use App\Models\AttributeValue;
class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'value' => $this->faker->unique()->word,
            'meta' => null,
        ];
    }

    public function forAttribute(Attribute $attribute, string $value, ?string $meta = null): static
    {
        return $this->state(fn(array $attributes) => [
            'attribute_id' => $attribute->id,
            'value' => $value,
            'meta' => $meta,
        ]);
    }
}
