<?php

namespace Database\Factories;
use App\Models\UploadedFile;
use App\Models\User; // Đã import
use Illuminate\Database\Eloquent\Factories\Factory; // Đã import
use Illuminate\Support\Str; // Đã import


class UploadedFileFactory extends Factory
{
    protected $model = UploadedFile::class;

    public function definition(): array
    {
        $faker = \Faker\Factory::create();
        // Cần composer require mmo/faker-picsum-provider --dev để dùng PicsumProvider
        // Nếu không, bạn có thể dùng ảnh placeholder tĩnh hoặc logic tạo file giả khác
        // $faker->addProvider(new \Mmo\Faker\PicsumProvider($faker));

        $extension = $this->faker->randomElement(['jpg', 'jpeg', 'png', 'webp', 'gif']);
        $originalName = Str::slug($this->faker->words(3, true)) . '.' . $extension;
        $filename = Str::random(40) . '.' . $extension;

        $defaultType = $this->faker->randomElement(['cover_image', 'gallery_image', 'avatar', 'category_image', 'banner_desktop', 'variant_image']);
        $subPath = 'uploads/' . Str::plural(Str::snake($defaultType));

        return [
            'path' => $subPath . '/' . $filename,
            'filename' => $filename,
            'original_name' => $originalName,
            'mime_type' => 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension),
            'size' => $this->faker->numberBetween(50 * 1024, 2 * 1024 * 1024),
            'disk' => 'public',
            'type' => $defaultType,
            'order' => $this->faker->numberBetween(0, 10),
            'alt_text' => $this->faker->optional(0.7)->sentence,
            'user_id' => User::query()->inRandomOrder()->first()?->id,
        ];
    }

    public function attachedTo($model, string $fileType = null, int $order = 0): static
    {
        $subPath = 'uploads/' . Str::plural(Str::snake($fileType ?: 'general'));
        $extension = $this->faker->randomElement(['jpg', 'jpeg', 'png', 'webp']);
        $filename = Str::random(40) . '.' . $extension;

        return $this->state(fn (array $attributes) => [
            'attachable_id' => $model->id,
            'attachable_type' => get_class($model),
            'type' => $fileType,
            'order' => $order,
            'path' => $subPath . '/' . $filename,
            'filename' => $filename,
            'mime_type' => 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension),
            'original_name' => Str::slug($this->faker->words(3, true)) . '.' . $extension,
        ]);
    }
}