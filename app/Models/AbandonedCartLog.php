<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class AbandonedCartLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'abandoned_cart_id',
        'action',
        'description',
        'causer_type',
        'causer_id',
    ];

    public function abandonedCart(): BelongsTo
    {
        return $this->belongsTo(AbandonedCart::class);
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}

