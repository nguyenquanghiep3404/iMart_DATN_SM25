<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransferItemSerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_item_id',
        'inventory_serial_id',
        'status', // Mặc dù có giá trị default, thêm vào đây để có thể ghi đè nếu cần
    ];

    /**
     * Lấy mục chuyển kho mà serial này thuộc về.
     */
    public function stockTransferItem(): BelongsTo
    {
        return $this->belongsTo(StockTransferItem::class, 'stock_transfer_item_id');
    }

    /**
     * Lấy serial hàng tồn kho liên quan.
     */
    public function inventorySerial(): BelongsTo
    {
        return $this->belongsTo(InventorySerial::class, 'inventory_serial_id');
    }
}