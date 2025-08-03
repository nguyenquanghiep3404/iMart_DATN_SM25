<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvinceOld extends Model
{
    protected $table = 'provinces_old';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'slug',
        'type',
        'name_with_type'
    ];

    public function districts()
    {
        return $this->hasMany(DistrictOld::class, 'parent_code', 'code');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'old_province_code', 'code');
    }

    public function shippingOrders()
    {
        return $this->hasMany(Order::class, 'shipping_old_province_code', 'code');
    }

    public function billingOrders()
    {
        return $this->hasMany(Order::class, 'billing_old_province_code', 'code');
    }

     // --- THÊM MỐI QUAN HỆ storeLocations VÀ districts VÀO ĐÂY ---

    /**
     * Lấy các địa điểm cửa hàng thuộc về tỉnh/thành phố này.
     */
    public function storeLocations()
    {
        // Một tỉnh/thành phố có nhiều cửa hàng
        // 'province_code' là khóa ngoại trong bảng 'store_locations'
        // 'code' là khóa chính của bảng 'provinces_new'
        return $this->hasMany(StoreLocation::class, 'province_code', 'code');
    }

    /**
     * Lấy các quận/huyện thuộc về tỉnh/thành phố này.
     */

    // Accessor để kiểm tra loại
    public function getIsCity()
    {
        return $this->type === 'thanh-pho';
    }

    public function getIsProvince()
    {
        return $this->type === 'tinh';
    }
}
