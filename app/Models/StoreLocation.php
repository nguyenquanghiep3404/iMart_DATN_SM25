<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class StoreLocation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 
        'type', 
        'address', 
        'phone', 
        'province_code', 
        'district_code', 
        'ward_code', 
        'is_active'
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

    /**
     * Mối quan hệ với Phường/Xã (bảng cũ).
     */
    public function ward()
    {
        return $this->belongsTo(WardOld::class, 'ward_code', 'code');
    }

    public function registers() {
        return $this->hasMany(Register::class);
    }

    public function productInventories() {
        return $this->hasMany(ProductInventory::class);
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
}
