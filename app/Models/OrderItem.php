<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProductVariant;
use App\Models\ReturnItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_name',
        'variant_attributes',
        'sku',
        'quantity',
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
        return $this->hasOne(Review::class, 'order_item_id', 'id');
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
    // OrderItem model
    public function returnItem()
    {
        return $this->hasOne(ReturnItem::class);
    }
    public function getImageUrlAttribute()
    {
        $variant = $this->productVariant; // Lấy biến thể sản phẩm

        // Ưu tiên 1: Lấy ảnh chính của biến thể
        if ($variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path))) {
            return Storage::url($variant->primaryImage->path);
        }

        // Ưu tiên 2: Lấy ảnh bìa của sản phẩm gốc
        if ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path))) {
            return Storage::url($variant->product->coverImage->path);
        }

        // Nếu không có ảnh nào, trả về ảnh mặc định
        return asset('images/placeholder.jpg');
    }

    public function warrantyClaims()
{
    return $this->hasMany(WarrantyClaim::class);
}
}
