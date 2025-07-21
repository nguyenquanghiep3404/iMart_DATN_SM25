<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeInItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_variant_id', 'store_location_id', 'type', 'sku', 'condition_grade',
        'condition_description', 'selling_price', 'imei_or_serial', 'status'
    ];

    public function productVariant() { return $this->belongsTo(ProductVariant::class); }
    public function storeLocation() { return $this->belongsTo(StoreLocation::class); }
    public function images() { return $this->belongsToMany(UploadedFile::class, 'trade_in_item_images'); }
    public function cartItems() { return $this->morphMany(CartItem::class, 'cartable'); }
}