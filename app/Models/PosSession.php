<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosSession extends Model
{
    use HasFactory;
    protected $fillable = [
        'register_id', 'user_id', 'opening_balance', 'closing_balance', 
        'calculated_balance', 'status', 'opened_at', 'closed_at', 'notes'
    ];
    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function register() {
        return $this->belongsTo(Register::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}