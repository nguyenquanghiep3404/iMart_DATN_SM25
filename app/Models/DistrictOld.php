<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistrictOld extends Model
{
    protected $table = 'districts_old';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'type',
        'name_with_type',
        'path_with_type',
        'parent_code'
    ];

    public function province()
    {
        return $this->belongsTo(ProvinceOld::class, 'parent_code', 'code');
    }

    public function wards()
    {
        return $this->hasMany(WardOld::class, 'parent_code', 'code');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'old_district_code', 'code');
    }

    public function shippingOrders()
    {
        return $this->hasMany(Order::class, 'shipping_old_district_code', 'code');
    }

    public function billingOrders()
    {
        return $this->hasMany(Order::class, 'billing_old_district_code', 'code');
    }

    // Accessor để kiểm tra loại
    public function getIsCity()
    {
        return $this->type === 'thanh-pho';
    }

    public function getIsDistrict()
    {
        return $this->type === 'huyen';
    }

    public function getIsTown()
    {
        return $this->type === 'thi-xa';
    }
}
