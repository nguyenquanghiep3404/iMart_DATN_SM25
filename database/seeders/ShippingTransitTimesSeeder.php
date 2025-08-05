<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingTransitTimesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // 1. Định nghĩa tên nhà vận chuyển nội bộ
        $carrierName = 'store_shipper';

        // 2. Định nghĩa quy tắc thời gian vận chuyển (ĐÃ KẾT HỢP)
        $rules = [
            'intra_province'  => ['min' => 1, 'max' => 1], // Giao trong ngày hoặc ngày hôm sau
            'intra_region'    => ['min' => 2, 'max' => 2], // Nội vùng (Bắc -> Bắc, Trung -> Trung, Nam -> Nam)
            'adjacent_region' => ['min' => 2, 'max' => 3], // Cận vùng (Bắc <-> Trung, Trung <-> Nam)
            'cross_region'    => ['min' => 3, 'max' => 5], // Liên vùng (Bắc <-> Nam)
        ];

        // 3. Định nghĩa giá trị số cho các vùng để tính toán khoảng cách
        $regionValues = ['north' => 1, 'central' => 2, 'south' => 3];
        
        // 4. Lấy tất cả các tỉnh đã được phân loại vùng miền
        $provinces = DB::table('provinces_old')->whereNotNull('region')->get();

        if ($provinces->isEmpty()) {
            $this->command->error('LỖI: Không tìm thấy tỉnh nào có dữ liệu vùng miền trong bảng `provinces_old`.');
            $this->command->error('Vui lòng chạy seeder phân loại vùng miền trước khi chạy seeder này.');
            return;
        }

        $transitTimesData = [];
        $now = now();

        // 5. Vòng lặp để tạo ra tất cả các cặp tuyến đường
        foreach ($provinces as $fromProvince) {
            foreach ($provinces as $toProvince) {
                
                $ruleKey = '';

                // Logic kết hợp: Kiểm tra trường hợp nội tỉnh trước
                if ($fromProvince->code === $toProvince->code) {
                    $ruleKey = 'intra_province';
                } else {
                    // Nếu không phải nội tỉnh, áp dụng logic vùng miền
                    $fromRegionVal = $regionValues[$fromProvince->region];
                    $toRegionVal = $regionValues[$toProvince->region];
                    
                    if ($fromRegionVal == $toRegionVal) {
                        $ruleKey = 'intra_region';
                    } elseif (abs($fromRegionVal - $toRegionVal) == 1) {
                        $ruleKey = 'adjacent_region';
                    } else {
                        $ruleKey = 'cross_region';
                    }
                }

                // Thêm dữ liệu tuyến đường vào mảng
                $transitTimesData[] = [
                    'carrier_name'       => $carrierName,
                    'from_province_code' => $fromProvince->code,
                    'to_province_code'   => $toProvince->code,
                    'transit_days_min'   => $rules[$ruleKey]['min'],
                    'transit_days_max'   => $rules[$ruleKey]['max'],
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }
        }

        // 6. Xóa dữ liệu cũ của nhà vận chuyển này và chèn dữ liệu mới
        $this->command->info("Đang xóa dữ liệu cũ cho nhà vận chuyển '{$carrierName}'...");
        DB::table('shipping_transit_times')->where('carrier_name', $carrierName)->delete();

        $this->command->info('Đang chèn ' . count($transitTimesData) . ' tuyến đường vận chuyển mới...');
        
        // Chèn hàng loạt để tối ưu hiệu suất
        foreach (array_chunk($transitTimesData, 200) as $chunk) {
            DB::table('shipping_transit_times')->insert($chunk);
        }

        $this->command->info("Hoàn tất! Đã tạo thành công ma trận thời gian vận chuyển cho '{$carrierName}'.");
    }
}