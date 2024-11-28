<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::first();
        $users = \App\Models\User::all();
        $roleUser = \App\Models\Role::where('role_name', 'User')->first();
        $roleAdmin = \App\Models\Role::first();

        foreach ($users as $key => $user) {
            if ($user->username != 'user') {
                $this->createData($user, $roleAdmin, $admin);
            }
            $this->createData($user, $roleUser, $admin);
        }
    }

    public function createData($user, $role, $admin): void
    {
        \App\Models\UserRole::create([
            "user_id" => $user->user_id,
            "role_id" => $role->role_id,
            "created_by" => $admin->user_id,
            "updated_by" => $admin->user_id,
        ]);
    }
}
