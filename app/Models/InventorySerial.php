<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;


class InventorySerial extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'inventory_serials';

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_variant_id',
        'lot_id',
        'store_location_id',
        'serial_number',
        'status', // available, transferred, sold, defective, returned
    ];

    /**
     * Lấy biến thể sản phẩm mà serial này thuộc về.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Lấy lô hàng mà serial này được nhập vào.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class, 'lot_id');
    }

    /**
     * Lấy vị trí (kho/cửa hàng) hiện tại của serial này.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(StoreLocation::class, 'store_location_id');
    }

    /**
     * Lấy tất cả các mục chuyển kho liên quan đến serial này.
     */
    public function stockTransferItemSerials(): HasMany
    {
        return $this->hasMany(StockTransferItemSerial::class, 'inventory_serial_id');
    }

    /**
     * Lấy mục đơn hàng liên quan đến serial này.
     */
   /**
     * Lấy mục đơn hàng liên quan đến serial này.
     */
    public function orderItemSerial(): HasOne
    {
        return $this->hasOne(OrderItemSerial::class, 'serial_number', 'serial_number');
    }
}