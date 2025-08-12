<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFulfillment extends Model
{
    protected $fillable = [
    'order_id', 'store_location_id', 'tracking_code',
    'shipping_carrier', 'status', 'shipped_at', 'delivered_at'
];

public function order()
{
    return $this->belongsTo(Order::class);
}

public function items()
{
    return $this->hasMany(OrderFulfillmentItem::class);
}

public function storeLocation()
{
    return $this->belongsTo(StoreLocation::class, 'store_location_id');
}

}
