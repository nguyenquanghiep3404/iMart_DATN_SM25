<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlashSaleProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'flash_sale_id',
        'flash_sale_time_slot_id',
        'product_variant_id',
        'flash_price',
        'quantity_limit',
        'quantity_sold',
        'status',
    ];

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(FlashSaleTimeSlot::class, 'flash_sale_time_slot_id');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function variant()
{
    return $this->belongsTo(ProductVariant::class, 'product_variant_id');
}


}
