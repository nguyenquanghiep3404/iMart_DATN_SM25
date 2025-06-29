<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,          // Tạo user trước khi tạo các đối tượng khác có created_by
            CategorySeeder::class,      // Tạo danh mục trước sản phẩm
            AttributeSeeder::class,     // Tạo thuộc tính và giá trị thuộc tính trước sản phẩm có biến thể
            ProductSeeder::class,       // Tạo sản phẩm và biến thể
            CartSeeder::class,
            OrderSeeder::class,         // Tạo đơn hàng và chi tiết đơn hàng
            ReviewSeeder::class,        // Tạo đánh giá sau khi có sản phẩm và đơn hàng
            BannerSeeder::class,
            PostSeeder::class,          // Sẽ seed PostCategory, PostTag, Post
            CouponSeeder::class,
            CommentSeeder::class,       // Tạo comment sau khi có Post/Review
            WishlistSeeder::class,
            AddressSeeder::class,
            SystemSettingSeeder::class,
            ContactFormSeeder::class,
            // ActivityLogSeeder::class, // Nếu có
        ]);
        $this->command->info('All seeders ran successfully!');
    }
}
