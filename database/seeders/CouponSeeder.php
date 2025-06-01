<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Coupon::truncate(); // Cẩn thận
        Coupon::factory(15)->create();
        $this->command->info('Coupons seeded successfully!');
    }
}
