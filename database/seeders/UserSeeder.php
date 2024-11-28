<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'username'   => 'smartsoft',
            'password'   => bcrypt("admin"),
            'path_photo' => '/storage/user_profile/default.png',
            'name'       => 'Admin Smartsoft 1',
        ]);
        \App\Models\User::create([
            'username'  => 'smartsoft2',
            'password'   => bcrypt("admin"),
            'name'       => 'Admin Smartsoft 2',
        ]);
        \App\Models\User::create([
            'username'  => 'user',
            'password'   => bcrypt("admin"),
            'name'       => 'user',
        ]);
    }
}
