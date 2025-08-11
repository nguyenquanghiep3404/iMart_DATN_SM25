<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFulfillmentItem extends Model
{
    protected $fillable = ['order_fulfillment_id', 'order_item_id', 'quantity'];

public function fulfillment()
{
    return $this->belongsTo(OrderFulfillment::class, 'order_fulfillment_id');
}

public function orderItem()
{
    return $this->belongsTo(OrderItem::class);
}

}
