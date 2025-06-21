<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = 'wishlists'; 

    protected $fillable = [
        'user_id',
    ];

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Quan hệ với WishlistItem
    public function items()
    {
        return $this->hasMany(WishlistItem::class, 'wishlist_id', 'id');
    }
}
