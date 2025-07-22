<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WardOld extends Model
{
    protected $table = 'wards_old';
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

    public function district()
    {
        return $this->belongsTo(DistrictOld::class, 'parent_code', 'code');
    }

    public function province()
    {
        return $this->district->province();
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'old_ward_code', 'code');
    }

    public function shippingOrders()
    {
        return $this->hasMany(Order::class, 'shipping_old_ward_code', 'code');
    }

    public function billingOrders()
    {
        return $this->hasMany(Order::class, 'billing_old_ward_code', 'code');
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
