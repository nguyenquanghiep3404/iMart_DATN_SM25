<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'address_label', 'full_name', 'phone_number', 'address_line1', 'address_line2',
        'city', 'district', 'ward', 'zip_code', 'country', 'is_default_shipping', 'is_default_billing',
    ];

    protected $casts = [
        'is_default_shipping' => 'boolean',
        'is_default_billing' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}