<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\UploadedFile;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::whereNotNull('parent_id')->get();
        if ($categories->isEmpty()) {
            $this->command->error('No sub-categories found to assign products. Please seed categories first.');
            return;
        }

        $allAttributes = Attribute::with('attributeValues')->get();
        if ($allAttributes->isEmpty()) {
            $this->command->warn('No attributes found. Variable products might not have variants with attributes.');
        }

        Product::factory(30)->create()->each(function ($product) use ($allAttributes) {
            // Tạo ảnh bìa và gallery (giữ nguyên)
            UploadedFile::factory()->attachedTo($product, 'cover_image')->create();
            UploadedFile::factory(rand(2, 5))->attachedTo($product, 'gallery_image')->create();

            if ($product->type === 'simple') {
                ProductVariant::factory()->create([
                    'product_id' => $product->id,
                    'is_default' => true,
                    // BỎ DÒNG stock_quantity. Factory sẽ tự xử lý.
                ]);
            } elseif ($product->type === 'variable' && $allAttributes->isNotEmpty()) {
                $numberOfVariantsToCreate = rand(2, 5);
                $createdVariantsCount = 0;

                // Logic tạo tổ hợp thuộc tính (giữ nguyên)
                $attributesForProduct = $allAttributes->count() > 1 ? $allAttributes->random(min($allAttributes->count(), rand(1, 3))) : $allAttributes;
                if ($attributesForProduct->isEmpty()) return;

                $possibleAttributeValues = [];
                foreach ($attributesForProduct as $attribute) {
                    if ($attribute->attributeValues->isNotEmpty()) {
                        $possibleAttributeValues[] = $attribute->attributeValues->random(min($attribute->attributeValues->count(), rand(1,3)))->pluck('id')->toArray();
                    }
                }
                if (empty($possibleAttributeValues)) return;

                $combinations = $this->generateCombinations($possibleAttributeValues);

                foreach ($combinations as $combination) {
                    if ($createdVariantsCount >= $numberOfVariantsToCreate) break;

                    $variant = ProductVariant::factory()->create([
                        'product_id' => $product->id,
                        'is_default' => ($createdVariantsCount === 0),
                        // BỎ DÒNG stock_quantity. Factory sẽ tự xử lý.
                    ]);
                    $variant->attributeValues()->attach($combination);
                    $createdVariantsCount++;
                }
                
                // Nếu không tạo được biến thể nào, tạo 1 biến thể mặc định
                if ($createdVariantsCount === 0) {
                     ProductVariant::factory()->create([
                         'product_id' => $product->id,
                         'is_default' => true,
                         // BỎ DÒNG stock_quantity. Factory sẽ tự xử lý.
                     ]);
                }
            }
        });

        $this->command->info('Products and Variants seeded successfully!');
    }

    // Hàm generateCombinations (giữ nguyên)
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
        return array_map(function($item) {
            return is_array($item) ? $item : [$item];
        }, $result);
    }
}