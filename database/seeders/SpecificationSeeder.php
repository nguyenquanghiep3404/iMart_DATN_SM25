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
            'Màn hình' => [
                ['name' => 'Công nghệ màn hình', 'type' => 'text'],
                ['name' => 'Độ phân giải', 'type' => 'text'],
                ['name' => 'Độ sáng tối đa', 'type' => 'text'],
                ['name' => 'Tính năng đặc biệt', 'type' => 'textarea'],
            ],
            'Hệ điều hành & CPU' => [
                ['name' => 'Hệ điều hành', 'type' => 'text'],
                ['name' => 'Chip xử lý', 'type' => 'text'],
            ],
            'Bộ nhớ & Lưu trữ' => [
                ['name' => 'RAM', 'type' => 'text'],
                ['name' => 'Dung lượng lưu trữ', 'type' => 'text'],
            ],
            'Camera' => [
                ['name' => 'Độ phân giải', 'type' => 'text'],
                ['name' => 'Quay phim', 'type' => 'textarea'],
                ['name' => 'Tính năng', 'type' => 'textarea'],
            ],
            'Pin & Sạc' => [
                ['name' => 'Dung lượng pin', 'type' => 'text'],
                ['name' => 'Sạc nhanh', 'type' => 'text'],
                ['name' => 'Sạc không dây', 'type' => 'text'],
            ],
            'Kết nối' => [
                ['name' => 'Mạng di động', 'type' => 'text'],
                ['name' => 'SIM', 'type' => 'text'],
                ['name' => 'Cổng sạc', 'type' => 'text'],
                ['name' => 'Kết nối không dây', 'type' => 'text'],
            ],
            'Thiết kế & Vật liệu' => [
                ['name' => 'Chất liệu', 'type' => 'text'],
                ['name' => 'Kích thước & Trọng lượng', 'type' => 'text'],
                ['name' => 'Kháng nước & bụi', 'type' => 'text'],
            ],
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
