<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSaleTimeSlot extends Model
{
    protected $fillable = [
        'flash_sale_id',
        'start_time',
        'end_time',
        'label',
        'total_quantity_limit',
        'sort_order',
        'status',
    ];

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function products()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }
}
