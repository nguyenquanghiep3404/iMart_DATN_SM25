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
        'province_code', 
        'ward_code', 
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

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_code', 'code');
    }

    // Accessor để lấy địa chỉ đầy đủ
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->ward?->name,
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
            $this->province?->name_with_type,
        ]);
        
        return implode(', ', $parts);
    }
}