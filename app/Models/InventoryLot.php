<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\InventoryLot
 *
 * @property int $id
 * @property string $lot_code Mã lô hàng duy nhất
 * @property int $product_variant_id
 * @property int|null $purchase_order_item_id
 * @property float $cost_price Giá vốn của sản phẩm trong lô
 * @property \Illuminate\Support\Carbon|null $expiry_date Ngày hết hạn
 * @property \Illuminate\Support\Carbon|null $manufacturing_date Ngày sản xuất
 * @property int $initial_quantity Số lượng ban đầu khi nhập
 * @property int $quantity_on_hand Số lượng thực tế còn lại
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProductVariant $variant
 * @property-read \App\Models\PurchaseOrderItem|null $purchaseOrderItem
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InventorySerial> $serials
 */
class InventoryLot extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'inventory_lots';

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lot_code',
        'product_variant_id',
        'purchase_order_item_id',
        'cost_price',
        'expiry_date',
        'manufacturing_date',
        'initial_quantity',
        'quantity_on_hand',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost_price' => 'decimal:2',
        'expiry_date' => 'date',
        'manufacturing_date' => 'date',
    ];

    /**
     * Lấy biến thể sản phẩm mà lô hàng này thuộc về.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Lấy chi tiết dòng đơn đặt hàng mua (nếu có) đã tạo ra lô hàng này.
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    /**
     * Lấy tất cả các mã serial/IMEI thuộc lô hàng này.
     */
    public function serials(): HasMany
    {
        return $this->hasMany(InventorySerial::class, 'lot_id');
    }
}