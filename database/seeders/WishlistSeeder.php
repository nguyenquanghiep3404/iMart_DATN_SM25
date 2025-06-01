<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\User;
use App\Models\ProductVariant;
class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        // DB::table('wishlist_items')->truncate(); // Cân nhắc nếu muốn xóa dữ liệu cũ mỗi lần seed
        // DB::table('wishlists')->truncate();      // Cân nhắc nếu muốn xóa dữ liệu cũ mỗi lần seed

        $customers = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->get();
        $activeVariants = ProductVariant::where('status', 'active')->get();

        if ($customers->isEmpty() || $activeVariants->isEmpty()) {
            $this->command->warn('No customers or active variants to create wishlists. Skipping WishlistSeeder.');
            return;
        }

        foreach ($customers as $customer) {
            if (rand(0, 1)) { // 50% chance customer has a wishlist
                $wishlist = Wishlist::firstOrCreate(['user_id' => $customer->id]);
                
                $numberOfItems = rand(1, min(5, $activeVariants->count())); // Không thêm nhiều hơn số variant có sẵn
                $variantsForThisWishlist = $activeVariants->random($numberOfItems)->unique('id'); // Lấy ngẫu nhiên và đảm bảo không trùng trong một lần

                foreach ($variantsForThisWishlist as $variant) {
                    WishlistItem::firstOrCreate(
                        [ // Điều kiện để tìm
                            'wishlist_id' => $wishlist->id,
                            'product_variant_id' => $variant->id,
                        ],
                        [ // Dữ liệu để tạo nếu không tìm thấy
                            'added_at' => now()->subDays(rand(0, 30)), // Ngày thêm ngẫu nhiên trong 30 ngày qua
                        ]
                    );
                }
            }
        }
        $this->command->info('Wishlists and WishlistItems seeded successfully!');
    }
}
