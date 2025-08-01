<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkShift;

class WorkShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workShifts = [
            [
                'name' => 'Ca Sáng',
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'color_code' => '#4299E1',
            ],
            [
                'name' => 'Ca Chiều',
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
                'color_code' => '#F6AD55',
            ],
            [
                'name' => 'Ca Tối',
                'start_time' => '18:00:00',
                'end_time' => '22:00:00',
                'color_code' => '#9F7AEA',
            ],
            [
                'name' => 'Ca Đêm',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'color_code' => '#2D3748',
            ],
        ];

        foreach ($workShifts as $shift) {
            WorkShift::create($shift);
        }
    }
} 