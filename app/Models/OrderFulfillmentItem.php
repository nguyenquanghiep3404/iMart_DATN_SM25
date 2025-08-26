<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderFulfillmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_fulfillment_id', 
        'order_item_id', 
        'quantity'
        // REMOVED: package_id - now using order_fulfillments directly
    ];

    /**
     * Relationship với OrderFulfillment
     */
    public function fulfillment(): BelongsTo
    {
        return $this->belongsTo(OrderFulfillment::class, 'order_fulfillment_id');
    }

    /**
     * Relationship với OrderItem
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    // REMOVED: Package relationship and scopes - now using order_fulfillments directly
}
