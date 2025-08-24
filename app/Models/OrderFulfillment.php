<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFulfillment extends Model
{
    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PACKED = 'packed';
    public const STATUS_AWAITING_SHIPMENT = 'awaiting_shipment';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_EXTERNAL_SHIPPING = 'external_shipping';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'order_id', 'store_location_id', 'shipper_id', 'tracking_code',
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

public function shipper()
{
    return $this->belongsTo(User::class, 'shipper_id');
}

// REMOVED: packages relationship - now using order_fulfillments directly

    /**
     * Get all available status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_PACKED => 'Đã đóng gói',
            self::STATUS_AWAITING_SHIPMENT => 'Chờ vận chuyển',
            self::STATUS_SHIPPED => 'Đã vận chuyển',
            self::STATUS_EXTERNAL_SHIPPING => 'Giao bởi đơn vị thứ 3',
            self::STATUS_DELIVERED => 'Đã giao hàng',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_FAILED => 'Thất bại',
            self::STATUS_RETURNED => 'Đã trả hàng'
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }
}
