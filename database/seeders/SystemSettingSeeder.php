<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'iStore - Apple Chính Hãng', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_logo', 'value' => null, 'group' => 'general', 'type' => 'image'], // Sẽ upload sau
            ['key' => 'admin_email', 'value' => 'admin@example.com', 'group' => 'general', 'type' => 'string'],
            ['key' => 'contact_email', 'value' => 'contact@example.com', 'group' => 'contact', 'type' => 'string'],
            ['key' => 'contact_phone', 'value' => '0123456789', 'group' => 'contact', 'type' => 'string'],
            ['key' => 'default_pagination_limit', 'value' => '15', 'group' => 'general', 'type' => 'number'],
            ['key' => 'currency_symbol', 'value' => '₫', 'group' => 'localization', 'type' => 'string'],
            ['key' => 'facebook_link', 'value' => 'https://facebook.com/yourpage', 'group' => 'social', 'type' => 'string'],
            ['key' => 'instagram_link', 'value' => 'https://instagram.com/yourpage', 'group' => 'social', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
        $this->command->info('System Settings seeded successfully!');
    }
}
