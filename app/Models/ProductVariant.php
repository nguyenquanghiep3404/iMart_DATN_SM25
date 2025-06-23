<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'sale_price_starts_at',
        'sale_price_ends_at',
        'stock_quantity',
        'manage_stock',
        'stock_status',
        'weight',
        'primary_image_id',
        'dimensions_length',
        'dimensions_width',
        'dimensions_height',
        'is_default',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'sale_price_starts_at' => 'datetime',
        'sale_price_ends_at' => 'datetime',
        'stock_quantity' => 'integer',
        'manage_stock' => 'boolean',
        'weight' => 'decimal:2',
        'dimensions_length' => 'decimal:2',
        'dimensions_width' => 'decimal:2',
        'dimensions_height' => 'decimal:2',
        'is_default' => 'boolean',

    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_values', 'product_variant_id', 'attribute_value_id')->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Mối quan hệ đa hình cho ảnh của biến thể (nếu có)
    public function images()
    {
        return $this->morphMany(UploadedFile::class, 'attachable')->where('type', 'variant_image')->orderBy('order');
    }
    public function primaryImage()
{
    // Giả định bạn có cột `primary_image_id` trong bảng `product_variants`
    // và nó liên kết với bảng `uploaded_files`.
    return $this->belongsTo(UploadedFile::class, 'primary_image_id');
}
}