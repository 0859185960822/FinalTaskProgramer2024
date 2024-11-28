<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Config::create([
            'slug' => 'APPLICATION_SHORT_NAME',
            'value' => 'SOP Alfahuma'
        ]);
        \App\Models\Config::create([
            'slug' => 'APPLICATION_FULL_NAME',
            'value' => 'Standard Operational Programming Alfahuma'
        ]);
        \App\Models\Config::create([
            'slug' => 'LOGO_SMALL',
            'value' => 'storage/logo_icon_apps/favicon.png'
        ]);
        \App\Models\Config::create([
            'slug' => 'LOGO_FULL_DARK',
            'value' => 'storage/logo_icon_apps/logo-dark.png'
        ]);
        \App\Models\Config::create([
            'slug' => 'LOGO_FULL_LIGHT',
            'value' => 'storage/logo_icon_apps/logo-light.png'
        ]);
        \App\Models\Config::create([
            'slug' => 'LOGIN_BACKGROUND',
            'value' => 'storage/login_background/default.jpg'
        ]);
        \App\Models\Config::create([
            'slug' => 'VERSION',
            'value' => '1.0.0'
        ]);
        \App\Models\Config::create([
            'slug' => 'COPYRIGHT_COPORATION',
            'value' => 'Alfahuma Rekayasa Teknologi'
        ]);
        \App\Models\Config::create([
            'slug' => 'SKIN',
            'value' => 'Default'
        ]);
        \App\Models\Config::create([
            'slug' => 'FAIL_ATTEMPT',
            'value' => '10'
        ]);
        \App\Models\Config::create([
            'slug' => 'ONE_TIME_LOGIN',
            'value' => 0,
        ]);
        \App\Models\Config::create([
            'slug' => 'CAPTCHA',
            'value' => 0,
        ]);
    }
}
