<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\DB; // Đã import
use Illuminate\Support\Str; // Đã import

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Category::truncate(); // Cẩn thận

        $categoriesData = [
            'Điện thoại' => ['iPhone', 'Samsung Galaxy', 'Xiaomi', 'Oppo'],
            'Máy tính xách tay' => ['MacBook', 'Dell XPS', 'HP Spectre', 'Lenovo ThinkPad'],
            'Máy tính bảng' => ['iPad', 'Samsung Galaxy Tab', 'Lenovo Tab'],
            'Đồng hồ thông minh' => ['Apple Watch', 'Samsung Galaxy Watch', 'Garmin'],
            'Tai nghe' => ['AirPods', 'Sony WH-1000XM5', 'Bose QuietComfort', 'Sennheiser Momentum'],
            'Phụ kiện' => ['Ốp lưng', 'Sạc & Cáp', 'Bàn phím', 'Chuột', 'Loa di động'],
        ];

        foreach ($categoriesData as $parentName => $childrenNames) {
            $parentCategory = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                Category::factory()->make(['name' => $parentName, 'parent_id' => null, 'status' => 'active'])->toArray()
            );
            if (!$parentCategory->images()->where('type', 'category_image')->exists()) {
                 UploadedFile::factory()->attachedTo($parentCategory, 'category_image')->create();
            }


            if (!empty($childrenNames)) {
                foreach ($childrenNames as $childName) {
                    $childCategory = Category::firstOrCreate(
                        ['slug' => Str::slug($parentName . ' ' . $childName)],
                        Category::factory()->make(['name' => $childName, 'parent_id' => $parentCategory->id, 'status' => 'active'])->toArray()
                    );
                    if (!$childCategory->images()->where('type', 'category_image')->exists()) {
                        UploadedFile::factory()->attachedTo($childCategory, 'category_image')->create();
                    }
                }
            }
        }
        $this->command->info('Categories seeded successfully!');
    }
}
