<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\OrderItemSerial
 *
 * @property int $id
 * @property int $order_item_id
 * @property int $product_variant_id
 * @property string $serial_number
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OrderItem $orderItem
 * @property-read \App\Models\ProductVariant $variant
 */
class OrderItemSerial extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'order_item_serials';

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_item_id',
        'product_variant_id',
        'serial_number',
        'status', // sold, returned
    ];

    /**
     * Lấy chi tiết dòng đơn hàng (order item) mà serial này được gán vào.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /**
     * Lấy biến thể sản phẩm của serial này.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}