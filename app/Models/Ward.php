<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'slug',
        'type',
        'name_with_type',
        'path',
        'path_with_type',
        'district_code',
        'province_code'
    ];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'ward_code', 'code');
    }

    public function shippingOrders()
    {
        return $this->hasMany(Order::class, 'shipping_ward_code', 'code');
    }

    public function billingOrders()
    {
        return $this->hasMany(Order::class, 'billing_ward_code', 'code');
    }

    // Accessor để kiểm tra loại
    public function getIsCommune()
    {
        return $this->type === 'xa';
    }

    public function getIsWard()
    {
        return $this->type === 'phuong';
    }

    public function getIsTown()
    {
        return $this->type === 'thi-tran';
    }
}
