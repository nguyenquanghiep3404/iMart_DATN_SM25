<?php

namespace Database\Factories;

use App\Models\MarketingCampaign;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketingCampaignFactory extends Factory
{
    protected $model = MarketingCampaign::class;

    public function definition()
    {
        // Lấy 1 customer_group_id ngẫu nhiên (có thể thay thành id cố định nếu chưa có nhóm)
        $customerGroupId = CustomerGroup::inRandomOrder()->value('id') ?? 1;
        $status = $this->faker->randomElement(['draft', 'scheduled', 'sent']);
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'customer_group_id' => $customerGroupId,
            'email_subject' => $this->faker->sentence(),
            'email_content' => $this->faker->paragraphs(3, true),
            'status' => $status,
            'sent_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
