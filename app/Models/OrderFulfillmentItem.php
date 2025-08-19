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
        'quantity',
        'package_id'
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

    /**
     * Relationship với Package
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Scope để lấy items theo package
     */
    public function scopeForPackage($query, $packageId)
    {
        return $query->where('package_id', $packageId);
    }

    /**
     * Scope để lấy items chưa được gán package
     */
    public function scopeWithoutPackage($query)
    {
        return $query->whereNull('package_id');
    }
}
