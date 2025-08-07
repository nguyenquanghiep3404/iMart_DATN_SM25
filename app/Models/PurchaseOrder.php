<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
class PurchaseOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'po_code',
        'status',
        'order_date',
        'store_location_id', 
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order_date' => 'date',
    ];

    /**
     * Get the supplier that owns the purchase order.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the items for the purchase order.
     */
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class);
    }
    /**
     * Lấy tất cả các chi tiết (sản phẩm) của phiếu nhập.
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    
    /**
     * Accessor để tính tổng tiền của phiếu nhập.
     * Cách dùng trong view: $purchaseOrder->total_amount
     */
    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items->sum(function ($item) {
                return $item->quantity * $item->cost_price;
            })
        );
    }
    
}