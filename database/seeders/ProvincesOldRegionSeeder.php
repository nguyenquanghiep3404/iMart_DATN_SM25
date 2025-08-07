<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvincesOldRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách mã tỉnh theo vùng Miền Bắc
        $north_codes = ['01', '02', '04', '06', '08', '10', '11', '12', '14', '15', '17', '19', '20', '22', '24', '25', '26', '27', '30', '31', '33', '34', '35', '36', '37'];

        // Danh sách mã tỉnh theo vùng Miền Trung
        $central_codes = ['38', '40', '42', '44', '45', '46', '48', '49', '51', '52', '54', '56', '58', '60', '62', '64', '66', '67'];

        // Danh sách mã tỉnh theo vùng Miền Nam
        $south_codes = ['70', '72', '74', '75', '77', '79', '80', '82', '83', '84', '86', '87', '89', '91', '92', '93', '94', '95', '96', '68'];

        // Cập nhật cho Miền Bắc
        DB::table('provinces_old')->whereIn('code', $north_codes)->update(['region' => 'north']);

        // Cập nhật cho Miền Trung
        DB::table('provinces_old')->whereIn('code', $central_codes)->update(['region' => 'central']);

        // Cập nhật cho Miền Nam
        DB::table('provinces_old')->whereIn('code', $south_codes)->update(['region' => 'south']);

        $this->command->info('Provinces_old table has been updated with regions successfully!');
    }
}
