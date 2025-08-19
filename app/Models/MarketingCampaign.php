<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class MarketingCampaign extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'customer_group_id',
        'coupon_id',
        'email_subject',
        'email_content',
        'sent_at',
        'status',
    ];
    protected $dates = ['deleted_at'];

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function logs()
    {
        return $this->hasMany(MarketingCampaignLog::class);
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
    public function customer_group()
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
