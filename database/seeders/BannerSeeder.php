<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banner;
use App\Models\UploadedFile;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        // Banner::truncate(); // Cẩn thận
        // UploadedFile::where('attachable_type', Banner::class)->delete();

        Banner::factory(5)->create()->each(function ($banner) {
            UploadedFile::factory()->attachedTo($banner, 'banner_desktop')->create();
            if(rand(0,1)) { // 50% chance of having a mobile banner
                 UploadedFile::factory()->attachedTo($banner, 'banner_mobile')->create();
            }
        });
        $this->command->info('Banners seeded successfully!');
    }
}