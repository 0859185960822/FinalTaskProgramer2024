<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();
        \App\Models\MenuMaster::create([
            'menu_master_name' => 'Menu',
            'menu_master_type' => 'LABEL',
            'menu_master_icon' => null,
            'menu_master_link' => null,
            'menu_master_urutan' => 1,
            'menu_master_parent' => 0,
            'menu_master_slug' => 'menu_label',
            'created_by' => $user->user_id,
        ]);
        \App\Models\MenuMaster::create([
            'menu_master_name' => 'Dashboards',
            'menu_master_type' => 'MENU',
            'menu_master_icon' => 'bx bx-home',
            'menu_master_link' => 'default',
            'menu_master_urutan' => 2,
            'menu_master_parent' => 0,
            'menu_master_slug' => 'dashboard',
            'created_by' => $user->user_id,
        ]);
        \App\Models\MenuMaster::create([
            'menu_master_name' => 'Master',
            'menu_master_type' => 'MENU',
            'menu_master_icon' => 'bx bx-customize',
            'menu_master_link' => null,
            'menu_master_urutan' => 3,
            'menu_master_parent' => 0,
            'menu_master_slug' => 'master',
            'created_by' => $user->user_id,
        ]);
        \App\Models\MenuMaster::create([
            'menu_master_name' => 'Menu Master',
            'menu_master_type' => 'MENU',
            'menu_master_icon' => null,
            'menu_master_link' => 'menu-master',
            'menu_master_urutan' => 1,
            'menu_master_parent' => 3,
            'menu_master_slug' => 'menu_master',
            'created_by' => $user->user_id,
        ]);
        \App\Models\MenuMaster::create([
            'menu_master_name' => 'Role',
            'menu_master_type' => 'MENU',
            'menu_master_icon' => null,
            'menu_master_link' => 'role',
            'menu_master_urutan' => 2,
            'menu_master_parent' => 3,
            'menu_master_slug' => 'role',
            'created_by' => $user->user_id,
        ]);
        \App\Models\MenuMaster::create([
            'menu_master_name' => 'User',
            'menu_master_type' => 'MENU',
            'menu_master_icon' => null,
            'menu_master_link' => 'user',
            'menu_master_urutan' => 3,
            'menu_master_parent' => 3,
            'menu_master_slug' => 'user',
            'created_by' => $user->user_id,
        ]);
        \App\Models\MenuMaster::create([
            'menu_master_name' => 'Konfigurasi',
            'menu_master_type' => 'MENU',
            'menu_master_icon' => null,
            'menu_master_link' => 'konfigurasi',
            'menu_master_urutan' => 4,
            'menu_master_parent' => 3,
            'menu_master_slug' => 'konfigurasi',
            'created_by' => $user->user_id,
        ]);
    }
}
