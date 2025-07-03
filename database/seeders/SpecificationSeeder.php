<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpecificationGroup;
use App\Models\Specification;
use Illuminate\Support\Facades\DB;

class SpecificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại để tránh lỗi khi truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SpecificationGroup::truncate();
        Specification::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $specificationsData = [
            'Cấu hình & Bộ nhớ' => [
                ['name' => 'Hệ điều hành', 'type' => 'text'],
                ['name' => 'Chip xử lý (CPU)', 'type' => 'text'],
                ['name' => 'Tốc độ CPU', 'type' => 'text'],
                ['name' => 'Chip đồ họa (GPU)', 'type' => 'text'],
                ['name' => 'RAM', 'type' => 'text'],
                ['name' => 'Dung lượng lưu trữ', 'type' => 'text'],
                ['name' => 'Dung lượng còn lại (khả dụng) khoảng', 'type' => 'text'],
                ['name' => 'Danh bạ', 'type' => 'text'],
            ],
            'Camera & Màn hình' => [
                ['name' => 'Độ phân giải camera sau', 'type' => 'text'],
                ['name' => 'Quay phim camera sau', 'type' => 'textarea'],
                ['name' => 'Đèn Flash camera sau', 'type' => 'text'], // Có thể dùng 'boolean'
                ['name' => 'Tính năng camera sau', 'type' => 'textarea'],
                ['name' => 'Độ phân giải camera trước', 'type' => 'text'],
                ['name' => 'Tính năng camera trước', 'type' => 'textarea'],
                ['name' => 'Công nghệ màn hình', 'type' => 'text'],
                ['name' => 'Độ phân giải màn hình', 'type' => 'text'],
                ['name' => 'Màn hình rộng', 'type' => 'text'],
                ['name' => 'Độ sáng tối đa', 'type' => 'text'],
                ['name' => 'Mặt kính cảm ứng', 'type' => 'text'],
            ],
            'Pin & Sạc' => [
                ['name' => 'Dung lượng pin', 'type' => 'text'],
                ['name' => 'Loại pin', 'type' => 'text'],
                ['name' => 'Hỗ trợ sạc tối đa', 'type' => 'text'],
                ['name' => 'Công nghệ pin', 'type' => 'textarea'],
            ],
            'Tiện ích' => [
                ['name' => 'Bảo mật nâng cao', 'type' => 'text'],
                ['name' => 'Tính năng đặc biệt', 'type' => 'textarea'],
                ['name' => 'Kháng nước, bụi', 'type' => 'text'],
                ['name' => 'Ghi âm', 'type' => 'text'],
                ['name' => 'Xem phim', 'type' => 'text'],
                ['name' => 'Nghe nhạc', 'type' => 'text'],
            ],
            'Kết nối' => [
                ['name' => 'Mạng di động', 'type' => 'text'],
                ['name' => 'SIM', 'type' => 'text'],
                ['name' => 'Wifi', 'type' => 'text'],
                ['name' => 'GPS', 'type' => 'textarea'],
                ['name' => 'Bluetooth', 'type' => 'text'],
                ['name' => 'Cổng kết nối/sạc', 'type' => 'text'],
                ['name' => 'Jack tai nghe', 'type' => 'text'],
                ['name' => 'Kết nối khác', 'type' => 'text'],
            ],
            'Thiết kế & Chất liệu' => [
                ['name' => 'Thiết kế', 'type' => 'text'],
                ['name' => 'Chất liệu', 'type' => 'text'],
                ['name' => 'Kích thước, khối lượng', 'type' => 'text'],
                ['name' => 'Thời điểm ra mắt', 'type' => 'text'],
                ['name' => 'Hãng', 'type' => 'text'],
            ]
        ];

        foreach ($specificationsData as $groupName => $specs) {
            // Dùng updateOrCreate để có thể chạy lại seeder mà không tạo bản ghi trùng lặp
            $group = SpecificationGroup::updateOrCreate(['name' => $groupName]);

            foreach ($specs as $spec) {
                $group->specifications()->updateOrCreate(
                    [
                        'name' => $spec['name']
                    ],
                    [
                        'type' => $spec['type']
                    ]
                );
            }
        }
    }
}