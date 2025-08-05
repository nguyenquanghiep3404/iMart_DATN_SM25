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
        'status', // Mặc dù có giá trị default, thêm vào đây để có thể ghi đè nếu cần
    ];
}