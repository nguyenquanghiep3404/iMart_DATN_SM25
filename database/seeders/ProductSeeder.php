<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\UploadedFile;
use App\Models\Category;
use Illuminate\Support\Facades\DB; // Đã import
use Illuminate\Support\Str; // Đã import

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Product::truncate(); // Cẩn thận
        // ProductVariant::truncate(); // Cẩn thận
        // DB::table('product_variant_attribute_values')->truncate(); // Cẩn thận
        // UploadedFile::where('attachable_type', Product::class)->delete(); // Cẩn thận
        // UploadedFile::where('attachable_type', ProductVariant::class)->delete(); // Cẩn thận

        $categories = Category::whereNotNull('parent_id')->get(); // Ưu tiên danh mục con
        if ($categories->isEmpty()) {
            $this->command->error('No sub-categories found to assign products. Please seed categories first.');
            return;
        }

        $allAttributes = Attribute::with('attributeValues')->get();
        if ($allAttributes->isEmpty()) {
            $this->command->warn('No attributes found. Variable products might not have variants with attributes.');
        }

        Product::factory(30)->create()->each(function ($product) use ($allAttributes) {
            // Tạo ảnh bìa
            UploadedFile::factory()->attachedTo($product, 'cover_image')->create();
            // Tạo ảnh gallery
            UploadedFile::factory(rand(2, 5))->attachedTo($product, 'gallery_image')->create();

            if ($product->type === 'simple') {
                ProductVariant::factory()->create([
                    'product_id' => $product->id,
                    'is_default' => true,
                    'stock_quantity' => rand(10, 100) // Đảm bảo sản phẩm đơn giản có hàng
                ]);
            } elseif ($product->type === 'variable' && $allAttributes->isNotEmpty()) {
                $numberOfVariantsToCreate = rand(2, 5);
                $createdVariantsCount = 0;

                // Lấy ngẫu nhiên 1-3 thuộc tính để tạo biến thể
                $attributesForProduct = $allAttributes->count() > 1 ? $allAttributes->random(min($allAttributes->count(), rand(1, 3))) : $allAttributes;

                if ($attributesForProduct->isEmpty()) return;

                $possibleAttributeValues = [];
                foreach ($attributesForProduct as $attribute) {
                    if ($attribute->attributeValues->isNotEmpty()) {
                        $possibleAttributeValues[] = $attribute->attributeValues->random(min($attribute->attributeValues->count(), rand(1,3)))->pluck('id')->toArray();
                    }
                }

                if (empty($possibleAttributeValues)) return;

                // Tạo tổ hợp các giá trị thuộc tính (Cartesian product)
                $combinations = $this->generateCombinations($possibleAttributeValues);

                foreach ($combinations as $combination) {
                    if ($createdVariantsCount >= $numberOfVariantsToCreate) break;

                    $variant = ProductVariant::factory()->create([
                        'product_id' => $product->id,
                        'is_default' => ($createdVariantsCount === 0), // Biến thể đầu tiên là default
                        'stock_quantity' => rand(5, 50)
                    ]);
                    $variant->attributeValues()->attach($combination);
                    // Có thể thêm ảnh riêng cho biến thể ở đây nếu muốn
                    // UploadedFile::factory()->attachedTo($variant, 'variant_image')->create();
                    $createdVariantsCount++;
                }
                 // Nếu không tạo được biến thể nào (do tổ hợp rỗng), tạo 1 biến thể mặc định không có thuộc tính
                if ($createdVariantsCount === 0) {
                     ProductVariant::factory()->create([
                        'product_id' => $product->id,
                        'is_default' => true,
                        'stock_quantity' => rand(10, 100)
                    ]);
                }
            }
        });

        $this->command->info('Products and Variants seeded successfully!');
    }

    private function generateCombinations(array $arrays, $i = 0) {
        if (!isset($arrays[$i])) {
            return [];
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        $tmp = $this->generateCombinations($arrays, $i + 1);
        $result = [];
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ? array_merge([$v], $t) : [$v, $t];
            }
        }
        // Đảm bảo mỗi phần tử trong $result là một mảng các ID
        return array_map(function($item) {
            return is_array($item) ? $item : [$item];
        }, $result);
    }
}
