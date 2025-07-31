<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transfer_code',
        'from_location_id',
        'to_location_id',
        'status',
        'created_by',
        'shipped_at',
        'received_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * Get the location the stock was transferred from.
     */
    public function fromLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'from_location_id');
    }

    /**
     * Get the location the stock was transferred to.
     */
    public function toLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'to_location_id');
    }

    /**
     * Get the user who created the transfer.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the items for the stock transfer.
     */
    public function items() // <-- ĐÃ ĐỔI TÊN TỪ stockTransferItems SANG items
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
