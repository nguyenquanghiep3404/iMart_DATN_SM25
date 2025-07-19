<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'start_time',
        'end_time',
        'banner_image_url',
        'status',
    ];

    protected $dates = [
        'start_time',
        'end_time',
    ];

    public function products()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function flashSaleProducts()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function flashSaleTimeSlots()
{
    return $this->hasMany(FlashSaleTimeSlot::class);
}


    // Nếu bạn có ProductVariant model, có thể dùng hasManyThrough (tuỳ mục đích)
}
