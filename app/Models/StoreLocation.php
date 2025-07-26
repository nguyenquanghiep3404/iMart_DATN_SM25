<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'store_locations';

    protected $fillable = [
        'name',
        'type',
        'address',
        'province_code', // Cột để lưu mã tỉnh đã chọn
        'district_code', // Cột để lưu mã huyện đã chọn
        'ward_code',     // Cột để lưu mã xã đã chọn
        'phone',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Mối quan hệ để lấy thông tin Tỉnh/Thành phố từ mã province_code
    public function province()
    {
        // Liên kết province_code của store_locations với code của provinces_old
        return $this->belongsTo(ProvinceOld::class, 'province_code', 'code');
    }

    // Mối quan hệ để lấy thông tin Quận/Huyện từ mã district_code
    public function district()
    {
        // Liên kết district_code của store_locations với code của districts_old
        return $this->belongsTo(DistrictOld::class, 'district_code', 'code');
    }

    // Mối quan hệ để lấy thông tin Phường/Xã từ mã ward_code
    public function ward()
    {
        // Liên kết ward_code của store_locations với code của wards_old
        return $this->belongsTo(WardOld::class, 'ward_code', 'code');
    }
}
