<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wishlist_id',
        'product_variant_id',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}