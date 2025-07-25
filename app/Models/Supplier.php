<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\WardOld;
use App\Models\DistrictOld;
use App\Models\ProvinceOld;

class Supplier extends Model
{
    use SoftDeletes; // nếu bạn đang dùng soft delete

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address_line',
        'ward_code',
        'district_code',
        'province_code'
    ];

    public function ward()
    {
        return $this->belongsTo(WardOld::class, 'ward_code', 'code');
    }

    public function district()
    {
        return $this->belongsTo(DistrictOld::class, 'district_code', 'code');
    }

    public function province()
    {
        return $this->belongsTo(ProvinceOld::class, 'province_code', 'code');
    }
}
