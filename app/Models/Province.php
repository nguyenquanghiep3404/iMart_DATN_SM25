<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces_new';
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

    public function wards()
    {
        return $this->hasMany(Ward::class, 'province_code', 'code');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'province_code', 'code');
    }

    public function shippingOrders()
    {
        return $this->hasMany(Order::class, 'shipping_province_code', 'code');
    }

    public function billingOrders()
    {
        return $this->hasMany(Order::class, 'billing_province_code', 'code');
    }

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
