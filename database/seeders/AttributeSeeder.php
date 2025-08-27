<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB; // Đã import
use Illuminate\Support\Str; // Đã import

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        // Attribute::truncate(); // Cẩn thận
        // AttributeValue::truncate(); // Cẩn thận

        $attributesData = [
             ['name' => 'Dung lượng', 'slug' => 'dung-luong', 'display_type' => 'select', 'values' => [
                ['value' => '64GB'], ['value' => '128GB'], ['value' => '256GB'],
                ['value' => '512GB'], ['value' => '1TB'], ['value' => '2TB']
            ]],
            ['name' => 'Màu sắc', 'slug' => 'mau-sac', 'display_type' => 'color_swatch', 'values' => [
                ['value' => 'Đen', 'meta' => '#000000'], ['value' => 'Trắng', 'meta' => '#FFFFFF'],
                ['value' => 'Bạc', 'meta' => '#C0C0C0'], ['value' => 'Vàng Gold', 'meta' => '#FFD700'],
                ['value' => 'Xanh Dương', 'meta' => '#007bff'], ['value' => 'Đỏ', 'meta' => '#DC3545'],
                ['value' => 'Hồng', 'meta' => '#FFC0CB'], ['value' => 'Xanh Lá', 'meta' => '#28A745'],
                ['value' => 'Titan Tự Nhiên', 'meta' => '#8A8A8D'], ['value' => 'Titan Xanh', 'meta' => '#2C3E50'],
            ]],
           
            ['name' => 'RAM', 'slug' => 'ram', 'display_type' => 'select', 'values' => [
                ['value' => '4GB'], ['value' => '6GB'], ['value' => '8GB'],
                ['value' => '12GB'], ['value' => '16GB'], ['value' => '32GB']
            ]],
            ['name' => 'Kích thước màn hình', 'slug' => 'kich-thuoc-man-hinh', 'display_type' => 'select', 'values' => [
                ['value' => '5.4 inch'], ['value' => '6.1 inch'], ['value' => '6.7 inch'],
                ['value' => '11 inch'], ['value' => '12.9 inch'], ['value' => '13.3 inch'],
                ['value' => '13.6 inch'], ['value' => '14 inch'], ['value' => '16 inch']
            ]],
             ['name' => 'Chất liệu vỏ', 'slug' => 'chat-lieu-vo', 'display_type' => 'radio', 'values' => [
                ['value' => 'Nhôm'], ['value' => 'Thép không gỉ'], ['value' => 'Titan'], ['value' => 'Nhựa Polycarbonate']
            ]],
        ];

        foreach ($attributesData as $attrData) {
            $attribute = Attribute::firstOrCreate(
                ['slug' => $attrData['slug']],
                ['name' => $attrData['name'], 'display_type' => $attrData['display_type']]
            );

            if (isset($attrData['values'])) {
                foreach ($attrData['values'] as $valueData) {
                    AttributeValue::firstOrCreate(
                        ['attribute_id' => $attribute->id, 'value' => $valueData['value']],
                        ['meta' => $valueData['meta'] ?? null]
                    );
                }
            }
        }
        $this->command->info('Attributes and Attribute Values seeded successfully!');
    }
}
