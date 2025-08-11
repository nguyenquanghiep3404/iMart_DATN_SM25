<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_label',
        'full_name',
        'phone_number',
        'address_line1',
        'address_line2',
        'address_system',
        'new_province_code',
        'new_ward_code',
        'old_province_code',
        'old_district_code',
        'old_ward_code',
        'is_default_shipping',
        'is_default_billing',
    ];

    protected $casts = [
        'is_default_shipping' => 'boolean',
        'is_default_billing' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Địa chỉ mới
    public function newProvince()
    {
        return $this->belongsTo(Province::class, 'new_province_code', 'code');
    }

    public function newWard()
    {
        return $this->belongsTo(Ward::class, 'new_ward_code', 'code');
    }

    // Hệ thống CŨ
    public function oldProvince()
    {
        return $this->belongsTo(ProvinceOld::class, 'old_province_code', 'code');
    }

    public function oldDistrict()
    {
        return $this->belongsTo(DistrictOld::class, 'old_district_code', 'code');
    }

    public function oldWard()
    {
        return $this->belongsTo(WardOld::class, 'old_ward_code', 'code');
    }

    // Alias cho tương thích với code hiện tại
    public function provinceOld()
    {
        return $this->oldProvince();
    }

    public function districtOld()
    {
        return $this->oldDistrict();
    }

    public function wardOld()
    {
        return $this->oldWard();
    }

    // Quan hệ động dựa trên hệ thống
    public function province()
    {
        if ($this->address_system === 'new') {
            return $this->newProvince();
        } else {
            return $this->oldProvince();
        }
    }

    public function ward()
    {
        if ($this->address_system === 'new') {
            return $this->newWard();
        } else {
            return $this->oldWard();
        }
    }

    public function district()
    {
        if ($this->address_system === 'old') {
            return $this->oldDistrict();
        }
        // Trả về relationship rỗng thay vì null
        return $this->belongsTo(DistrictOld::class, 'old_district_code', 'code');
    }

    // Accessor để lấy địa chỉ đầy đủ
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->ward?->name,
            $this->district?->name,
            $this->province?->name,
        ]);

        return implode(', ', $parts);
    }

    // Accessor để lấy địa chỉ đầy đủ với type
    public function getFullAddressWithTypeAttribute()
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->ward?->name_with_type,
            $this->district?->name_with_type,
            $this->province?->name_with_type,
        ]);

        return implode(', ', $parts);
    }

    // Scope để lọc theo hệ thống
    public function scopeNewSystem($query)
    {
        return $query->where('address_system', 'new');
    }

    public function scopeOldSystem($query)
    {
        return $query->where('address_system', 'old');
    }
}
