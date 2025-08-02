<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'color_code',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Lấy tất cả lịch làm việc của nhân viên cho ca làm việc này.
     */
    public function employeeSchedules()
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    /**
     * Scope để lấy các ca làm việc đang hoạt động.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Tìm ca làm việc theo tên.
     */
    public static function timTheoTen($name)
    {
        return static::where('name', $name)->first();
    }

    /**
     * Lấy ID của ca làm việc theo tên (dùng cho việc xếp lịch).
     */
    public static function layIdTheoTen($name)
    {
        $workShift = static::where('name', $name)->first();
        return $workShift ? $workShift->id : null;
    }

    /**
     * Lấy các ca làm việc mặc định (Ca Sáng, Ca Chiều, Ca Tối).
     */
    public static function layCaLamViecMacDinh()
    {
        return [
            [
                'name' => 'Ca Sáng',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'color_code' => '#4299E1',
            ],
            [
                'name' => 'Ca Chiều',
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
                'color_code' => '#F6AD55',
            ],
            [
                'name' => 'Ca Tối',
                'start_time' => '00:00:00',
                'end_time' => '08:00:00',
                'color_code' => '#9F7AEA',
            ],
        ];
    }

    /**
     * Tạo các ca làm việc mặc định nếu chúng chưa tồn tại.
     */
    public static function taoCaLamViecMacDinh()
    {
        $caLamViecMacDinh = self::layCaLamViecMacDinh();
        
        foreach ($caLamViecMacDinh as $duLieuCa) {
            static::firstOrCreate(
                ['name' => $duLieuCa['name']],
                $duLieuCa
            );
        }
    }

    /**
     * Lấy thời gian làm việc của ca tính bằng giờ.
     */
    public function getThoiGianLamViecTinhBangGioAttribute()
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        // Xử lý ca làm việc qua đêm
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        return $start->diffInHours($end);
    }

    /**
     * Kiểm tra xem một thời gian có nằm trong ca làm việc này không.
     */
    public function kiemTraThoiGianTrongCa($time)
    {
        $time = \Carbon\Carbon::parse($time);
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        // Xử lý ca làm việc qua đêm
        if ($end->lt($start)) {
            $end->addDay();
            if ($time->lt($start)) {
                $time->addDay();
            }
        }
        
        return $time->between($start, $end);
    }

    /**
     * Lấy khoảng thời gian đã định dạng.
     */
    public function getKhoangThoiGianAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
