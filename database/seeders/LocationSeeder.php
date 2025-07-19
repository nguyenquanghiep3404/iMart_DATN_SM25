<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear all data first (child tables before parent tables)
        $this->command->info('Clearing existing location data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Ward::truncate();
        Province::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->seedProvinces();
        $this->seedWards();
    }

    /**
     * Seed provinces from JSON file
     */
    private function seedProvinces(): void
    {
        $this->command->info('Seeding provinces...');
        
        $provincesPath = database_path('locations/province_new.json');
        
        if (!File::exists($provincesPath)) {
            $this->command->error('Province JSON file not found!');
            return;
        }

        $provincesData = json_decode(File::get($provincesPath), true);
        
        if (!$provincesData) {
            $this->command->error('Invalid province JSON data!');
            return;
        }

        // Data already cleared in run() method

        $provinces = [];
        foreach ($provincesData as $code => $province) {
            $provinces[] = [
                'code' => $province['code'],
                'name' => $province['name'],
                'slug' => $province['slug'],
                'type' => $province['type'],
                'name_with_type' => $province['name_with_type'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert provinces
        Province::insert($provinces);
        
        $this->command->info('Provinces seeded successfully! Total: ' . count($provinces));
    }

    /**
     * Seed wards from JSON file
     */
    private function seedWards(): void
    {
        $this->command->info('Seeding wards...');
        
        $wardsPath = database_path('locations/ward_new.json');
        
        if (!File::exists($wardsPath)) {
            $this->command->error('Ward JSON file not found!');
            return;
        }

        $wardsData = json_decode(File::get($wardsPath), true);
        
        if (!$wardsData) {
            $this->command->error('Invalid ward JSON data!');
            return;
        }

        // Data already cleared in run() method

        $wards = [];
        $batchSize = 1000; // Process in batches of 1000 records

        foreach ($wardsData as $code => $ward) {
            $wards[] = [
                'code' => $ward['code'],
                'name' => $ward['name'],
                'slug' => $ward['slug'],
                'type' => $ward['type'],
                'name_with_type' => $ward['name_with_type'],
                'path' => $ward['path'] ?? null,
                'path_with_type' => $ward['path_with_type'] ?? null,
                'district_code' => null, // Để null vì data hiện tại không có district table
                'province_code' => $ward['parent_code'], // Map parent_code thành province_code
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches
            if (count($wards) >= $batchSize) {
                Ward::insert($wards);
                $wards = [];
                $this->command->info('Processed batch of ' . $batchSize . ' wards...');
            }
        }

        // Insert remaining wards
        if (!empty($wards)) {
            Ward::insert($wards);
        }

        $wardsCount = Ward::count();
        $this->command->info('Wards seeded successfully! Total: ' . $wardsCount);
    }
}
