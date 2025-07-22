<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use DB;


class SyncGhnAddresses extends Command
{
    protected $signature = 'ghn:sync-address';
    protected $description = 'Đồng bộ dữ liệu địa chỉ từ GHN về database';

    public function handle()
    {
        $token = config('services.ghn.token');
        $headers = [
            'Token' => $token,
            'Content-Type' => 'application/json',
        ];

        // 1. Lấy danh sách tỉnh/thành
        $this->info('Đang lấy danh sách tỉnh/thành...');
        $provinces = Http::withHeaders($headers)
            ->get('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/province')
            ->json('data');
        DB::table('ghn_provinces')->truncate();
        foreach ($provinces as $province) {
            DB::table('ghn_provinces')->insert([
                'id' => $province['ProvinceID'],
                'name' => $province['ProvinceName'],
                'code' => $province['Code'] ?? null,
                'name_with_type' => $province['NameExtension'][0] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->info('Đã đồng bộ tỉnh/thành.');

        // 2. Lấy danh sách quận/huyện cho từng tỉnh
        DB::table('ghn_districts')->truncate();
        foreach ($provinces as $province) {
            $this->info('Đang lấy quận/huyện cho tỉnh: ' . $province['ProvinceName']);
            $districts = Http::withHeaders($headers)
                ->post('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/district', [
                    'province_id' => $province['ProvinceID']
                ])->json('data');
            if (!$districts) continue;
            foreach ($districts as $district) {
                DB::table('ghn_districts')->insert([
                    'id' => $district['DistrictID'],
                    'name' => $district['DistrictName'],
                    'code' => $district['Code'] ?? null,
                    'name_with_type' => $district['NameExtension'][0] ?? null,
                    'province_id' => $province['ProvinceID'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->info('Đã đồng bộ quận/huyện.');

        // 3. Lấy danh sách phường/xã cho từng quận/huyện
        $districts = DB::table('ghn_districts')->get();
        DB::table('ghn_wards')->truncate();
        foreach ($districts as $district) {
            $this->info('Đang lấy phường/xã cho quận/huyện: ' . $district->name);
            $wards = Http::withHeaders($headers)
                ->post('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/ward', [
                    'district_id' => $district->id
                ])->json('data');
            if (!$wards) continue;
            foreach ($wards as $ward) {
                DB::table('ghn_wards')->insert([
                    'code' => $ward['WardCode'],
                    'name' => $ward['WardName'],
                    'name_with_type' => $ward['NameExtension'][0] ?? null,
                    'district_id' => $district->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->info('Đã đồng bộ phường/xã.');
        $this->info('Đồng bộ địa chỉ GHN hoàn tất!');
    }
} 