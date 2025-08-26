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
            BannerSeeder::class,
            PostSeeder::class,          // Sẽ seed PostCategory, PostTag, Post
            CouponSeeder::class,
            CommentSeeder::class,       // Tạo comment sau khi có Post/Review
            WishlistSeeder::class,
            LocationSeeder::class,      // Tạo tỉnh thành và phường xã trước address
            AddressSeeder::class,
            SystemSettingSeeder::class,
            ContactFormSeeder::class,
            SpecificationSeeder::class,
             NewAddressSeeder::class,    // Seed dữ liệu cho hệ thống địa chỉ MỚI
            OldAddressSeeder::class,  
            ProvincesOldRegionSeeder::class, // Seed dữ liệu cho vùng miền cũ
             ShippingTransitTimesSeeder::class, // Seed dữ liệu thời gian vận chuyển  // Seed dữ liệu cho hệ thống địa chỉ CŨ
            // AbandonedCartSeeder::class,
            // CartSeeder::class,
            // OrderSeeder::class,
            // ReviewSeeder::class,        // Tạo đánh giá sau khi có sản phẩm và đơn hàng
            ProFeaturesSeeder::class,
            // ActivityLogSeeder::class, // Nếu có 
            // Thêm các seeder cho hệ thống địa chỉ kép
           
            CustomerGroupSeeder::class,
            MarketingCampaignSeeder::class,
            
           
        ]);
        $this->command->info('All seeders ran successfully!');
    }
}

