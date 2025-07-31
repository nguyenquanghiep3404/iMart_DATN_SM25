<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignLog;
use App\Models\User;

class MarketingCampaignSeeder extends Seeder
{
    public function run()
    {
        // Tạm tắt kiểm tra khóa ngoại để xóa dữ liệu
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Xóa dữ liệu bảng log và bảng chiến dịch
        MarketingCampaignLog::query()->delete();
        MarketingCampaign::query()->delete();

        // Bật lại kiểm tra khóa ngoại
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Tạo 5 chiến dịch mẫu
        $campaigns = MarketingCampaign::factory()->count(5)->create();

        // Lấy danh sách tất cả user id
        $allUserIds = User::pluck('id')->toArray();

        foreach ($campaigns as $campaign) {
            if ($campaign->status === 'sent') {
                $userIds = collect($allUserIds)->random(min(10, count($allUserIds)));
        
                foreach ($userIds as $userId) {
                    MarketingCampaignLog::factory()->create([
                        'marketing_campaign_id' => $campaign->id,
                        'user_id' => $userId,
                    ]);
                }
            }
        }
    }
}
