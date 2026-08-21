<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => 'BNG-Angola',
            'primary_color' => '#1A3C6E',
            'secondary_color' => '#007A4D',
            'theme_mode' => 'light',
            'support_email' => 'suporte@bng.ao',
            'support_phone' => '+244 000 000 000',
            'homepage_video_path' => null,
            'homepage_video_url' => null,
        ];

        foreach ($defaults as $key => $value) {
            SystemSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
