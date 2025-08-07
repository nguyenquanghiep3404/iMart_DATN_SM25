<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferItemSerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_item_id',
        'inventory_serial_id',
        'status',
    ];

    /**
     * Get the main inventory serial record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventorySerial()
    {
        return $this->belongsTo(InventorySerial::class, 'inventory_serial_id');
    }
}