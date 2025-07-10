<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProductVariant;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_name',
        'variant_attributes',
        'quantity',
        'sku',
        'price',
        'total_price',
    ];

    protected $casts = [
        'variant_attributes' => 'array', // Hoặc 'json'
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected $with = ['productVariant.product', 'productVariant.primaryImage'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // Accessor để đảm bảo variant_attributes được serialize đúng
    public function getVariantAttributesAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }
    // public function getVariantAttributesAttribute($value)
    // {
    //     if (is_array($value)) {
    //         return $value;
    //     }

    //     return json_decode($value, true) ?: [];
    // }

    public function setVariantAttributesAttribute($value)
    {
        $this->attributes['variant_attributes'] = is_string($value)
            ? $value
            : json_encode($value);
    }
}
