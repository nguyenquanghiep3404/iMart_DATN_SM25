<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class EmployeeSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_location_id',
        'work_shift_id',
        'date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Lấy thông tin người dùng (nhân viên) cho lịch làm việc này.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy thông tin cửa hàng cho lịch làm việc này.
     */
    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class);
    }

    /**
     * Lấy thông tin ca làm việc cho lịch làm việc này.
     */
    public function workShift()
    {
        return $this->belongsTo(WorkShift::class);
    }

    /**
     * Lấy thông tin người dùng đã tạo lịch làm việc này.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Lấy thông tin người dùng đã cập nhật lịch làm việc này lần cuối.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope để lấy lịch làm việc cho một người dùng cụ thể.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope để lấy lịch làm việc cho một cửa hàng cụ thể.
     */
    public function scopeForStore($query, $storeLocationId)
    {
        return $query->where('store_location_id', $storeLocationId);
    }

    /**
     * Scope để lấy lịch làm việc cho một khoảng thời gian cụ thể.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope để lấy lịch làm việc cho một tuần cụ thể.
     */
    public function scopeForWeek($query, $weekStartDate)
    {
        $weekEndDate = Carbon::parse($weekStartDate)->addDays(6);
        return $query->forDateRange($weekStartDate, $weekEndDate);
    }

    /**
     * Scope để lấy lịch làm việc cho hôm nay.
     */
    public function scopeForToday($query)
    {
        return $query->where('date', Carbon::today());
    }

    /**
     * Lấy lịch làm việc của nhân viên cho một ngày cụ thể.
     */
    public static function layLichLamViecTheoNgay($userId, $date)
    {
        return static::with(['workShift', 'storeLocation'])
                    ->where('user_id', $userId)
                    ->where('date', $date)
                    ->first();
    }

    /**
     * Lấy tất cả lịch làm việc của một người dùng trong khoảng thời gian.
     */
    public static function layLichLamViecCuaNhanVien($userId, $startDate, $endDate)
    {
        return static::with(['workShift', 'storeLocation'])
                    ->where('user_id', $userId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date')
                    ->get();
    }

    /**
     * Lấy tất cả lịch làm việc của một cửa hàng trong khoảng thời gian.
     */
    public static function layLichLamViecCuaCuaHang($storeLocationId, $startDate, $endDate)
    {
        return static::with(['user', 'workShift'])
                    ->where('store_location_id', $storeLocationId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date')
                    ->orderBy('user_id')
                    ->get();
    }

    /**
     * Lấy lịch làm việc theo tuần cho một cửa hàng (dùng cho giao diện HTML).
     */
    public static function layLichLamViecTuanCuaCuaHang($storeLocationId, $weekStartDate)
    {
        $weekEndDate = Carbon::parse($weekStartDate)->addDays(6);
        return static::layLichLamViecCuaCuaHang($storeLocationId, $weekStartDate, $weekEndDate);
    }

    /**
     * Tạo hoặc cập nhật lịch làm việc cho một nhân viên.
     */
    public static function ganCa($userId, $storeLocationId, $workShiftId, $date, $createdBy = null)
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'date' => $date,
            ],
            [
                'store_location_id' => $storeLocationId,
                'work_shift_id' => $workShiftId,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]
        );
    }

    /**
     * Gán ca làm việc theo tên ca (dùng cho giao diện HTML).
     */
    public static function ganCaTheoTen($userId, $storeLocationId, $tenCa, $date, $createdBy = null)
    {
        $workShiftId = WorkShift::layIdTheoTen($tenCa);
        if (!$workShiftId) {
            throw new \Exception("Không tìm thấy ca làm việc: {$tenCa}");
        }
        
        return static::ganCa($userId, $storeLocationId, $workShiftId, $date, $createdBy);
    }

    /**
     * Xóa lịch làm việc của một nhân viên vào một ngày cụ thể.
     */
    public static function xoaCa($userId, $date)
    {
        return static::where('user_id', $userId)
                    ->where('date', $date)
                    ->delete();
    }

    /**
     * Kiểm tra xem một nhân viên có lịch làm việc vào một ngày cụ thể không.
     */
    public static function coLichLamViec($userId, $date)
    {
        return static::where('user_id', $userId)
                    ->where('date', $date)
                    ->exists();
    }

    /**
     * Lấy tóm tắt lịch làm việc của một người dùng trong một tuần.
     */
    public static function layTomTatLichLamViecTuan($userId, $weekStartDate)
    {
        $schedules = static::with('workShift')
                    ->forUser($userId)
                    ->forWeek($weekStartDate)
                    ->get();

        $tomTat = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::parse($weekStartDate)->addDays($i);
            $schedule = $schedules->where('date', $date->format('Y-m-d'))->first();
            
            $tomTat[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'has_schedule' => $schedule ? true : false,
                'shift_name' => $schedule ? $schedule->workShift->name : null,
                'shift_color' => $schedule ? $schedule->workShift->color_code : null,
            ];
        }

        return $tomTat;
    }

    /**
     * Lấy thuộc tính ngày đã định dạng.
     */
    public function getNgayDinhDangAttribute()
    {
        return $this->date->format('d/m/Y');
    }

    /**
     * Lấy thuộc tính tên ngày.
     */
    public function getTenNgayAttribute()
    {
        return $this->date->format('l');
    }
}
