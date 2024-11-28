<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuRole = \App\Models\RoleMenu::with('menu_master')->get();
        $suffixes = ['_read', '_create', '_update', '_delete'];
        $data = [];
        foreach ($menuRole as $key => $value) {
            foreach ($suffixes as $suffix) {
                $data[] = [
                    'role_id' => $value->role_id,
                    'menu_master_id' => $value->menu_master_id,
                    'role_menu_id' => $value->role_menu_id,
                    'slug' => $value->menu_master->menu_master_slug . $suffix,
                    'value' => true,
                ];
            }
        }

        \App\Models\RolePermission::insert($data);
    }
}
