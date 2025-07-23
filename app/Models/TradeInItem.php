<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Enums\TradeInStatusEnum;
use Illuminate\Database\Eloquent\SoftDeletes;
class TradeInItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_variant_id', 'store_location_id', 'type', 'sku', 'condition_grade',
        'condition_description', 'selling_price', 'imei_or_serial', 'status'
    ];

    /**
     * Lấy biến thể sản phẩm gốc.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Lấy cửa hàng lưu trữ.
     */
    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class);
    }

    /**
     * Lấy tất cả hình ảnh của sản phẩm (quan hệ đa hình).
     */
    public function images()
    {
        // Sử dụng quan hệ đa hình thay vì belongsToMany để khớp với logic store
        return $this->morphMany(UploadedFile::class, 'attachable');
    }

    /**
     * Accessor: Lấy URL ảnh đại diện.
     * Tên phương thức get...Attribute sẽ cho phép bạn gọi $item->cover_image_url
     */
    public function getCoverImageUrlAttribute()
    {
        // Lấy ảnh đầu tiên trong danh sách ảnh
        $firstImage = $this->images()->orderBy('order', 'asc')->first();

        if ($firstImage && Storage::disk('public')->exists($firstImage->path)) {
            // Nếu có ảnh và file tồn tại, trả về URL của nó
            return Storage::url($firstImage->path);
        }

        // Trả về URL ảnh mặc định nếu không có ảnh
        return asset('admin-assets/images/placeholder.png'); // Hãy chắc chắn bạn có ảnh này
    }
}