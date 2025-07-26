<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
class AbandonedCart extends Model
{
    use HasFactory;
    protected $fillable = [
        'cart_id',
        'user_id',
        'guest_email',
        'status',
        'email_status',
        'in_app_notification_status',
        'recovery_token',
        'last_notified_at',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AbandonedCartLog::class);
    }
    public function getItemsAttribute(): Collection
    {
        return optional($this->cart)->items ?? collect();
    }
}

