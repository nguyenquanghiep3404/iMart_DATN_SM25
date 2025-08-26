<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


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
        'manage_stock',
        'stock_status',
        'weight',
        'primary_image_id',
        'dimensions_length',
        'dimensions_width',
        'dimensions_height',
        'is_default',
        'status',
        'cost_price',
        'points_awarded_on_purchase',
        'has_serial_tracking',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'sale_price_starts_at' => 'datetime',
        'sale_price_ends_at' => 'datetime',
        'manage_stock' => 'boolean',
        'weight' => 'decimal:2',
        'dimensions_length' => 'decimal:2',
        'dimensions_width' => 'decimal:2',
        'dimensions_height' => 'decimal:2',
        'is_default' => 'boolean',
        'has_serial_tracking' => 'boolean',
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

    public function getImageUrlAttribute()
    {
        if ($this->primaryImage && $this->primaryImage->path) {
            return '/storage/' . ltrim($this->primaryImage->path, '/');
        }

        // fallback nếu không có ảnh
        return '/images/no-image.png';
    }

    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(Specification::class, 'product_specification_values')
            ->withPivot('value') // Quan trọng: Lấy cột 'value' từ bảng trung gian
            ->withTimestamps();
    }

    public function inventories()
    {
        return $this->hasMany(ProductInventory::class);
    }

    /**
     * Lấy tổng số lượng tồn kho có thể bán được (ví dụ: new + open_box).
     * CŨ: Chỉ tính quantity
     */
    public function getSellableStockAttribute(): int
    {
        $sellableTypes = ['new'];
        return $this->inventories()
            ->whereIn('inventory_type', $sellableTypes)
            ->sum('quantity');
    }

    /**
     * MỚI: Lấy tổng số lượng tồn kho có thể bán được theo mô hình mới
     * Available Quantity = quantity - quantity_committed
     */
    public function getAvailableStockAttribute(): int
    {
        $sellableTypes = ['new'];
        return $this->inventories()
            ->whereIn('inventory_type', $sellableTypes)
            ->selectRaw('SUM(quantity - quantity_committed) as total_available')
            ->value('total_available') ?? 0;
    }

    /**
     * Kiểm tra xem có đủ tồn kho để bán không
     */
    public function hasAvailableStock($requestedQuantity = 1): bool
    {
        return $this->available_stock >= $requestedQuantity;
    }

    /**
     * Lấy thông tin chi tiết tồn kho theo từng kho
     */
    public function getInventoryDetails()
    {
        return $this->inventories()
            ->with('storeLocation:id,name,address')
            ->where('inventory_type', 'new')
            ->selectRaw('*, (quantity - quantity_committed) as available_quantity')
            ->get();
    }

    // Một biến thể sản phẩm (ProductVariant) có thể nằm trong nhiều chương trình flash sale khác nhau.
    // Thiết lập quan hệ 1-n với bảng trung gian flash_sale_products.
    public function flashSaleProducts()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    /**
     * Lấy danh sách tất cả các gói sản phẩm (bundle) mà biến thể sản phẩm này đóng vai trò là **sản phẩm chính**.
     *
     * Mỗi bản ghi trong bảng `bundle_main_products` tương ứng với một mối quan hệ giữa biến thể này
     * và một `ProductBundle` mà nó làm sản phẩm chính.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bundleMainProducts()
    {
        return $this->hasMany(BundleMainProduct::class, 'product_variant_id');
    }

    /**
     * Lấy danh sách tất cả các gợi ý sản phẩm mà biến thể sản phẩm này xuất hiện trong các **gợi ý sản phẩm đi kèm**.
     *
     * Mỗi bản ghi trong bảng `bundle_suggested_products` thể hiện một mối quan hệ giữa biến thể này
     * và một `ProductBundle` mà nó được đề xuất đi kèm.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bundleSuggestedProducts()
    {
        return $this->hasMany(BundleSuggestedProduct::class, 'product_variant_id');
    }

    public function tradeInItems()
    {
        return $this->hasMany(TradeInItem::class);
    }

    // Trong model ProductVariant.php
    public function getSlugAttribute()
    {
        $product = $this->product;
        $baseSlug = Str::slug($product->name);
        $attributes = $this->attributeValues
            ->pluck('value')
            ->map(fn($value) => Str::slug($value, '-'))
            ->filter() // Loại bỏ giá trị rỗng
            ->join('-');
        return $attributes ? "{$baseSlug}-{$attributes}" : $baseSlug;
    }

     public function getAvailableQuantityAttribute()
    {
        // Tính tổng (tồn kho - đã tạm giữ cho đơn khác) từ bảng product_inventories
        return $this->inventories()->sum(DB::raw('quantity - quantity_committed'));
    }
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }
    public function coverImage()
    {
        return $this->morphOne(UploadedFile::class, 'attachable')
            ->where('type', 'cover')
            ->latest();

    }
}
