<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class NewAddressSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('provinces_new')->truncate();
        DB::table('wards_new')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $provincesData = json_decode(File::get("database/locations/province_new.json"), true);
        foreach ($provincesData as $code => $province) {
            DB::table('provinces_new')->insert([
                'code' => $province['code'],
                'name' => $province['name'],
                'slug' => $province['slug'],
                'type' => $province['type'],
                'name_with_type' => $province['name_with_type'],
            ]);
        }

        $wardsData = json_decode(File::get("database/locations/ward_new.json"), true);
        foreach ($wardsData as $code => $ward) {
            DB::table('wards_new')->insert([
                'code' => $ward['code'],
                'name' => $ward['name'],
                'slug' => $ward['slug'],
                'type' => $ward['type'],
                'name_with_type' => $ward['name_with_type'],
                'path' => $ward['path'],
                'path_with_type' => $ward['path_with_type'],
                'district_code' => null, // Địa chỉ mới không có quận/huyện
                'province_code' => $ward['parent_code'],
            ]);
        }
    }
}
