<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'user_id', 'return_code', 'status', 'refund_method', 'admin_note', 'approved_by'];

    public function order() { return $this->belongsTo(Order::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function returnItems() { return $this->hasMany(ReturnItem::class); }
}