<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'inventory_type',
        'quantity',
        'quantity_committed',
        'store_location_id',
    ];
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'store_location_id');
    }

    /**
     * Tính toán số lượng tồn kho có thể bán
     * Available Quantity = quantity - quantity_committed
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->quantity_committed;
    }

    /**
     * Kiểm tra xem có đủ tồn kho để bán không
     */
    public function hasAvailableStock($requestedQuantity)
    {
        return $this->available_quantity >= $requestedQuantity;
    }

    /**
     * Tạm giữ tồn kho cho đơn hàng
     */
    public function commitStock($quantity)
    {
        if (!$this->hasAvailableStock($quantity)) {
            throw new \Exception('Không đủ tồn kho để tạm giữ');
        }
        
        $this->increment('quantity_committed', $quantity);
        return $this;
    }

    /**
     * Thả tồn kho đã tạm giữ (khi hủy đơn hàng)
     */
    public function releaseStock($quantity)
    {
        $this->decrement('quantity_committed', $quantity);
        return $this;
    }

    /**
     * Xuất kho thực tế (khi giao hàng)
     */
    public function fulfillStock($quantity)
    {
        $this->decrement('quantity', $quantity);
        $this->decrement('quantity_committed', $quantity);
        return $this;
    }

    /**
     * Nhập kho mới
     */
    public function receiveStock($quantity)
    {
        $this->increment('quantity', $quantity);
        return $this;
    }
}
