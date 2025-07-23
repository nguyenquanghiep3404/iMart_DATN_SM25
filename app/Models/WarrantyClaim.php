<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyClaim extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'order_item_id', 'claim_code', 'reported_defect', 'status', 'resolution', 'technician_notes', 'resolved_at'];
    protected $casts = ['resolved_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function orderItem() { return $this->belongsTo(OrderItem::class); }
}