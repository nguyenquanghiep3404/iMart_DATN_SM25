<?php

namespace Database\Factories;

use App\Models\MarketingCampaignLog;
use App\Models\MarketingCampaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketingCampaignLogFactory extends Factory
{
    protected $model = MarketingCampaignLog::class;

    public function definition()
    {
        $campaignId = MarketingCampaign::inRandomOrder()->value('id') ?? 1;
        $userId = User::inRandomOrder()->value('id') ?? 1;

        return [
            'marketing_campaign_id' => $campaignId,
            'user_id' => $userId,
            'sent_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['pending', 'sent', 'failed']),
            'error_message' => null,
        ];
    }
}
