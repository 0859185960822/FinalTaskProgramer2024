<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\CustomersSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(ConfigSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(MenuMasterSeeder::class);
        $this->call(UserRoleSeeder::class);
        $this->call(RoleMenuSeeder::class);
        $this->call(RolePermissionSeeder::class);
    }
}
