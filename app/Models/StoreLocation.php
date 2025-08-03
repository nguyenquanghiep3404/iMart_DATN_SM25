<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class StoreLocation extends Model
{
     use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name', 
        'type', 
        'address', 
        'phone', 
        'province_code', 
        'district_code', 
        'ward_code', 
        'latitude',
        'longitude',
        'is_active',
    ];
    /**
     * Mối quan hệ với Tỉnh/Thành (bảng cũ).
     */
    public function province()
    {
        // Liên kết cột 'province_code' của bảng này với cột 'code' của bảng 'provinces_old'
        return $this->belongsTo(ProvinceOld::class, 'province_code', 'code');
    }

    /**
     * Mối quan hệ với Quận/Huyện (bảng cũ).
     */
    public function district()
    {
        return $this->belongsTo(DistrictOld::class, 'district_code', 'code');
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function () {
                $parts = [];
                // 1. Thêm địa chỉ chi tiết (số nhà, đường)
                if ($this->address) {
                    $parts[] = $this->address;
                }
                // 2. Thêm Phường/Xã (nếu có và đã được load)
                if ($this->relationLoaded('ward') && $this->ward) {
                    $parts[] = $this->ward->name_with_type;
                }
                // 3. Thêm Quận/Huyện (nếu có và đã được load)
                if ($this->relationLoaded('district') && $this->district) {
                    $parts[] = $this->district->name_with_type;
                }
                // 4. Thêm Tỉnh/Thành phố (nếu có và đã được load)
                if ($this->relationLoaded('province') && $this->province) {
                    $parts[] = $this->province->name_with_type;
                }

                return implode(', ', $parts);
            }
        );
    }
    // Mối quan hệ để lấy thông tin Phường/Xã từ mã ward_code
    public function ward()
    {
        // Liên kết ward_code của store_locations với code của wards_old
        return $this->belongsTo(WardOld::class, 'ward_code', 'code');
    }

    // --- Quan Hệ Quản Lý Nhân Viên Bán Hàng ---

    /**
     * Lấy tất cả người dùng được gán vào cửa hàng này.
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_store_location', 'store_location_id', 'user_id');
    }

    /**
     * Lấy tất cả lịch làm việc của nhân viên cho cửa hàng này.
     */
    public function employeeSchedules()
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    /**
     * Lấy tất cả máy POS cho cửa hàng này.
     */
    public function registers()
    {
        return $this->hasMany(Register::class);
    }

    /**
     * Lấy tất cả đơn hàng cho cửa hàng này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Lấy tất cả kiểm kê cho cửa hàng này.
     */
    public function stocktakes()
    {
        return $this->hasMany(Stocktake::class);
    }

    /**
     * Lấy tất cả chuyển kho từ cửa hàng này.
     */
    public function stockTransfersFrom()
    {
        return $this->hasMany(StockTransfer::class, 'from_location_id');
    }

    /**
     * Lấy tất cả chuyển kho đến cửa hàng này.
     */
    public function stockTransfersTo()
    {
        return $this->hasMany(StockTransfer::class, 'to_location_id');
    }

    /**
     * Lấy tất cả phiếu nhập cho cửa hàng này.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Lấy tất cả sản phẩm thu đổi cho cửa hàng này.
     */
    public function tradeInItems()
    {
        return $this->hasMany(TradeInItem::class);
    }

    /**
     * Lấy tất cả tồn kho sản phẩm cho cửa hàng này.
     */
    public function productInventories()
    {
        return $this->hasMany(ProductInventory::class);
    }

    /**
     * Lấy lịch làm việc của nhân viên cho một khoảng thời gian cụ thể.
     */
    public function layLichLamViecTheoKhoangThoiGian($startDate, $endDate)
    {
        return $this->employeeSchedules()
                    ->with(['user', 'workShift'])
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date')
                    ->orderBy('user_id')
                    ->get();
    }

    /**
     * Lấy lịch làm việc của nhân viên cho một tuần cụ thể.
     */
    public function layLichLamViecTheoTuan($weekStartDate)
    {
        $weekEndDate = \Carbon\Carbon::parse($weekStartDate)->addDays(6);
        return $this->layLichLamViecTheoKhoangThoiGian($weekStartDate, $weekEndDate);
    }

    /**
     * Lấy số lượng nhân viên được gán.
     */
    public function getSoLuongNhanVienAttribute()
    {
        return $this->assignedUsers()->count();
    }

    /**
     * Kiểm tra xem cửa hàng có nhân viên được gán không.
     */
    public function coNhanVien()
    {
        return $this->assignedUsers()->exists();
    }

    /**
     * Lấy nhân viên đang hoạt động (người dùng có trạng thái active).
     */
    public function layNhanVienDangHoatDong()
    {
        return $this->assignedUsers()->where('status', 'active')->get();
    }
}
