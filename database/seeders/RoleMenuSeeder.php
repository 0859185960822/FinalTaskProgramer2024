<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = \App\Models\MenuMaster::all();
        // $adminRole = \App\Models\Role::where("role_name", "Administrator")->first();
        // $userRole = \App\Models\Role::where("role_name", "User")->first();
        $roles = \App\Models\Role::all();
        $admin = \App\Models\User::first();

        foreach ($roles as $role) {
            foreach ($menus as $menu) {
                if (!($menu->menu_master_name != 'Dashboards' && $role->role_name == 'User')) {
                    \App\Models\RoleMenu::create([
                        'role_id' => $role->role_id,
                        'menu_master_id' => $menu->menu_master_id,
                        'created_by' => $admin->user_id,
                    ]);
                }
            }
        }
    }
}
