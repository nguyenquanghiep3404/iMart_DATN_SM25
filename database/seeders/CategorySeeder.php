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
            'iPhone' => [
                'iPhone 16 Series',
                'iPhone 15 Series',
                'iPhone 14 Series',
                'iPhone 13 Series',
                'iPhone 12 Series',
            ],
            'iPad' => [
                'iPad Pro',
                'iPad Air',
                'iPad mini',
                'iPad',
            ],
            'Mac' => [
                'MacBook Air',
                'MacBook Pro',
                'iMac',
                'Mac mini',
                'Mac Studio',
            ],
            'Watch' => [
                'Apple Watch Ultra',
                'Apple Watch Series 9',
                'Apple Watch SE',
            ],
            'Tai Nghe' => [
                'AirPods Pro',
                'AirPods',
                'AirPods Max',
                'HomePod',
            ],
            'Phụ kiện' => [
                'Phụ kiện iPhone',
                'Phụ kiện iPad',
                'Phụ kiện Mac',
                'Phụ kiện Apple Watch',
                'AirTag',
                'Apple Pencil',
            ],
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
