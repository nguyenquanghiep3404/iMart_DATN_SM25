<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_variant_id', 'store_location_id', 'inventory_type', 'quantity_change',
        'quantity_after_change', 'reason', 'reference_id', 'reference_type', 'user_id', 'notes'
    ];

    public function productVariant() { return $this->belongsTo(ProductVariant::class); }
    public function storeLocation() { return $this->belongsTo(StoreLocation::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function reference() { return $this->morphTo(); }
}