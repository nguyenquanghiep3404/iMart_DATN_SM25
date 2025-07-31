<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaignLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_campaign_id',
        'user_id',
        'sent_at',
        'status',
        'error_message',
    ];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
