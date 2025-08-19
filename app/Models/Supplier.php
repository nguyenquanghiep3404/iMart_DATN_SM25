<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\WardOld;
use App\Models\DistrictOld;
use App\Models\ProvinceOld;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email', // Thêm email nếu cần
        'phone',
        'address_line',
        'ward_code',
        'district_code',
        'province_code',
        // Thêm các trường khác nếu cần
    ];

    /**
     * Mối quan hệ để lấy thông tin Phường/Xã.
     */
    public function ward()
    {
        return $this->belongsTo(WardOld::class, 'ward_code', 'code');
    }

    /**
     * Mối quan hệ để lấy thông tin Quận/Huyện.
     */
    public function district()
    {
        return $this->belongsTo(DistrictOld::class, 'district_code', 'code');
    }

    /**
     * Mối quan hệ để lấy thông tin Tỉnh/Thành.
     */
    public function province()
    {
        return $this->belongsTo(ProvinceOld::class, 'province_code', 'code');
    }

    /**
     * Mối quan hệ với các phiếu nhập kho.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Accessor để lấy địa chỉ đầy đủ của nhà cung cấp.
     * Cách dùng: $supplier->full_address
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function () {
                $parts = [];

                // 1. Thêm địa chỉ chi tiết (số nhà, tên đường)
                if ($this->address_line) { // Sửa từ 'address' thành 'address_line' cho đúng với $fillable
                    $parts[] = $this->address_line;
                }

                // 2. Thêm Phường/Xã (nếu có và đã được eager-load)
                if ($this->relationLoaded('ward') && $this->ward) {
                    $parts[] = $this->ward->name_with_type;
                }

                // 3. Thêm Quận/Huyện (nếu có và đã được eager-load)
                if ($this->relationLoaded('district') && $this->district) {
                    $parts[] = $this->district->name_with_type;
                }

                // 4. Thêm Tỉnh/Thành (nếu có và đã được eager-load)
                if ($this->relationLoaded('province') && $this->province) {
                    $parts[] = $this->province->name_with_type;
                }

                // Nối các phần lại với nhau bằng dấu phẩy
                return implode(', ', $parts);
            }
        );
    }
}