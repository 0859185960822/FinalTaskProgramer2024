<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::first();
        \App\Models\Role::create([
            'role_name' => "Administrator",
            'role_description' => 'Role ini bertindak sebagai Administrator.',
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
        ]);
        \App\Models\Role::create([
            'role_name' => "User",
            'role_description' => 'Role ini sebagai User',
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
        ]);
    }
}
