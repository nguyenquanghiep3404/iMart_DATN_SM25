<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_order_id',
        'product_variant_id',
        'quantity',
        'cost_price',
    ];

    /**
     * Get the purchase order that this item belongs to.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product variant associated with the purchase order item.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}